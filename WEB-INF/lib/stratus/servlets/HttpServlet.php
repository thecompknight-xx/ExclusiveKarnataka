<?php
/* $Id: HttpServlet.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package stratus.servlets
 * @abstract
 * @author Dan Allen
 */
class HttpServlet extends Object // implements GenericServlet
{
	/**
	 * Implementation of ServletConfig object created from the web.xml file for this servlet
	 *
	 * @var ServletConfig
	 * @access private
	 */
	var $_config;

	/**
	 * Called by the servlet container to indicate to a servlet that the
	 * servlet is being taken out of service.
	 *
	 * @return void
	 */
	function destroy()
	{
	}

	/**
	 * Returns a {@link String} containing the value of the named
	 * initialization parameter, or <kbd>null</kbd> if the parameter does not
	 * exist.
	 *
	 * This method is supplied for convenience. It gets the value of the
	 * named parameter from the servlet's {@link ServletConfig} object.
	 *
	 * @param string $name The name of the initialization parameter
	 * @return string The value of the initialization parameter
	 */
	function getInitParameter($name)
	{
		return $this->_config->getInitParameter($name);
	}

	/**
	 * Returns the names of the servlet's initialization parameters as an array
	 * 
	 * <p>This method is supplied for convenience. It gets the parameter names
	 * from the servlet's {@link ServletConfig} object. 
	 *
	 * @return array
	 */
	function getInitParameterNames()
	{
		return $this->_config->getInitParameterNames();
	}

	/**
	 * Returns this servlet's {@link ServletConfig} object.
	 * It is necessary to call this function since the data member is private.
	 *
	 * Please be careful with this object.  PHP does not have the ability to return
	 * an interface and the underlying config object has a lot more methods available
	 * then should be publically seen.  Follow the {@link ServletConfig} interface
	 * when accessing these methods.
	 *
	 * @return ServletConfig
	 */
	function &getServletConfig()
	{
		return $this->_config;
	}

	/**
	 * Returns a reference to the {@link ServletContext} in which this servlet
	 * is running.
	 *
	 * <p>This method is supplied for convenience. It gets the context from the
	 * servlet's {@link ServletConfig} object.
	 *
	 * @return ServletContext
	 */
	function &getServletContext()
	{
		return $this->_config->getServletContext();
	}

	/**
	 * Called by the servlet container to indicate to a servlet that the
	 * servlet is being placed into service.
	 *
	 * <p>This implementation stores the {@link ServletConfig} object it
	 * receives from the servlet container for later use.  When overriding this
	 * method, call {@link super.init($config)}!!!</p>
	 *
	 * @param ServletConfig $config
	 * @return void
	 */
	function initConfig(&$config)
	{
		$this->_config =& $config;
		$this->init();
	}

	/**
	 * For convenience the methods called by the inherited servlet and
	 * the original servlet have been split so that no call to parent::init($config)
	 * is required.  Hence this can be implemented with no arguments.
	 *
	 * @return void
	 */
	function init()
	{
		; // default implementation empty
	}

	/**
	 * Returns the name of this servlet instance.
	 *
	 * @return string The name of this servlet instance
	 */
	function getServletName()
	{
		return $this->_config->getServletName();
	}

	/**
	 * Return (the first) mapping used to filter a web request to this servlet.
	 * This information can be used to create a link to another url which will
	 * be directed to this servlet.  The format is the same as the url-pattern
	 * used in the web.xml file for the <servlet> tag.
	 *
	 * @return string
	 */
	function getServletMapping()
	{
		return $this->_config->getServletMapping();
	}

	/**
	 * Called by the server (via the {@link service()} method) to allow a
	 * servlet to handle a GET request. 
	 *
	 * @param HttpServletRequest $request
	 * @param HttpServletRequest $response
	 * @return void
	 */
	function doGet(&$request, &$response)
	{
		die('HTTP method not implemented');
	}

	/**
	 * Called by the server (via the {@link service()} method) to allow a
	 * servlet to handle a POST request. 
	 *
	 * @param HttpServletRequest $request
	 * @param HttpServletRequest $response
	 * @return void
	 */
	function doPost(&$request, &$response)
	{
		die('HTTP method not implemented');
	}

	/**
	 * Receives standard HTTP requests from the public {@link service()}
	 * method and dispatches them to the <i>doXXX</i> methods
	 * defined in this class.
	 *
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 * @return void
	 */
	function service(&$request, &$response)
	{
		$method = $request->getMethod();

		switch ($method)
		{
			case 'GET':
				$this->doGet($request, $response);
			break;

			case 'POST':
				$this->doPost($request, $response);
			break;
			
			default:
				die('HTTP method not implemented');
		}
	}
}
?>
