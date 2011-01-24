<?php
/* $Id: StandardContext.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.io.File');
import('horizon.io.FileWriter');
import('horizon.util.logging.Logger');
import('stratus.ServletConstants');
import('stratus.ServletException');
import('stratus.core.ApplicationContext');
import('stratus.http.HttpServletResponse');

def('StandardContext::SERVLET_WORK_DIR', '/WEB-INF/work');
def('StandardContext::SERVLET_CONTEXT_CACHE', '/ServletContext.ser');
def('StandardContext::WEB_APP_CONFIG', '/WEB-INF/web.xml');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package stratus.core
 */
class StandardContext extends Object /* implements Container, Context */
{
	/**
	 * Flag which specifies if this context is correctly configured
	 * @var boolean
	 */
	var $configured = false;

	/**
	 * Flag which specifies if the context is undergoing the startup process
	 * @var boolean
	 */
	var $startup = false;

	var $name = null;

	var $context = null;

	var $displayName = null;

	var $parameters = array();

	var $servletMappings = array();

	var $sessionTimeout = 30;

	var $children = array();

	var $welcomeFiles = array();

	var $contextModified = false;

	/**
	 * Paused while a reload event occurs, we don't want to write a stale servlet
	 * context to disk
	 * @boolean
	 */
	var $paused = false;

	var $mimeMappings = array();

