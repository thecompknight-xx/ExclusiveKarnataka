<?php
/* $Id: HttpServletRequest.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.collections.HashMap');
import('horizon.beanutils.ConvertUtils');

import('stratus.http.HttpSession');
import('stratus.util.ServletUtils');
import('stratus.ServletConstants');

/**
 * Provides an interface for the request properties of the HTTP protocol.
 *
 * Most of the data collection is actually done by PHP prior to the
 * instantiation of this class and as such, this class merely serves as a
 * wrapper to the native PHP implicit collections.
 *
 * <i>Based on javax.servlet.http.HttpServletRequest</i>
 *
 * @author Dan Allen
 * @package stratus.http
 * @access public
 */
class HttpServletRequest extends Object
{
	/**
	 * The response with which this request is associated.
	 * @var HttpServletResponse
	 */
	var $response = null;

	/**
	 * The session with which this request is associated.
	 @ var HttpSession
	 */
	var $session = null;

	/**
	 * Array of headers sent by the request
	 * @var array
	 */
	var $headers;

	/**
	 * Array of parameters sent by the request (either GET or POST)
	 * @var array
	 */
	var $parameters = null;

	/**
	 * Array of attributes set into the request scope (usually from the action)
	 * @var array
	 */
	var $attributes = array();

	/**
	 * Return the Internet Protocol (IP) address of the client that sent this
	 * request.
	 * @var int
	 */
	var $remoteAddr = 0;

	/**
	 * Was this request received on a secure channel?
	 * @var boolean
	 */
	var $secure = false;

	/**
	 * The request URI associated with this request.
	 * @var string
	 */
	var $requestURI = null;

	/**
	 * The server name associated with this Request.
	 * @var string
	 */
	var $serverName = null;

	/**
	 * The server port associated with this Request.
	 * @var int
	 */
	var $serverPort = -1;

	/**
	 * The scheme associated with this Request.
	 * @var string
	 */
	var $scheme = null;

	/**
	 * The protocol name and version associated with this Request.
	 * @var string
	 */
	var $protocol = null;

	/**
	 * The query string for this request.
	 * @var string
	 */
	var $queryString = null;

	/**
	 * The input data coming from the request (corresponds to the input stream in java servlets),
	 * such as post data and files
	 * @var array
	 */
	var $input = array();

	/**
	 * The request method associated with this Request.
	 * @var string
	 */
	var $method = null;

	/**
	 * The context path for this request.
	 * @var string
	 */
	var $contextPath = '';

	/**
	 * The servlet path for this request.
	 * @var string
	 */
	var $servletPath = null;

	/**
	 * The path information for this request.
	 * @var string
	 */
	var $pathInfo = null;

	/**
	 * The Context within which this Request is being processed.
	 * @var StandardContext
	 */
	var $context = null;

	/**
	 * The preferred locales associated with this request
	 *
	 * @var array
	 */
	var $locales = array();

	/**
	 * The default Locale if not are specified
	 *
	 * @var string
	 */
	var $defaultLocale = 'en_US';

	function HttpServletRequest()
	{
	}

	/**
	 * Get the value of the attribute.  If it doesn't exist, <i>null</i> will be returned.
	 * @return mixed
	 */
	function &getAttribute($name)
	{
		if (isset($this->attributes[$name]))
		{
			return $this->attributes[$name];
		}

		$nil =& ref(null);
		return $nil;
	}

	/**
	 * Set the specified request attribute to the specified value.
	 * @return void
	 */
	function setAttribute($name, &$value)
	{
		if (is_null($value))
		{
			$this->removeAttribute($name);
			return;
		}

		$this->attributes[$name] =& $value;
	}

	/**
	 * Removed the specified request attribute if it exists.
	 * @return void
	 */
	function removeAttribute($name)
	{
		unset($this->attributes[$name]);
	}

	/**
	 * Return the collection of attribute names.
	 */
	function getAttributeNames()
	{
		return array_keys($this->attributes);
	}

	/**
	 * Get the value of the parameter.  If it doesn't exist, <i>null</i> will be returned.
	 * If it exists but is empty, it will be a string.
	 * @return mixed
	 */
	function &getParameter($name)
	{
		$this->parseParameters();
		if (isset($this->parameters[$name]))
		{
			return $this->parameters[$name][0];
		}
		else
		{
			return ref(null);
		}
	}

