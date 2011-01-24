<?php
/* $Id: HttpProcessor.php 220 2005-06-23 19:38:30Z mojavelinux $
 *
 * Copyright 2003-2005 Dan Allen, Mojavelinux.com (dan.allen@mojavelinux.com)
 *
 * This project was originally created by Dan Allen, but you are permitted to
 * use it, modify it and/or contribute to it.  It has been largely inspired by
 * a handful of other open source projects and public specifications, most
 * notably Apache's Jakarta Project and Sun Microsystem's J2EE SDK.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import('stratus.http.HttpServletRequest');
import('stratus.http.HttpServletResponse');
import('stratus.util.ServletUtils');
import('stratus.util.ServerInfo');

/**
 * @package stratus.connector
 * @author Dan Allen
 */
class HttpProcessor extends Object
{
	var $context = null;

	var $request = null;

	var $response = null;

	var $serverPort = 0;

	// temporarily place reports in local array
	var $statusReports = array(
		400 => 'The request sent by the client was syntactically incorrect ({0}).',
		401 => 'This request requires HTTP authentication ({0}).',
		404 => 'The requested resource ({0}) is not available.',
		500 => 'The server encountered an internal error ({0}) that prevented it from fulfilling this request.',
	);

	function HttpProcessor(&$context)
	{
		$this->context =& $context;
		$this->request =& new HttpServletRequest();
		$this->response =& new HttpServletResponse();
		$this->serverPort = $_SERVER['SERVER_PORT'];
	}

	/**
	 * @return void
	 */
	function run()
	{
		$this->request->setResponse($this->response);
		$this->response->setRequest($this->request);

		// NOTE: we need to handle any exceptions that were thrown during
		// context configuration
		if ($e = catch_exception())
		{
			$this->handleException($e);
			return;
		}

		// use a top-level buffer to catch fatal error that are outputted to the browser
		ob_start(array(&$this, 'fatalErrorWatchdog'));

		// if the context config told us this is startup time for the context, start
		// the context now, in the comfort of the fatalErrorWatchdog ;)
		if ($this->context->getStartup())
		{
			$this->context->start();
		}

		$this->parseConnection($_SERVER);
		$this->parseRequest($_SERVER);
		$this->parseHeaders($_SERVER);
		$this->response->startBuffer();
		$this->response->setHeader('Date', gmdate('D, d M Y H:i:s T'));
		$this->context->setName($this->request->getContextPath());
		$this->context->invoke($this->request, $this->response);

		// TODO: move this to actual valve location, for now we just do it here
		if ($e = catch_exception())
		{
			$this->handleException($e);
		}
		else
		{
			$this->response->finishResponse();

			if ($this->response->isError())
			{
				echo $this->report($this->request, $this->response, $tmp = null);
			}
		}

		// end the fatalErrorWatchdog buffer
		ob_end_flush();
	}

	function handleException(&$e)
	{
		$this->response->setError();
		$this->response->reset();
		$this->response->sendError(500);
		$this->response->setSuspended(true);
		$this->response->finishResponse();
		// this buffer will be picked up by fatalErrorWatchdog
		echo $this->report($this->request, $this->response, $e);
	}

	function parseLanguageAccept($value)
	{
		$locales = array();
		$value = str_replace(array(' ', "\t"), '', $value);
		$entries = explode(',', $value);
		foreach ($entries as $entry)
		{
			// extract the quality factor
			$quality = 1.0;
			if (($semi = strpos($entry, ';q=')) !== false)
			{
				$quality = (float)substr($entry, $semi + 3);
				$entry = substr($entry, 0, $semi);
			}
			
			$localeParts = explode('-', $entry);
			$language = $locale = $localeParts[0];
			$country = '';
			$varient = '';
			if (count($localeParts) > 1)
			{
				$country = $localeParts[1];
				$locale .= '_' . strtoupper($country);

				if (count($localeParts) > 2)
				{
					$variant = $localeParts[2];
					$locale .= '_' . $variant;
				}
			}

			$key = -$quality;
			if (empty($locales["$key"]))
			{
				$locales["$key"] = array();
			}

			$locales["$key"][] = $locale;
		}

		// process the quality values in highest->lowest order
		ksort($locales);
		foreach ($locales as $localeSet)
		{
			foreach ($localeSet as $locale)
			{
				$this->request->addLocale($locale);
			}
		}
	}

	// parseRequest gets the method/protocol/secure/scheme/requestURI
	function parseRequest(&$input)
	{
		// NOTE: The SCRIPT_NAME will always be the controller file prefixed by the context path.
		// Here we just strip off the controller and we arrive at our contextPath
		$contextPath = substr($input['SCRIPT_NAME'], 0, strrpos($input['SCRIPT_NAME'], '/'));
		// PATH_INFO is effectively the servlet path (PHP automatically decodes this string)
		$requestURI = $contextPath . (isset($input['PATH_INFO']) ? $input['PATH_INFO'] : '');
		$normalizedURI = ServletUtils::normalize($requestURI);
		// make sure it ends in '/' if it is the root of the context
		if ($contextPath == '' && $normalizedURI == '')
		{
			$normalizedURI = '/';
		}

		// TODO: make sure the normalizedURI is not null and if so throw an exception
		$this->request->setRequestURI($normalizedURI);

		$this->request->setMethod($input['REQUEST_METHOD']);
		$this->request->setProtocol($input['SERVER_PROTOCOL']);

		if (isset($input['QUERY_STRING']) && strlen($input['QUERY_STRING']) > 0)
		{
			$this->request->setQueryString($input['QUERY_STRING']);
		}

		if (isset($input['HTTPS']) && $input['HTTPS'] == 'on')
		{
			$this->request->setScheme('https');
			$this->request->setSecure(true);
		}
		else
		{
			$this->request->setScheme('http');
		}

		// we have to set the contextPath explicitly since this is handled normally by the host mapper
		$this->request->setContextPath($contextPath);
	}

