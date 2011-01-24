<?php
/* $Id: ContextConfig.php 318 2005-07-31 13:24:48Z mojavelinux $
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

import('horizon.io.FileReader');
import('horizon.xml.digester.Digester');
import('stratus.core.StandardContext');
import('stratus.config.WebRuleSet');
import('stratus.ServletException');

/**
 * The <b>ContextConfig</b> is the startup class for a <b>ServletContext</b>.
 * It configures the environment for that context by reading the J2EE
 * compatible web.xml file.  If the environment has been cached, it reads the
 * object from the cached file instead.
 *
 * @package stratus.config
 * @author Dan Allen
 * @version $Revision: 318 $
 */
class ContextConfig extends Object
{
	/**
	 * The file root of context, for use in reading the web.xml file.
	 * @var string
	 */
	var $baseDir = null;

	/**
	 * The servlet context, which contains information about the servlets and
	 * other initialization parameters.
	 * @var StandardContext
	 */
	var $context = null;

	/**
	 * Status flag.
	 * @var boolean
	 */
	var $ok = false;

	var $registrations = array(
		'-//Sun Microsystems, Inc.//DTD Web Application 2.3//EN' => 'stratus/resources/web-app_2_3.dtd'
	);

	/**
	 * Either configure a new instance of {@link StandardContext}, or read in
	 * the serialized version from the cache file.
	 */
	function ContextConfig($baseDir, $workDir = null)
	{
		$this->baseDir = $baseDir;

		$workDir = $this->_resolveWorkDir($workDir);
		$contextCache = $workDir . c('StandardContext::SERVLET_CONTEXT_CACHE');
		// make sure file exists and it is not empty
		if (file_exists($contextCache) && filesize($contextCache))
		{
			$dataReader =& new FileReader($contextCache);
			$this->context =& Object::readObject($dataReader);
			$dataReader->close();
			// NOTE: IOException will be caught by HttpProcessor
			if (!is_a($this->context, 'StandardContext'))
			{
				throw_exception(new ServletException('Servlet context failed to load.  The servlet cache seems to have been corrupted.'));
				// QUESTION: should we just unlink the file?
			}
		}
		else
		{
			$this->context =& new StandardContext(); 
			$this->context->setWorkDir($workDir);
			$this->webDigester =& $this->createWebDigester();
			$this->start();
			// configure the standard context, which is the environment
			// described by the web.xml file

			// NOTE: the call to start is being differed until after the
			// processor starts running so that appropriate error reports can
			// be outputted
			$this->context->setStartup(true);
		}
	}

	function start()
	{
		$this->context->setConfigured(false);
		$this->ok = true;
		$this->applicationConfig();
		if ($this->ok)
		{
			$this->context->setConfigured(true);
		}
		else
		{
			$this->context->setConfigured(false);
		}
	}

	/**
	 * Initialized the configuration for this context by parsing the web.xml file.
	 *
	 * @return void
	 */
	function applicationConfig()
	{
		$path = $this->baseDir . c('StandardContext::WEB_APP_CONFIG');
		// NOTE: temporary addition until the SAXParser can validate
		// against DTDs in PHP
		$configFile =& Clazz::getResource($path);
		if (!$this->webDigester->checkValid($configFile))
		{
			return;
		}
		// end temporary addition

		$is =& Clazz::getResourceAsStream($path);
		if (is_null($is)) {
			throw_exception(new ServletException('The application configuration file could not be read: ' . $path));
			return;
		}

		$this->webDigester->push($this->context);
		$this->webDigester->parse($is);
	}

	/**
	 * Create and return a new Digester instance that has been initialized to
	 * process the web-app configuration file for a context.
	 *
	 * @return Digester
	 */
	function &createWebDigester()
	{
		$webDigester =& new Digester();
		$webDigester->setValidating(true);
		$webDigester->addRuleSet(new WebRuleSet());

		// register known DTDs for validation
		foreach ($this->registrations as $publicID => $entityURL)
		{
			$url =& Clazz::getResource($entityURL);
			if (!is_null($url))
			{
				$webDigester->register($publicID, $url->getPath());
			}
		}

		return $webDigester;
	}

	/**
	 * Return the underlying StandardContext instance that has been
	 * configured from the web.xml file parse.
	 *
	 * @return StandardContext
	 */
	function &getContext()
	{
		return $this->context;
	}

	/**
	 * Allow the work directory to be configured from the ContextConfig
	 * constructor call in the fuse file.  If one is not specified, use the
	 * fixed constant value.  If the workdir begins with /WEB-INF/, assume it
	 * lies within the web application, otherwise treat it as absolute.
	 */
	function _resolveWorkDir($workDir)
	{
		if (!is_null($workDir))
		{
			if (strpos($workDir, '/WEB-INF/') === 0)
			{
				$workDir = $this->baseDir . $workDir;
			}
		}
		else
		{
			$workDir = $this->baseDir . c('StandardContext::SERVLET_WORK_DIR');
		}

		return $workDir;
	}
}
?>