	function &getParameterValues($name)
	{
		$this->parseParameters();
		if (isset($this->parameters[$name]))
		{
			return $this->parameters[$name];
		}
		else
		{
			return ref(null);
		}
	}

	/**
	 * Return a RequestDispatcher that wraps the resource at the
	 * specified path, which may be interpreted as relative to the current
	 * request path.
	 *
	 * @param string $path
	 * @return RequestDispatcher
	 */
	function getRequestDispatcher($path)
	{
		if (is_null($this->context))
		{
			return null;
		}

		if (is_null($path))
		{
			return null;
		}
		else if ($path[0] == '/')
		{
			$servletContext =& $this->context->getServletContext();
			return $servletContext->getRequestDispatcher($path);
		}

		// @todo need to check for a INC_SERVLET_PATH_KEY here, I guess from an include()
		$servletPath = $this->getServletPath();
		$pos = strrpos($servletPath, '/');	
		if ($pos !== false)
		{
			$relative = substr($servletPath, 0, $pos + 1) . $path;
		}
		else
		{
			$relative = $servletPath . $path;
		}

		$servletContext =& $this->context->getServletContext();
		return $servletContext->getRequestDispatcher(ServletUtils::normalize($relative));
	}

	/**
	 * Returns an array of strings containing the names of the parameters
	 * contained in this request. If the request has no parameters, the method
	 * returns an empty array.
	 *
	 * @return array
	 */
	function getParameterNames()
	{
		$this->parseParameters();
		return array_keys($this->parameters);
	}

	/**
	 * Returns the set of parameters as a HashMap array.
	 *
	 * @return array
	 */
	function &getParameterMap()
	{
		$this->parseParameters();
		$params =& new HashMap($this->parameters);
		return $params;
	}

	/**
	 * Set the specified request variable to the specified value.
	 * @return void
	 */
	function setParameter($name, &$value)
	{
		if (is_null($value))
		{
			$this->removeParameter($name);
			return;
		}

		$this->parameters[$name] =& $value;
	}

	/**
	 * Remove the specified request variable if it exists.
	 * @return void
	 */
	function removeParameter($name)
	{
		unset($this->parameters[$name]);
	}

	/**
	 * Determine if parameter exists.
	 * @return boolean
	 */
	function parameterExists($name)
	{
		return isset($this->parameters[$name]);
	}

	/**
	 * Return the method name of the http request.
	 *
	 * @return string
	 */
	function getMethod()
	{
		return $this->method;
	}

	/**
	 * Set the method name of the http request
	 *
	 * @return void
	 */
	function setMethod($method)
	{
		$this->method = $method;
	}

	/**
	 * Get the running session or create a new one if specified and it doesn't
	 * exist. If a session is not going to be created, then return
	 * <kbd>null</kbd>.
	 * NOTE: It assumed that the same request instance is passed around
	 * as a reference throughout the request.
	 *
	 * @return HttpSession
	 */
	function &getSession($create = true)
	{
		// QUESTION: perhaps check for session_started()

		if (is_null($this->session) && $create)
		{
			$this->session =& new HttpSession($this->contextPath);
		}

		return $this->session;
	}

	/**
	 * Returns the part of this request's URL from the protocol name up to
	 * the query string in the first line of the HTTP request.
	 *
	 * @return string
	 */
	function getRequestURI()
	{
		return $this->requestURI;
	}

	function setRequestURI($requestURI)
	{
		$this->requestURI = $requestURI;
	}

	/**
	 * Returns the name of the scheme used to make this request, for example,
	 * http, https, or ftp. Different schemes have different rules for
	 * constructing URLs
	 */
	function getScheme()
	{
		return $this->scheme;
	}

	function setScheme($scheme)
	{
		$this->scheme = $scheme;
	}