	// parseHeaders gets authorization/accept language/cookies/host name
	function parseHeaders(&$input)
	{
		// authorization
		// add cookies
		// parse language
		$this->parseLanguageAccept(isset($input['HTTP_ACCEPT_LANGUAGE']) ? $input['HTTP_ACCEPT_LANGUAGE'] : 'en');
		$this->request->setInput($_POST);
		// NOTE: I used HTTP_HOST rather than SERVER_NAME since it uses the host from the request
		list($host) = explode(':', $input['HTTP_HOST']);
		$this->request->setServerName($host);
	}

	/**
	 * Record the connection parameters related to this request.
	 *
	 * @param array $input The server variables generated by PHP which summarize this request
	 * @return void
	 */
	function parseConnection(&$input)
	{
		$this->request->setRemoteAddr($input['REMOTE_ADDR']);
		$this->request->setServerPort($this->serverPort);
	}

	function report(&$request, &$response, &$e)
	{
		$statusCode = $response->getStatus();
		$message = htmlspecialchars($response->getMessage());
		// do nothing on info level status codes
		if ($statusCode < 400)
		{
			return;
		}

		// temporary lookup to local array
		$report = str_replace('{0}', $message, $this->statusReports[$statusCode]);
		$out = '<html><head><title>';
		$out .= ServerInfo::getServerInfo() . ' - Error report';
		$out .= '</title>';
		$out .= '<style><!--
h1 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size:22px; }
h2 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size:16px; }
h3 { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; font-size:14px; }
body { font-family: Tahoma,Arial,sans-serif; color: black; background-color: white; }
b { font-family: Tahoma,Arial,sans-serif; color: white; background-color: #525D76; }
p { font-family: Tahoma,Arial,sans-serif; background: white; color: black; font-size:12px; }
a { color: black;}
a.name { color: black;}
hr { color: #525D76;}
--></style>';
        $out .= '</head><body>';
		$out .= '<h1>';	
		$out .= 'HTTP Status ' . $statusCode . ' - ' . $message . '</h1>';
		$out .= '<hr size="1" noshade="noshade" />';
		$out .= '<p><b>type</b> ';
		$out .= is_null($e) ? 'Status report' : 'Exception report';
		$out .= '</p>';
		$out .= '<p><b>message</b> <u>' . $message . '</u></p>';
		$out .= '<p><b>description</b> <u>' . $report . '</u></p>';
		if (!is_null($e))
		{
			$out .= '<p><b>exception</b> <pre>' . $e->getStackTrace() . '</pre></p>';
		}
		$out .= '<hr size="1" noshade="noshade" />';
		$out .= '<h3>' . ServerInfo::getServerInfo() . '</h3>';
		$out .= '</body></html>';
		$response->setContentType('text/html');
		return $out;
	}

	/**
	 * This watchdog serves as a top-level buffer event handler.  Since PHP outputs FATAL errors
	 * directly to the browser, escaping the exception handling mechanism that is provided by Horizon,
	 * it is necessary to use a callback output buffer to capture these messages and covert them
	 * into formal exception reports (similar to those in Tomcat).
	 * 
	 * In the case of a fatal error, once this method is called, the execution
	 * of the PHP script is basically finished, waiting only for the return
	 * value of this method to determine what to display.
	 *
	 * In the case of normal output, the string is simply trimmed and sent to the browser window.
	 *
	 * @param string The contents of the buffer
	 *
	 * @return string The buffer to output instead of the captured buffer
	 */
	function fatalErrorWatchdog($buffer)
	{
		$html_errors = ini_get('html_errors');
		// if user has html_errors on, we need to have the non-html version
		if ($html_errors)
		{
			$sanitizedBuffer = strip_tags($buffer);
		}
		else
		{
			$sanitizedBuffer = trim($buffer);
		}

		// we know we got here from a thrown fatal error
		// NOTE: this searches anywhere in the string, so outputting the
		// string equal to FATAL_ERROR_PREFIX will trigger an error report
		// QUESTION: would there be a way to backout if no exception existed?
		if (($pos = strpos($sanitizedBuffer, FATAL_ERROR_PREFIX)) !== false)
		{
			$this->response->setError();
			$this->response->setStatus(c('HttpServletResponse::SC_INTERNAL_SERVER_ERROR'));
			if ($e = catch_exception())
			{
				return $this->report($this->request, $this->response, $e);
			}
			else
			{
				return $this->report($this->request, $this->response, new RootException(substr($sanitizedBuffer, $pos)));
			}
		}

		return trim($buffer);
	}
}
?>
