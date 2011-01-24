<?php
/* $Id: ActionServlet.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('stratus.UnavailableException');
import('stratus.servlets.HttpServlet');
import('horizon.xml.digester.Digester');
import('studs.StudsConstants');
import('studs.action.RequestProcessor');
import('studs.config.ModuleConfig');
import('studs.config.StudsRuleSet');
import('studs.util.RequestUtils');
import('studs.util.ModuleUtils');
import('horizon.beanutils.ConvertUtils');
import('horizon.util.logging.Logger');

/**
 * An <b>ActionServlet</b> represents the "controller" in the
 * Model-View-Controller (MVC) design pattern for web applications that is
 * commonly known as "Model 2".
 *
 * The standard version of {@link ActionServlet} implements the following logic
 * for each incoming HTTP request.  You can override some or all of this
 * functionality by subclassing this servlet and implementing your own version
 * of the processing.
 *
 * <ul>
 *   <li>Identify, from the incoming request URI, the substring that will be
 *       used to select an action procedure.</li>
 *   <li>Use this substring to map to the Java class name of the corresponding
 *       action class (an implementation of the {@link Action} interface).</li>
 *   <li>If this is the first request for a particular action class, instantiate
 *       an instance of that class and cache it for future use.</li>
 *   <li>Optionally populate the properties of an {@link ActionForm} bean
 *       associated with this mapping.</li>
 *   <li>Call the {@link execute()} method of this action class, passing
 *       on a reference to the mapping that was used (thereby providing access
 *       to the underlying ActionServlet and ServletContext, as well as any
 *       specialized properties of the mapping itself), and the request and
 *       response that were passed to the controller by the servlet container.</li>
 * </ul>
 *
 * NOTE: Modules work in the following way.  Modules are stored with the prefix
 * appended to the end of the global module key.  Before grabbing the module
 * config from the request, a selectModule() call should be made which puts the
 * ModuleConfig into the request as the "main" module.
 *
 * @package studs.action
 * @access public
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @author Craig R. McClanahan
 * @author Ted Husted
 * @author Martin Cooper
 * @version $Revision: 370 $ $Date: 2006-10-17 01:19:38 -0400 (Tue, 17 Oct 2006) $
 */
class ActionServlet extends HttpServlet
{
	/**
	 * Comma-separated list of context-relative path(s) to our configuration
	 * resource(s) for the default module.
	 * @var string
	 */
	var $config = '/WEB-INF/struts-config.xml';

	/**
	 * The Digester used to produce ModuleConfig objects from a
	 * Studs configuration file.
	 * @var Digester
	 */
	var $configDigester = null;

	/**
	 * The set of public identifiers and cooresponding resource names for the versions
	 * of configuration file DTDs that are to be used for validation.
	 * @var array
	 */
	var $registrations = array(
		'-//Apache Software Foundation//DTD Struts Configuration 1.0//EN' => 'studs/resources/struts-config_1_2.dtd',
	);