	/**
	 * Reconstructs the URL the client used to make the request.
	 * The returned URL contains a protocol, server name, port number, and
	 * server path, but it does not include query string parameters.
	 *
	 * @return string
	 */
	function getRequestURL()
	{
		$scheme = $this->getScheme();
		$port = $this->getServerPort();

		$url = $scheme . '://' . $this->getServerName();

		// add the port if it is not using the default protocol ports
		if (($scheme == 'http' && $port != 80) || ($scheme == 'https' && $port != 443))
		{
			$url .= ':' . $port;
		}

		$url .= $this->getRequestURI();

		return $url;
	}

	/**
	 * Get the qualified hostname of the server.
	 *
	 * @return string
	 */
	function getServerName()
	{
		return $this->serverName;
	}

	function setServerName($serverName)
	{
		$this->serverName = $serverName;
	}

	/**
	 * Get the port on which the request was made
	 *
	 * @return int
	 */
	function getServerPort()
	{
		return $this->serverPort;
	}

	function setServerPort($serverPort)
	{
		$this->serverPort = $serverPort;
	}

	/**
	 * Determine if this was a secure request.
	 *
	 * @return boolean
	 */
	function isSecure()
	{
		return $this->secure;
	}

	/**
	 * Get the qualified hostname of the client
	 * If no qualified hostname exists, the IP address is used
	 *
	 * @return string
	 */
	function getRemoteHost()
	{
		return gethostbyname($this->remoteAddr);
	}

	/**
	 * Get the IP address of the client
	 *
	 * @return string
	 */
	function getRemoteAddr()
	{
		return $this->remoteAddr;
	}

	function setRemoteAddr($remoteAddr)
	{
		$this->remoteAddr = $remoteAddr;
	}

	/**
	 * Add a Locale to the set of preferred Locales for this Request.  The
	 * first added Locale will be the first one returned by getLocales().
	 *
	 * @param string $locale
	 * @return void
	 */
	function addLocale($locale)
	{
		$this->locales[] = $locale;
	}

	/**
	 * Get the accepting locale for the client.  If no locale is
	 * sent, use the default from the server
	 *
	 * @return string
	 */
	function getLocale()
	{
		if (count($this->locales) > 0)
		{
			return $this->locales[0];
		}
		else
		{
			return $this->defaultLocale;
		}
	}

	/**
	 * Return the set of preferred Locales that the client will accept content
	 * in, based on the values for any <kbd>Accept-Language</kbd> headers
	 * that were encountered.  If the request did not specify a preferred
	 * language, the server's default Locale is returned.
	 *
	 * @return array
	 */
	function getLocales()
	{
		if (count($this->locales) > 0)
		{
			return $this->locales;
		}
		else
		{
			return array($this->defaultLocale);
		}
	}

	/**
	 * Get the file path on the server for the requested script
	 *
	 * @param string $path The virtual path to the desired resource
	 *
	 * @return string
	 */
	function getRealPath($path)
	{
		if (is_null($this->context))
		{
			return null;
		}

		$servletContext =& $this->context->getServletContext();
		if (is_null($servletContext))
		{
			return null;
		}

		return $servletContext->getRealPath($path);
	}

	/**
	 * Returns the information after the script name and before the query string
	 * If the uri is /webapp/index.php/hello.do?foo=bar it would return /hello.do
	 * @return string
	 */
	function getPathInfo()
	{
		return $this->pathInfo;
	}

	function setPathInfo($pathInfo)
	{
		$this->pathInfo = $pathInfo;
	}

	/**
	 * Returns any extra path information after the servlet name but before the
	 * query string, and translates it to a real path.
	 *
	 * @return string
	 */
	 function getPathTranslated()
	 {
		if (is_null($this->context))
		{
			return null;
		}

		if (is_null($this->pathInfo))
		{
			return null;
		}

		$servletContext =& $this->context->getServletContext();
		if (is_null($servletContext))
		{
			return null;
		}

		return $servletContext->getRealPath($this->pathInfo);
	 }

	/**
	 * Get the query string, which is the part of the URL after the first ?
	 *
	 * @return string
	 */
	function getQueryString()
	{
		return $this->queryString;
	}

	function setQueryString($queryString)
	{
		$this->queryString = $queryString;
	}