	var $workDir = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('stratus.core.StandardContext');
		return $logger;
	}

	function StandardContext()
	{
		// to start out with, the context is modified
		$this->contextModified = true;
	}

	function getConfigured()
	{
		return $this->configured;
	}

	function setConfigured($configured)
	{
		$this->configured = $configured;
	}

	function getStartup()
	{
		return $this->startup;
	}

	function setStartup($startup)
	{
		$this->startup = $startup;
	}

	/**
	 * These methods are added in place of the "listeners" framework
	 * to signal that the context has been updated an needs to be
	 * resaved so that all requests see the updated context.
	 *
	 * QUESTION: do we want to synchronize with filesystem immediately, or
	 * wait for sleep?  Might have to get feedback.
	 */
	function setContextModified($contextModified)
	{
		$this->contextModified = $contextModified;
	}

	function isContextModified()
	{
		return $this->contextModified;
	}

	function getDisplayName()
	{
		return $this->displayName;
	}

	function setDisplayName($displayName)
	{
		$this->displayName = $displayName;
	}

	/**
	 * Return the context path for this Context, which is
	 * equivalent to its name
	 */
	function getPath()
	{
		return $this->getName();
	}

	function getName()
	{
		return $this->name;
	}

	/**
	 * The name is just the context path under which this container is running.
	 *
	 * @param string $name The context path beginning with '/' or empty string
	 * if root context
	 */
	function setName($name)
	{
		$this->name = $name;
	}

	function getWorkDir()
	{
		return $this->workDir;
	}

	function setWorkDir($workDir)
	{
		$this->workDir = $workDir;
		//$this->_postWorkDirectory();
	}

	/**
	 * Get the facade for the servlet context which only exposes the public methods
	 *
	 * @return ApplicationContext
	 */
	function &getServletContext()
	{
		if (is_null($this->context))
		{
			$this->context =& new ApplicationContext($this->getBasePath(), $this);
			$this->setContextModified(true);
		}

		return $this->context;
	}

	function getSessionTimeout()
	{
		return $this->sessionTimeout;
	}

	function setSessionTimeout($sessionTimeout)
	{
		$this->sessionTimeout = $sessionTimeout;
	}

	/**
	 * Add a child Wrapper.  A wrapper is simply a servlet container.  Wrappers
	 * are either created in the web.xml file digesting or as an InvokerServlet
	 * which creates a servlet wrapper dynamically upon the first call.
	 *
	 * @param Wrapper $child The servlet container to be added
	 */
	function addChild(&$child)
	{
		$child->setParent($this);
		$this->children[$child->getName()] =& $child;	
	}

	function addParameter($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	function addServletMapping($pattern, $name)
	{
		$this->servletMappings[$pattern] = $name;
	}

	function addMimeMapping($extension, $mimeType)
	{
		$this->mimeMappings[$extension] = $mimeType;
	}

	function addWelcomeFile($name)
	{
		$this->welcomeFiles[] = $name;
		$this->_postWelcomeFiles();
	}

    /**
     * Return <kbd>true</kbd> if the specified welcome file is defined
     * for this Context; otherwise return <kbd>false</kbd>.
     *
     * @param string $name Welcome file to verify
	 * @return boolean
     */
	function findWelcomeFile($name)
	{
		return in_array($name, $this->welcomeFiles);
	}

    /**
     * Return the set of welcome files defined for this Context.  If none are
     * defined, a zero-length array is returned.
	 *
	 * @return array
     */
	function &findWelcomeFiles()
	{
		return $this->welcomeFiles;
	}

	/**
	 * Remove the specified welcome file name from the list recognized by this
	 * Context.
	 *
	 * @param string $name Name of the welcome file to be removed
	 * @return void
	 */
	function removeWelcomeFile($name)
	{
		$index = array_search($name, $this->welcomeFiles);
		if (is_int($index))
		{
			unset($this->welcomeFiles[$index]);
			$this->welcomeFiles = array_values($this->welcomeFiles);
		}
	}

	/**
	 * Return the MIME type to which the specified extension is mapped, if
	 * any, otherwise return <kbd>null</kbd>
	 *
	 * @param string $extension Extension to map to a MIME type
	 * @return string The MIME type mapped to the extension
	 */
	function findMimeMapping($extension)
	{
		return isset($this->mimeMappings[$extension]) ? $this->mimeMappings[$extension] : null;	
	}

	/**
	 * Return the extensions for which MIME mappings are defined.  If there are
	 * none, a zero-length array is returned.
	 *
	 * @return array
	 */
	function findMimeMappings()
	{
		return array_keys($this->mimeMappings);
	}

	/**
	 * Remove the MIME mapping for the specified extension, if it exists;
	 * otherwise, no action is taken.
	 *
	 * @param string $extension Extension to map to a MIME type
	 * @return void
	 */
	function removeMimeMapping($extension)
	{
		unset($this->mimeMappings[$extension]);
	}

	function &findParameter($name)
	{
		if (!isset($this->parameters[$name]))
		{
			return ref(null);
		}

		return $this->parameters[$name];
	}

	function &findParameters()
	{
		return $this->parameters;
	}

	function &findServletMapping($name)
	{
		$nil =& ref(null);
		if (!isset($this->servletMappings[$name]))
		{
			return $nil;
		}

		return $this->servletMappings[$name];
	}

	function &findServletMappings()
	{
		return $this->servletMappings;
	}

	function removeServletMapping($pattern)
	{
		unset($this->servletMappings[$pattern]);
	}

	function removeParameter($name)
	{
		unset($this->parameter[$name]);
	}

	/**
	 * Return the set of child servlets associated with this
	 * context.
	 *
	 * NOTE: This method returns a copy of all the references to the children.
	 */
	function findChildren()
	{
		return array_values($this->children);
	}

	function &findChild($name)
	{
		if (is_null($name))
		{
			return ref(null);
		}

		return $this->children[$name];
	}

	/**
	 * This "starts" our context, initializing the servlet context
	 * and stuffing in configuration settings for use by the app.
	 */
	function start()
	{
		$this->startup = false;
		// FIXME: what are we doing here with the configured flag?
		//$this->setConfigured(false);
		
		$this->_postWorkDirectory();
		$this->_postWelcomeFiles();

		// TODO: put message resources into context

		$this->loadOnStartup($this->findChildren());
		$this->startup = true;
	}

	/**
	 * Load and initialize all servlets marked "load-on-startup" in the web
	 * application deployment descriptor.
	 * NOTE: we might not really need to keep such a strict handling of the order
	 *
	 * @param array $children An array of references to wrappers for defined servlets
	 * @return void
	 */
	function loadOnStartup($children)
	{
		$list = array();
		// get a list of servlets that are to be loaded, keyed on
		// the load-on-startup number
		for ($i = 0; $i < sizeof($children); $i++)
		{
			$wrapper =& $children[$i];
			$order = $wrapper->getLoadOnStartup();
			if ($order < 0)
			{
				continue;
			}

			if ($order == 0)
			{
				$order = getrandmax();
			}

			if (!isset($list[$order]))
			{
				$list[$order] = array();
			}

			$list[$order][] =& $wrapper;
		}

		$list = array_values($list);
		for ($i = 0; $i < sizeof($list); $i++)
		{
			$sublist = $list[$i];
			for ($j = 0; $j < sizeof($sublist); $j++)
			{
				$wrapper =& $sublist[$j];
				// try {
				$wrapper->load();
				// } catch (ServletException $e) {
				if ($e = catch_exception('ServletException'))
				{
					$log =& StandardContext::getLog();
					$log->warn('Servlet ' . $this->getName() . ' threw load() exception.', $e);
					// NOTE: not fatal at application startup, it will be fatal
					// later when the servlet is needed
				}
				// }
			}
		}
	}

	/**
	 * Return the child Container that should be used to process this Request,
	 * based upon its characteristics.  If no such child Container can be
	 * identified, return <kbd>null</kbd> instead.
	 *
	 * An example request would be http://localhost/webapp/index.php/action.do?foo=bar
	 * so our pathinfo is really our requestURI in this case
	 *
	 * NOTE: I merged the StandardHostMapper class with the StandardContext for simplicity
	 *
	 * @param HttpServletRequest
	 * @return StandardWrapper
	 */
	function &map(&$request)
	{
		$contextPath = $request->getContextPath();
		// NOTE: the requestURI comes from the pathInfo that follows our controller file (index.php)
		// initially the pathInfo is null since we don't know what constitutes the extra information
		// until we section off the servlet mapping, and then we can say that the rest is pathInfo

		// NOTE: no need to decode requestURI as PHP already does this for
		// us...normally we would have to decode it at this point
		$requestURI = $request->getRequestURI();
		$relativeURI = substr($requestURI, strlen($contextPath));

		$wrapper = null;
		$servletPath = $relativeURI;
		$pathInfo = null;
		$name = null;

		// Rule 1: Exact Match
		if (is_null($wrapper))
		{
			if ($relativeURI != '/')
			{
				$name = $this->findServletMapping($relativeURI);
			}

			if (!is_null($name))
			{
				$wrapper =& $this->findChild($name);
			}

			if (!is_null($wrapper))
			{
				$servletPath = $relativeURI;
				$pathInfo = null;
			}
		}

		// Rule 2: Prefix Match
		if (is_null($wrapper))
		{
			$servletPath = $relativeURI;
			while (true)
			{
				$name = $this->findServletMapping($servletPath . '/*');
				if (!is_null($name))
				{
					$wrapper =& $this->findChild($name);
				}
				
				if (!is_null($wrapper))
				{
					$pathInfo = substr($relativeURI, strlen($servletPath));
					if (strlen($pathInfo) == 0)
					{
						$pathInfo = null;
					}

					break;
				}

				$slash = strrpos($servletPath, '/');
				if ($slash === false)
				{
					break;	
				}

				$servletPath = substr($servletPath, 0, $slash);
			}
		}

		// Rule 3: Extension Match
		if (is_null($wrapper))
		{
			$slash = strrpos($relativeURI, '/');
			if ($slash !== false)
			{
				$last = substr($relativeURI, $slash);
				$period = strrpos($last, '.');
				if ($period !== false)
				{
					$pattern = '*' . substr($last, $period);
					$name = $this->findServletMapping($pattern);
					if (!is_null($name))
					{
						$wrapper =& $this->findChild($name);
					}

					if (!is_null($wrapper))
					{
						$servletPath = $relativeURI;
						$pathInfo = null;
					}
				}
			}
		}

		// Rule 4: Default Match
		if (is_null($wrapper))
		{
			$name = $this->findServletMapping('/');
			if (!is_null($name))
			{
				$wrapper =& $this->findChild($name);
			}

			if (!is_null($wrapper))
			{
				$servletPath = $relativeURI;
				$pathInfo = null;
			}
		}

		$request->setServletPath($servletPath);
		$request->setPathInfo($pathInfo);

		return $wrapper;
	}

	/**
	 * <b>IMPLEMENTATION NOTE</b>: There is a major merge going on here.  Usually
	 * the {@link invoke()} method calls the method in the {@link ContainerBase}
	 * which then calls the {@link invoke()} in the pipeline which calls {@link invoke()}
	 * on each valve, which then get's to where we are.  To me that is just way to complex.  We
	 * are going to make this much simpler and do it right here.
	 *
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 * @return void
	 */
	function invoke(&$request, &$response)
	{
		$log =& StandardContext::getLog();

		$requestURI = $request->getRequestURI();

		// make sure this is not a container protected resource
		$relativeURI = substr($requestURI, strlen($request->getContextPath()));
		if (preg_match('/(WEB-INF|META-INF)(\/|$)/i', $relativeURI))
		{
			$this->notFound($requestURI, $response);
			return;
		}

		// select the wrapper to be used for this Request (remember, a wrapper
		// creates and holds a servlet instance)
		$wrapper =& $this->map($request);
		$request->setContext($this);

		if (is_null($wrapper))
		{
			$this->notFound($requestURI, $response);
			return;
		}

		$servlet =& $wrapper->allocate();

		if ($wrapper->isUnavailable())
		{
			$response->sendError(c('HttpServletResponse::SC_SERVICE_UNAVAILABLE'), 'Servlet ' . $wrapper->getName() . ' is currently unavailable');	
			return;
		}
		// if any other errors have been thrown, bubble up now
		if (bubble_exception()) return;
		
		register_shutdown_function(array(&$wrapper, 'deallocate'));
		// register the context to be serialized when the script finishes
		register_shutdown_function(array(&$this, 'sleep'));

		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('[' . $request->getContextPath() . '] Servicing request for servlet matching \'' . $request->getServletPath() . '\' using instance of ' . $wrapper->getServletClass());
		}

		// TODO: start filter chain here which will allow us to wrap our request and also call service()
		//  for now we will just cheat and call service() directly, but we won't get to deallocate then
		$servlet->service($request, $response);
	}

	/**
	 * Reload this context (which in our case is the whole web application)
	 * All we need to do is blow away the StandardContext::SERVLET_CONTEXT_CACHE
	 * TODO: not finished!
	 */
	function reload()
	{
		$this->paused = true;
		// TODO: make this a private method or something
		$contextCache = new File($this->getWorkDir() . c('StandardContext::SERVLET_CONTEXT_CACHE'));
		// TODO: we need to make sure this file is not being accessed
		$contextCache->delete();
	}

	/**
	 * Report a "not found" error for the specified resource.
	 *
	 * @param string $requestURI The request resource
	 * @param HttpServletResponse $response The response we are processing
	 *
	 * @return void
	 */
	function notFound($requestURI, &$response)
	{
		$response->sendError(c('HttpServletResponse::SC_NOT_FOUND'), $requestURI);
	}

	/**
	 * Get the base path of this servlet context, which is just the absolute
	 * directory name of our fuse file <i>index.php</i>.
	 *
	 * @return string
	 */
	function getBasePath()
	{
		return dirname($_SERVER['SCRIPT_FILENAME']);
	}

	/**
	 * Persist the ApplicationContext object to disk cache between
	 * requests only if it has been marked as "modified" by the
	 * container.  This leverages PHP's internal sleep() method
	 * of objects.  Blocking file locking is also used to ensure
	 * that the cache does not clobber itself.
	 */
	function sleep()
	{
		// NOTE: inside the sleep() method, the umask() has been reset
		global $_UMASK;

		// only serialize if the servlet context has signaled that it changed
		if ($this->contextModified && !$this->paused)
		{
			$this->contextModified = false;
			$cacheFile = $this->getWorkDir() . c('StandardContext::SERVLET_CONTEXT_CACHE');
			umask($_UMASK);
			// open in append mode, lock, then truncate to avoid
			// empty reads by other threads
			$objectWriter = new FileWriter($cacheFile, true);
			$lock =& $objectWriter->lock();
			// only write of lock is valid
			if (!is_null($lock))
			{
				$objectWriter->truncate();
				$this->writeObject($objectWriter);
				$lock->release();
			}

			$objectWriter->close();
		}
	}

    /**
     * Post a copy of our current list of welcome files as a servlet context
     * attribute, so that the default servlet can find them.
	 *
	 * @access private
	 * @return void
     */
	function _postWelcomeFiles()
	{
		$servletContext =& $this->getServletContext();		
		$servletContext->setAttribute(c('ServletConstants::WELCOME_FILES_KEY'), $this->welcomeFiles);
	}

	/**
	 * Set the appropriate context attribute for our work directory.
	 * This directory will be used for the ServletContext cache data
	 * and all compiled phase files.
	 *
	 * @access private
	 * @return void
	 */
	function _postWorkDirectory()
	{
		if (is_null($this->workDir))
		{
			die('Invalid servlet configuration state.  The work directory should not be empty!');
		}

		// create directory if necessary
		$dir =& new File($this->workDir);
		// NOTE: the permissions will inherit from the umask() setting
		$dir->mkdirs();
		// this is fatal and we simply cannot go on...prevent stack trace
		// by just failing since there is no reason to proceed
		if ($e = catch_exception('SecurityException'))
		{
			die('Could not create servlet work directory:<pre>' . $this->workDir . '</pre>Please create directory manually.');
			return;
		}

		if (!$dir->canWrite())
		{
			die('The servlet work directory is not writable:<pre>' . $this->workDir . '</pre>Please update the permissions.');
		}

		$servletContext =& $this->getServletContext();
		$servletContext->setAttribute(c('ServletConstants::WORK_DIR_ATTR'), $dir);
	}
}
?>
