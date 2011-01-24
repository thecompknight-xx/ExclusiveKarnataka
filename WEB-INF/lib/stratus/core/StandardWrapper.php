<?php
/* $Id: StandardWrapper.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.util.logging.Logger');

/**
 * @package stratus.core
 * @author Dan Allen
 *
 * TODO: would probably be good to create a StandardWrapperFacade which covers
 * this object with the publicly available methods in ServletConfig.  When doing
 * so be sure to add getServletMapping() which I added so that we don't have to
 * reparse the web.xml file in studs init().
 */
class StandardWrapper /* implements ServletConfig, Wrapper */
{
	var $instance = null;

	var $parameters = array();

	var $servletClass = null;

	/**
	 * The parent of the wrapper is the StandardContext instance
	 * @var StandardContext
	 */
	var $parent = null;

	var $name = null;

	/**
	 * The load-on-startup order value (negative value means load on first
	 * call) for this servlet.
	 * @var int
	 */
	var $loadOnStartup = -1;

	var $available = true;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('stratus.core.StandardWrapper');
		return $logger;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function getLoadOnStartup()
	{
		return $this->loadOnStartup;
	}

	function getName()
	{
		return $this->name;
	}

	function getServletClass()
	{
		return $this->servletClass;
	}

	function setLoadOnStartup($order)
	{
		$this->loadOnStartup = intval($order);
	}

	function setServletClass($class)
	{
		$this->servletClass = $class;
	}

	function setServletName($name)
	{
		$this->setName($name);
	}

	function addInitParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	function findInitParameter($name)
	{
		if (!isset($this->parameters[$name]))
		{
			return null;
		}

		return $this->parameters[$name];
	}

	function findInitParameters()
	{
		return $this->parameters;
	}

	function &allocate()
	{
		if (is_null($this->instance))
		{
			$this->instance =& $this->loadServlet();
			$this->parent->setContextModified(true);
		}

		return $this->instance;
	}

	/**
	 * NOTE: since we are single threaded, we really don't need to pass in the servlet instance
	 * here since it will be the same exact one as the 'instance' property
	 */
	function deallocate()
	{
		; // does nothing right now
	}

	/**
	 * Used by {@link StandardContext::loadOnStartup()}, this method
	 * loads the servlet and directly assigns the instance to the
	 * class instance variable.
	 *
	 * @throws ServletException if loading fails or an error occurs in the
	 * 	init() method of the servlet
	 * @return void
	 */
	function load()
	{
		$this->instance =& $this->loadServlet();
	}

	/**
	 * Load and initialize an instance of this servlet, if there is not already
	 * at least one initialized instance.  This can be used, for example, to
	 * load servlets that are marked in the deployment descriptor to be loaded
	 * at server startup time.  The servlet will now be ready to have it's
	 * {@link service()} method called.
	 *
	 * NOTE: I combined load() and loadServlet() for simplicity
	 *
	 * @return void
	 */
	function &loadServlet()
	{
		if (!is_null($this->instance))
		{
			return $this->instance;
		}

		$log =& StandardWrapper::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Initializing new servlet instance for ' . $this->servletClass);
		}

		$servlet = null;

		$clazz =& Clazz::forName($this->servletClass);
		if (bubble_exception()) return;
		$servlet =& $clazz->newInstance();
		// if this is a ContainerServlet, we need to set the wrapper
		if (method_exists($servlet, 'setwrapper'))
		{
			$servlet->setWrapper($this);
		}

		// we are actually calling the overloaded init() method which takes a ServletConfig as a param
		// try {
		$servlet->initConfig($this);
		// } catch (UnavailableException $e) {
		if ($e = catch_exception('UnavailableException'))
		{
			$log->error('Servlet initialization failed with the following error: ' . $e->getMessage(), $e);
			$this->setAvailable(false);
		}
		// }
		
		return $servlet;
	}

	function removeInitParameter($name)
	{
		unset($this->parameters[$name]);
	}

	function getInitParameter($name)
	{
		return $this->findInitParameter($name);
	}

	function getInitParameterNames()
	{
		return array_keys($this->parameters);
	}

	function &getServletContext()
	{
		return $this->parent->getServletContext();
	}

	function getServletName()
	{
		return $this->getName();
	}

	// NOTE: I added this because it just seems to make sense
	function getServletMapping()
	{
		return array_search($this->name, $this->parent->findServletMappings());
	}

	function &getParent()
	{
		return $this->parent;
	}

	function setParent(&$parent)
	{
		$this->parent =& $parent;
	}

	function setAvailable($available)
	{
		$this->available = $available;
	}

	function getAvailable()
	{
		return $this->available;
	}

	function isUnavailable()
	{
		return !$this->available;
	}
}
?>