	/**
	 * The path prefix of the current servlet.  If this context is the
	 * "default" context rooted at the base of the web server's URL namespace,
	 * this path will be an empty string. Otherwise, if the context is not
	 * rooted at the root of the server's namespace, the path starts with a'/'
	 * character but does not end with a'/' character.
	 *
	 * NOTE: Since PHP has to inject the controller file (index.php) after the
	 * context, this method takes an optional flag which, when true, will append
	 * the controller script.
	 *
	 * @return string
	 */
	function getContextPath($controller = false)
	{
		if (!$controller)
		{
			return $this->contextPath;
		}
		else
		{
			return $this->generateControllerPath($this->contextPath);
		}
	}

	/**
	 * The controller path is that base url that routes requests to the stratus
	 * servlet container.  The context-param controlAllResources is checked to see if
	 * apache is handling the serving of these resources (no passthru to the
	 * container) or the DefaultServlet is going to serve them up.
	 *
	 * @param $contextPath string The context path
	 * @param $requiresDispatch boolean (optional) instructs whether or not the
	 *        path must always go through the container (prehaps a PHP file or a
	 *        servlet path)
	 * TODO: optimize this by caching init params into class
	 */
	function generateControllerPath($contextPath, $requiresDispatch = true)
	{
		$serveWithContainer = true;

		// check to see if we need to hand this off directly or go through the
		// front controller
		if (!$requiresDispatch)
		{
			$sc =& $this->context->getServletContext();
			$serveWithContainer = ConvertUtils::convert($sc->getInitParameter('controlAllResources'), 'boolean');
		}

		// add controller script to serve with container, but only if
		// we are not using mod_rewrite to insert the controller script
		if ($serveWithContainer)
		{
			$sc =& $this->context->getServletContext();
			$useRewrite = ConvertUtils::convert($sc->getInitParameter('useRewrite'), 'boolean');
			if (!$useRewrite)
			{
				$contextPath .= c('ServletConstants::CONTROLLER_SCRIPT');
			}
		}

		return $contextPath;
	}

	function setContextPath($contextPath)
	{
		$this->contextPath = $contextPath;
	}

	/**
	 * Returns the value of the specified request header. If the request did
	 * not include a header of the specified name, this method returns null.
	 * @return string
	 */
	function getHeader($name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : null;
	}

	/**
	 * Return the cookies as an array
	 * @return array
	 */
	function &getCookies()
	{
		return $_COOKIE;
	}

	/**
	 * Returns the login of the user making this request, if the user has been
	 * authenticated, or null if the user has not been authenticated.
	 * @return string
	 */
	function getRemoteUser()
	{
		return null;
	}

	/**
	 * The portion or the path URI that is used to select the servlet that will
	 * process this request. This path starts with a'/' character.
	 *
	 * @return string
	 */
	function getServletPath()
	{
		return $this->servletPath;
	}

	function setServletPath($servletPath)
	{
		$this->servletPath = $servletPath;
	}

	/**
	 * Return the protocol and version used to make this Request.
	 *
	 * @return string
	 */
	function getProtocol()
	{
		return $this->protocol;
	}

	function setProtocol($protocol)
	{
		$this->protocol = $protocol;
	}

	/**
	 * @access protected
	 */
	function parseParameters()
	{
		static $parsed = false;
		
		if ($parsed)
		{
			return;
		}

		$this->parameters = array();

		// NOTE: parse_str replaces dots "." with underscores "_" in the
		// parameter names, so we use chr(149) (a bullet) as a temporary
		// place holder
		parse_str($this->getQueryString(), $results);
		foreach ($results as $name => $values)
		{
			settype($values, 'array');
			$name = str_replace(chr(183), '.', $name);
			$this->parameters[$name] = $values;
		}

		// input is the post data
		foreach ($this->getInput() as $name => $values)
		{
			settype($values, 'array');
			$name = str_replace(chr(183), '.', $name);
			$this->parameters[$name] = array_values($values);
		}

		$parsed = true;	
	}

	function &getInput()
	{
		return $this->input;
	}

	function setInput(&$input)
	{
		$this->input =& $input;
	}

	function setResponse(&$response)
	{
		$this->response =& $response;
	}

	function setContext(&$context)
	{
		$this->context =& $context;
	}

	function &getContext()
	{
		return $this->context;
	}
}
?>
