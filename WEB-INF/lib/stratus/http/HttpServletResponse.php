<?php
/* $Id: HttpServletResponse.php 224 2005-06-24 00:03:22Z mojavelinux $
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

/**
 * <i>Based on javax.servlet.http.HttpServletResponse</i>
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package stratus.http
 * @access public
 *
 * TODO: encode URL methods, locale setting
 */

def('HttpServletResponse::SC_CONTINUE', 100);

def('HttpServletResponse::SC_OK', 200);

def('HttpServletResponse::SC_MOVED_PERMANENTLY', 301);

def('HttpServletResponse::SC_MOVED_TEMPORARILY', 302);

def('HttpServletResponse::SC_BAD_REQUEST', 400);

def('HttpServletResponse::SC_UNAUTHORIZED', 401);

def('HttpServletResponse::SC_FORBIDDEN', 403);

def('HttpServletResponse::SC_NOT_FOUND', 404);

def('HttpServletResponse::SC_INTERNAL_SERVER_ERROR', 500);

def('HttpServletResponse::SC_SERVICE_UNAVAILABLE', 503);

class HttpServletResponse extends Object
{
	var $headers = array();

	var $cookies = array();

	/**
	 * The date format we will use for creating date headers.
	 * @var string
	 */
	var $format = 'D, d M Y H:i:s T';

	/**
	 * Has this response been committed yet?
	 * @var boolean
	 */
	var $committed = false;

	var $contentType = null;

	var $contentLength = null;

	var $message = null;

	var $status = null;

	var $request = null;

	var $included = false;

	var $suspended = false;

	/**
	 * The buffer which is accumulated between flush operations
	 * @var string
	 */
	var $buffer = null;

	/**
	 * Has the buffer been activated?  We don't want to activate it twice.
	 * @var boolean
	 */
	var $bufferActive = false;

	/**
	 * Error flag.  True if the response is an error report.
	 */
	var $error = false;

	function HttpServletResponse()
	{
		$this->setStatus(c('HttpServletResponse::SC_OK'));
	}

	function getIncluded()
	{
		return $this->included;
	}

	function setIncluded($included)
	{
		$this->included = $included;
	}

	/**
	 * Set the Request with which the Response is associated
	 *
	 * @param string $request
	 * @return void
	 */
	function setRequest(&$request)
	{
		$this->request =& $request;
	}

	function &getRequest()
	{
		return $this->request;
	}

	function getProtocol()
	{
		return $this->request->getProtocol();
	}

	/**
	 * Perform whatever actions are required to flush and close the output
	 * in a single operation, including sending the headers
	 *
	 * @return void
	 */
	function finishResponse()
	{
		/*
		if (!$this->committed &&
			!$this->bufferActive &&
			$this->status >= c('HttpServletResponse::SC_BAD_REQUEST') &&
			is_null($this->contentType))
		{
			$this->setContentType('text/html');	
			echo '<html><head><title>Error Report</title></head><body><h1>HTTP Status ' . $this->status . ' - ' . $this->message . '</h1></body></html>';
			exit;
		}*/

		$this->flushBuffer();
	}

	function flushBuffer()
	{
		if (!$this->committed)
		{
			$this->sendHeaders();
		}

		// we don't want to take on any more output if the response has been suspended
		if (!$this->suspended)
		{
			$this->buffer .= ob_get_contents();
		}

		ob_end_clean();
		echo $this->buffer;
		$this->bufferActive = false;
	}

	function &getCookies()
	{
		return $this->cookies;	
	}

	function getHeader($name)
	{
		if (!isset($this->headers[$name]))
		{
			return null;
		}

		return $this->headers[$name][0];
	}

	/**
	 * Return an array of all the headers names set for this response, or a zero-length
	 * array if no headers have been set.
	 *
	 * @return array
	 */
	function getHeaderNames()
	{
		return array_keys($this->headers);
	}

	function getHeaderValues()
	{
		return array_values($this->headers);
	}

	function getMessage()
	{
		return $this->message;
	}

	function getStatus()
	{
		return $this->status;
	}

	function getStatusMessage($status)
	{
		switch ($status)
		{
			case 100:
				return 'Continue';

			case 200:
				return 'OK';

			case 301:
				return 'Moved Permanently';

			case 302:
				return 'Moved Temporarily';

			case 400:
				return 'Bad Request';

			case 401:
				return 'Unauthorized';

			case 403:
				return 'Forbidden';

			case 404:
				return 'Not Found';

			case 500:
				return 'Internal Server Error';

			case 503:
				return 'Service Unavailable';

			default:
				return 'HTTP Response Status ' + $status;
		}
	}

	function setStatus($status, $message = null)
	{
		$this->status = $status;
		if (is_null($message))
		{
			$this->message = $this->getStatusMessage($status);
		}
		else
		{
			$this->message = $message;
		}
	}