	/**
	 * Out of convenience, store the servletContext to a local property so we don't have to create
	 * so many temporary variables due to PHP's lack of variable dereferencing.
	 * @var ServletContext
	 */
	var $servletContext = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 *
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('studs.action.ActionServlet');
		return $logger;
	}

	/**
	 * Determine if this servlet should be reloaded.  This is done by checking
	 * the timestamp of the config files for the current module against the
	 * values stored at servlet initialization.  By looking only at the
	 * current module, reloading can be delayed until it is required and
	 * the check can be optimized.
	 *
	 * @return boolean Whether or not one of the files has changed for the
	 * current module
	 */
	function checkReload($request)
	{
		if (!$this->isReloading())
		{
			return false;
		}

		$prefix = ModuleUtils::getModuleName($request, $this->servletContext);
		$config =& $this->servletContext->getAttribute(c('StudsConstants::MODULE_KEY') . $prefix);

		$paths = $config->getConfigPaths();
		foreach ($paths as $path => $lastModified)
		{
			$configFile =& $this->servletContext->getResource($path);
			if ($configFile->lastModified() > $lastModified)
			{
				$log =& ActionServlet::getLog();
				$log->debug('Detected configuration change in "' . $path . '"');
				return true;
			}
		}

		return false;
	}

	/**
	 * Reload this servlet instance.
	 */
	function reload()
	{
		$this->destroy();
		$this->init();
	}

	/**
	 * Gracefully shut down this controller servlet, releasing any resources
	 * that were allocated at initialization.
	 *
	 * @access public
	 *
	 * @return void
	 */
	function destroy()
	{
		$this->destroyModules();
		$this->servletContext->removeAttribute(c('StudsConstants::SERVLET_MAPPING_KEY'));
		$this->servletContext->removeAttribute(c('StudsConstants::ACTION_SERVLET_KEY'));
	}

	/**
	 * Remove the studs modules from the application context.
	 */
	function destroyModules()
	{
		$names = $this->servletContext->getAttributeNames();
		foreach ($names as $name)
		{
			$object =& $this->servletContext->getAttribute($name);
			if (is_a($object, 'ModuleConfig'))
			{
				$this->servletContext->removeAttribute($name);
				$this->servletContext->removeAttribute(c('StudsConstants::REQUEST_PROCESSOR_KEY') . $object->getPrefix());
				// TODO: remove DataSources and MessageResources
			}
		}

		$this->servletContext->removeAttribute(c('StudsConstants::MODULE_PREFIXES_KEY'));
	}

	/**
	 * Initialize this servlet. Most processing is delegated to support methods
	 * for easy extension.  The initOther() method from Struts' ActionServlet
	 * is skipped since we have enabled a channel to the initialization
	 * parameters from the web.xml file in this servlet implementation, freeing
	 * us from having to reparse the file.
	 *
	 * @access public
	 *
	 * @return void
	 * @throws ServletException
	 */
	function init()
	{
		$log =& ActionServlet::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Performing servlet initialization...');
		}

		$this->initServlet();

		$this->servletContext->setAttribute(c('StudsConstants::ACTION_SERVLET_KEY'), $this);

		// initialize the default module
		$moduleConfig =& $this->initModuleConfig('', $this->config);
		if (bubble_exception()) return;
		$this->initModuleMessageResources($moduleConfig);
		$this->initModuleDataSources($moduleConfig);
		$moduleConfig->freeze();

		// initialize additionally specified modules
		$names = $this->getInitParameterNames();
		$prefixes = array();
		foreach ($names as $name)
		{
			// Alternate configurations must be named with the prefix 'config/'
			// skip init parameter if this is not the case
			if (strpos($name, 'config/') !== 0)
			{
				continue;
			}

			$prefix = substr($name, 6);
			$moduleConfig =& $this->initModuleConfig($prefix, $this->getInitParameter($name));
			if (bubble_exception()) return;
			$this->initModuleMessageResources($moduleConfig);
			$this->initModuleDataSources($moduleConfig);
			$moduleConfig->freeze();
			$prefixes[] = $prefix;
		}

		$this->initModulePrefixes($prefixes);
		$this->destroyConfigDigester();
	}

	/**
	 * Saves an array of module prefixes in the servlet context which does not
	 * include the default module
	 *
	 * @param array $prefixes The non-default module prefixes
	 * @return void
	 */
	function initModulePrefixes($prefixes)
	{
		$this->servletContext->setAttribute(c('StudsConstants::MODULE_PREFIXES_KEY'), $prefixes);
	}

	/**
	 * Process an HTTP "GET" request, which just pushes to the generic {@link process()} method.
	 *
	 * @access public
	 *
	 * @param HttpServletRequest $request The servlet request we are processing
	 * @param HttpServletResponse $response The servlet response we are processing
	 *
	 * @return void
	 * @throws ServletException
	 */
	function doGet(&$request, &$response)
	{
		$this->process($request, $response);
	}

	/**
	 * Process an HTTP "POST" request, which just pushes to the generic {@link process()} method.
	 *
	 * @access public
	 *
	 * @param HttpServletRequest $request The servlet request we are processing
	 * @param HttpServletResponse $response The servlet response we are processing
	 *
	 * @return void
	 * @throws ServletException
	 */
	function doPost(&$request, &$response)
	{
		$this->process($request, $response);
	}

	/**
	 * Gracefully release any configDigester instance that we have created.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	function destroyConfigDigester()
	{
		$this->configDigester = null;
	}

	/**
	 * Return the module configuration object for the currently selected
	 * module or get the default module if that fails.
	 *
	 * @access protected
	 *
	 * @param request The HttpServletRequest we are processing
	 *
	 * @return ModuleConfig
	 */
	function &getModuleConfig(&$request)
	{
		$config =& $request->getAttribute(c('StudsConstants::MODULE_KEY'));

		// grab the default module if this lookup fails
		if (is_null($config))
		{
			$config =& $this->servletContext->getAttribute(c('StudsConstants::MODULE_KEY'));
		}

		return $config;
	}

	/**
	 * Look up and return the {@link RequestProcessor} responsible for the
	 * specified module, creating a new one if necessary.
	 *
	 * @access protected
	 *
	 * @param  ModuleConfig $config
	 *
	 * @return RequestProcessor
	 * @throws ServletException
	 */
	function &getRequestProcessor(&$config)
	{
		$key = c('StudsConstants::REQUEST_PROCESSOR_KEY') . $config->getPrefix();
		$processor =& $this->servletContext->getAttribute($key);

		if (is_null($processor))
		{
			// try {
			$controllerConfig =& $config->getControllerConfig();
			$processor =& RequestUtils::applicationInstance($controllerConfig->getProcessorClass());
			// } catch (RootException $e) {
			if ($e = catch_exception())
			{
				throw_exception(new UnavailableException('Cannot initialize RequestProcessor class ' . $controllerConfig->getProcessorClass()));
				return;
			}
			// }

			$processor->init($this, $config);
			$this->servletContext->setAttribute($key, $processor);
		}

		return $processor;
	}

	/**
	 * Initialize the application configuration information for the specified module.
	 *
	 * @access protected
	 *
	 * @param string $prefix Module prefix for this module
	 * @param string $paths context-relative resource path(s) for this module's configuration resource
	 *
	 * @return ModuleConfig
	 * @throws ServletException
	 */
	function &initModuleConfig($prefix, $paths)
	{
		$log =& ActionServlet::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Initializing module path "' . $prefix . '"; configuration from "' . $paths . '"');
		}

		// create a ModuleConfig object and parse the configure file into it
		$config =& new ModuleConfig($prefix);

		// configure the digester instance we will use
		$digester =& $this->initConfigDigester();

		// process each specified resource path
		while (strlen($paths) > 0)
		{
			$digester->push($config);
			$path = null;
			$comma = strpos($paths, ',');
			if ($comma !== false)
			{
				$path = trim(substr($paths, 0, $comma));
				$paths = substr($paths, $comma + 1);
			}
			else
			{
				$path = trim($paths);
				$paths = '';
			}

			if (strlen($path) < 1)
			{
				break;
			}

			$this->parseModuleConfigFile($digester, $path);
			if (bubble_exception()) return;
		}

		$this->servletContext->setAttribute(c('StudsConstants::MODULE_KEY') . $config->getPrefix(), $config);
		return $config;
	}

	/**
	 * Parse one module config file
	 *
	 * @param Digester $digester
	 * @param string $path
	 * @return boolean
	 * @throws UnavailableException
	 */
	function parseModuleConfigFile(&$digester, $path)
	{
		$config =& $digester->peek();

		// try {

		// <temporary> (until the SAXParser can validate against DTDs in PHP)
		$configFile =& $this->servletContext->getResource($path);
		$digester->checkValid($configFile);
		// </temporary>

		$input =& $this->servletContext->getResourceAsStream($path);
		$digester->parse($input);
		// } catch (RootException $e) {
		if ($e = catch_exception())
		{
			$log =& ActionServlet::getLog();
			$msg = 'Parsing error processing resource path ' . $path;
			$log->error($msg, $e);
			throw_exception(new UnavailableException($msg));
			return;
		}
		// }

		// save this path and last modified time to allow for reload checks
		$config->storeConfigPath($path, $configFile->lastModified());
	}

	/**
	 * Initialize the data sources for the specified module.
	 *
	 * @access protected
	 *
	 * @param ModuleConfig $moduleConfig information for this module
	 *
	 * @return void
	 * @throws ServletException
	 */
	function initModuleDataSources(&$moduleConfig)
	{
		$log =& ActionServlet::getLog();

		$dscs = $moduleConfig->findDataSourceConfigs();
		for ($i = 0; $i < count($dscs); $i++)
		{
			if ($log->isLoggable('DEBUG'))
			{
				$log->debug('Initializing module path "' . $moduleConfig->getPrefix() . '"; data source "' . $dscs[$i]->getKey() . '"');
			}

			// try {
			$ds =& RequestUtils::applicationInstance($dscs[$i]->getType());
			BeanUtils::populate($ds, $dscs[$i]->getProperties());
			// } catch (RootException $e) {
			if ($e = catch_exception())
			{
				$msg = 'Error initializing data source: ' . $dscs[$i]->getKey();
				$log->error($msg, $e);
				throw_exception(new UnavailableException($msg));
				return;
			}
			// }

			$this->servletContext->setAttribute($dscs[$i]->getKey() . $moduleConfig->getPrefix(), $ds);
		}
	}

	/**
	 * Initialize the application MessageResources for the specified
	 * module.
	 *
	 * @access protected
	 *
	 * @param ModuleConfig $moduleConfig information for this module
	 *
	 * @return void
	 * @throws ServletException
	 */
	function initModuleMessageResources(&$moduleConfig)
	{
		$log =& ActionServlet::getLog();
		
		$mrcs = $moduleConfig->findMessageResourcesConfigs();
		for ($i = 0; $i < count($mrcs); $i++)
		{
			if ($log->isLoggable('DEBUG'))
			{
				$log->debug('Initializing module path "' . $moduleConfig->getPrefix() . '"; message resources from "' . $mrcs[$i]->getParameter() . '"');
			}

			// try {
			/*
			 * I hacked this a bit here to enable an empty constructor...in reality
			 * this should be a factory so that calling createResources() can create
			 * the new instance with the parameters passed to the constructor
			 */
			$clazz =& Clazz::forName($mrcs[$i]->getFactory());
			$resources =& $clazz->newInstance();
			$resources->setServletContext($this->servletContext);
			$resources->setConfig($mrcs[$i]->getParameter());
			$resources->setReturnNull($mrcs[$i]->getNull());
			// } catch (RootException $e) {
			if ($e = catch_exception())
			{
				$msg = 'Error initializing message resources ' . $mrcs[$i]->getParameter();
				$log->error($msg, $e);
				throw_exception(new UnavailableException($msg));
				return;
			}
			// }

			$this->servletContext->setAttribute($mrcs[$i]->getKey() . $moduleConfig->getPrefix(), $resources);
		}
	}

	/**
	 * <p>Create (if needed) and return a new Digester instance that has been
	 * initialized to process Studs module configuraiton files and
	 * configure a corresponding ModuleConfig object (which must be
	 * pushed on to the evaluation stack before parsing begins).</p>
	 *
	 * @access protected
	 *
	 * @return Digester
	 * @throws ServletException
	 */
	function &initConfigDigester()
	{
		if (!is_null($this->configDigester))
		{
			return $this->configDigester;
		}

		// setup a digester instance, configured with settings
		// from the servlet initialization parameters
		$this->configDigester =& new Digester();
		$this->configDigester->setNamespaceAware(true);
		$this->configDigester->setValidating($this->isValidating());
		$this->configDigester->addRuleSet(new StudsRuleSet());

		// register known DTDs for validation
		foreach ($this->registrations as $publicID => $entityURL)
		{
			$url =& Clazz::getResource($entityURL);
			if (!is_null($url))
			{
				$this->configDigester->register($publicID, $url->getPath());
			}
		}
		
		// return the completely configured digest instance
		return $this->configDigester;
	}

	/**
	 * Check the status of the <kbd>reloading</kbd> initialization parameter,
	 * which specifies whether or not the ActionServlet should detect changes
	 * in the configuration files for automatic reloading.
	 * (default: true)
	 *
	 * @return boolean Whether the ActionServlet should auto-reload.
	 */
	function isReloading()
	{
		$reloading = true;
		$value = $this->getInitParameter('reloading');
		if (!is_null($value))
		{
			$reloading = ConvertUtils::convert($reloading, 'boolean');
		}

		return $reloading;
	}

	/**
	 * Check the status of the <kbd>validating</kbd> initialization parameter.
	 * (default: true)
	 *
	 * @return boolean Whether the Digester should validate documents.
	 */
	function isValidating()
	{
		$validating = true;
		$value = $this->getInitParameter('validating');
		if (!is_null($value))
		{
			$validating = ConvertUtils::convert($value, 'boolean');
		}

		return $validating;
	}

	/**
	 * Initialize our servlet with any parameters that were set.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	function initServlet()
	{
		$this->servletContext =& $this->getServletContext();

		// url-pattern used to match request to this servlet...this implementation differs slightly
		// from j2ee since we can get the current servlet mapping from our container
		// TODO: (what if there is more than one?)
		if (!is_null($this->getServletMapping()))
		{
			$this->servletContext->setAttribute(c('StudsConstants::SERVLET_MAPPING_KEY'), ref($this->getServletMapping()));
		}

		// the main struts-config.xml file(s) for the default module is
		// registered under "config" parameter
		$value = $this->getInitParameter('config');
		if (!is_null($value))
		{
			$this->config = $value;
		}
	}

	/**
	 * Perform the standard request processing for this request, and create
	 * the corresponding response.
	 *
	 * @access protected
	 *
     * @param HttpServletRequest $request The servlet request we are processing
     * @param HttpServletResponse $response The servlet response we are processing
	 * 
	 * @return void
	 * @throws ServletException
	 */
	function process(&$request, &$response)
	{
		$log =& ActionServlet::getLog();

		if ($this->checkReload($request))
		{
			$log->info('Reloading ActionServlet instance...');
			$this->reload();
		}

		ModuleUtils::selectModule($request, $this->servletContext);
		$requestProcessor =& $this->getRequestProcessor($this->getModuleConfig($request));
		if (bubble_exception()) return;
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Processing action for servlet path ' . $request->getServletPath());
		}

		$requestProcessor->process($request, $response);
	}
}
?>