	/**
	 * Send the HTTP response headers, if this has not already occured.
	 *
	 * @return void
	 */
	function sendHeaders()
	{
		if ($this->committed)
		{
			return;
		}

		// Status: header
		$status = $this->getProtocol() . ' ' . $this->status;
		if (!is_null($this->message))
		{
			$status .= ' ' . $this->message;
		}

		// NOTE: PHP seems to ignore the message when sending this header
		// and uses it's default message instead
		header($status);

		if (!is_null($this->getContentType()))
		{
			header('Content-Type: ' . $this->getContentType());
		}

		if (!is_null($this->getContentLength()))
		{
			header('Content-Length: ' . $this->getContentLength());
		}

		foreach ($this->headers as $name => $values)
		{
			foreach ($values as $value)
			{
				header($name . ': ' . $value, false);
			}
		}

		// do the cookie stuff
		// setcookie(
		// 	$cookie->getName(), $cookie->getValue, $cookie->getMaxAge(),
		// 	$cookie->getPath(), $cookie->getDomain(), $cookie->getSecure()
		// );

		$this->committed = true;
	}

	/**
	 * Checks to see if a header exists with the specified name
	 * @param string $name
	 * @return void
	 */
	function containsHeader($name)
	{
		return isset($this->headers[$name]) && count($this->headers[$name]) > 0;
	}

	/**
	 * Send an HttpServletResponse to overwrite (or add if doesn't exist) the header
	 * with the specified name
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	function setHeader($name, $value)
	{
		if ($this->committed)
		{
			return;
		}

		$this->headers[$name] = array($value);
	}

	/**
	 * Send an HttpServletResponse to add the header with the specified name
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	function addHeader($name, $value)
	{
		if ($this->committed)
		{
			return;
		}

		$values =& $this->headers[$name];
		if (!isset($values))
		{
			$values = array();
		}

		$values[] = $value;
	}

	/**
	 * Send an HttpServletResponse to add the given cookie to the array of cookies
	 *
	 * @param Cookie $cookie
	 * @return void
	 */
	function addCookie(&$cookie)
	{
		if ($this->committed)
		{
			return;
		}

		$this->cookies[] =& $cookie;
	}

	/**
	 * Send an temporary redirect to the given location, which is either
	 * absolute or relative to the current page
	 *
	 * TODO: resolve $location to an absolute url, maybe handle automatic insertion of fuse script index.php
	 *
	 * @param string $location
	 * @return void
	 */
	function sendRedirect($location)
	{
		if ($this->committed)
		{
			throw_exception(new IllegalStateException('Response already committed!'));
			return;
		}

		$this->resetBuffer();

		$this->setStatus(c('HttpServletResponse::SC_MOVED_TEMPORARILY'));
		$this->setHeader('Location', $location);

		// cause the response to be finished from the perspective of the application
		$this->setSuspended(true);
	}

	function setContentLength($length)
	{
		if ($this->committed)
		{
			return;
		}

		$this->contentLength = $length;
	}

	function getContentLength()
	{
		return $this->contentLength;
	}

	function getContentType()
	{
		return $this->contentType;
	}

	function setContentType($type)
	{
		if ($this->committed)
		{
			return;
		}

		$this->contentType = $type;
	}

	/**
	 * Send an error response to the client using the status and message.
	 *
	 * @param int $status Status code
	 * @param string $message (optional) Optional message override
	 * @return void
	 */
	function sendError($status, $message = null)
	{
		if ($this->committed)
		{
			// throw error
			return;
		}

		$this->setError();	
		$this->setStatus($status, $message);

		// clear any data that has been buffered
		$this->resetBuffer();
		
		// cause the response to be finished (from the perspective of the application)
		$this->setSuspended(true);
	}

	function addDateHeader($name, $value)
	{
		if ($this->committed)
		{
			return;
		}

		$this->addHeader($name, gmdate($this->format, $value));
	}

	function setDateHeader($name, $value)
	{
		if ($this->committed)
		{
			return;
		}

		$this->setHeader($name, gmdate($this->format, $value));
	}

	/**
	 * Has the output of this response been committed yet?  Internal this method
	 * is not used in place of using the property value
	 *
	 * @return boolean
	 */
	function isCommitted()
	{
		return $this->committed;
	}

    /**
     * Reset the data buffer but not any status or header information.
     *
	 * @return void
     */
	function resetBuffer()
	{
		if ($this->committed)
		{
			// throw Illegal state exception
			return;
		}

		ob_clean();
		$this->buffer = null;
	}

	function reset()
	{
		$this->resetBuffer();
		$this->headers = array();
		$this->cookies = array();
		$this->message = null;
		$this->status = 200;
	}

	/**
	 * By putting the response into the 'suspended' state, we
	 * are saying that no new output can be added to this response.
	 * Effectively it is done.  This method is typically called after an
	 * internal forward or when the response is generating an error report.
	 *
	 * @param boolean $suspended The state in which to put the response
	 */
	function setSuspended($suspended)
	{
		// only if we are not already suspended do we grab what is in the
		// response up to this point
		if (!$this->suspended)
		{
			$this->buffer .= ob_get_contents();
		}
		
		// clean the output and ignore any new output
		ob_clean();
		$this->suspended = $suspended;
	}

	function isSuspended()
	{
		return $this->suspended;
	}

	function startBuffer()
	{
		if (!$this->bufferActive)
		{
			ob_start();
		}
	}

	/**
	 * Set the error flag
	 *
	 * @return void
	 */
	function setError()
	{
		$this->error = true;
	}

	/**
	 * Get the error flag
	 *
	 * @return boolean
	 */
	function isError()
	{
		return $this->error;
	}

	/**
	 * Return the number of bytes (length) written to the output stream
	 *
	 * @return int
	 */
	function getContentCount()
	{
		return ob_get_length();
	}
}
?>
