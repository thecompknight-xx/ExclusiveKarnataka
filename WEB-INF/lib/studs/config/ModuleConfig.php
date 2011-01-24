<?php
/* $Id: ModuleConfig.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('studs.config.ControllerConfig');

/**
 * @todo: implement the remove*Config methods, comment, optimize the freeze() method
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.config
 */
class ModuleConfig
{
	var $actionMappingClass = 'studs.action.ActionMapping';

	var $prefix = null;

	var $configured = false;

	var $configPaths = array();

	var $controllerConfig = null;

	var $actionConfigs = array();

	var $dataSources = array();

	var $exceptions = array();

	var $formBeans = array();

	var $forwards = array();

	var $messageResources = array();

	function ModuleConfig($prefix)
	{
		$this->prefix = $prefix;
	}

	function isConfigured()
	{
		return $this->configured;	
	}

	function &getControllerConfig()
	{
		if (is_null($this->controllerConfig))
		{
			$this->controllerConfig =& new ControllerConfig();
		}

		return $this->controllerConfig;
	}

	function setControllerConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->controllerConfig =& $config;	
	}

	function getPrefix()
	{
		return $this->prefix;
	}

	function setPrefix($prefix)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->prefix = $prefix;
	}

	/**
	 * The ModuleConfig instance is self aware of its
	 * composition configuration files so that they can
	 * be checked for updates.
	 */
	function getConfigPaths()
	{
		return $this->configPaths;
	}

	function storeConfigPath($path, $lastModified)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->configPaths[$path] = $lastModified;
	}

	function addActionConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}
		
		$config->setModuleConfig($this);
		$this->actionConfigs[$config->getPath()] =& $config;
	}

	function addDataSourceConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->dataSources[$config->getKey()] =& $config;
	}

	function addExceptionConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->exceptions[$config->getType()] =& $config;	
	}

	function addFormBeanConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}
		
		$config->setModuleConfig($this);
		$this->formBeans[$config->getName()] =& $config;
	}

	function addForwardConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}
		
		$this->forwards[$config->getName()] =& $config;
	}

	function addMessageResourcesConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->messageResources[$config->getKey()] =& $config;
	}

	function &findActionConfig($path)
	{
		if (!isset($this->actionConfigs[$path]))
		{
			return ref(null);
		}

		return $this->actionConfigs[$path];
	}

	function findActionConfigs()
	{
		return array_values($this->actionConfigs);
	}

	function &findDataSourceConfig($key)
	{
		if (!isset($this->dataSources[$key]))
		{
			return ref(null);
		}

		return $this->dataSources[$key];

	}

	function findDataSourceConfigs()
	{
		return array_values($this->dataSources);
	}

	function &findExceptionConfig($type)
	{
		if (!isset($this->exceptions[$type]))
		{
			return ref(null);
		}

		return $this->exceptions[$type];
	}

	function findExceptionConfigs()
	{
		return array_values($this->exceptions);
	}

	function &findFormBeanConfig($name)
	{
		if (!isset($this->formBeans[$name]))
		{
			return ref(null);
		}

		return $this->formBeans[$name];	
	}

	function findFormBeanConfigs()
	{
		return array_values($this->formBeans);
	}

	function &findForwardConfig($name)
	{
		if (!isset($this->forwards[$name]))
		{
			return ref(null);
		}

		return $this->forwards[$name];

	}

	function findForwardConfigs()
	{
		return array_values($this->forwards);
	}
	

	function &findMessageResourcesConfig($key)
	{
		if (!isset($this->messageResources[$key]))
		{
			return ref(null);
		}

		return $this->messageResources[$key];
	}

	function findMessageResourcesConfigs()
	{
		return array_values($this->messageResources);
	}

	/**
	 * Freeze the configuration of this module.  After this method
	 * returns, any attempt to modify the configuration will be blocked
	 * @return void
	 */
	function freeze()
	{
		$this->configured = true;

		// need to do this just in case it needs to be initialized
		$controllerConfig =& $this->getControllerConfig();
		$controllerConfig->freeze();

		$actionConfigs = $this->findActionConfigs();
		for ($i = 0; $i < count($actionConfigs); $i++)
		{
			$actionConfigs[$i]->freeze();
		}

		$dataSourceConfigs = $this->findDataSourceConfigs();
		for ($i = 0; $i < count($dataSourceConfigs); $i++)
		{
			$dataSourceConfigs[$i]->freeze();
		}

		$exceptionConfigs = $this->findExceptionConfigs();
		for ($i = 0; $i < count($exceptionConfigs); $i++)
		{
			$exceptionConfigs[$i]->freeze();
		}

		$formBeanConfigs = $this->findFormBeanConfigs();
		for ($i = 0; $i < count($formBeanConfigs); $i++)
		{
			$formBeanConfigs[$i]->freeze();
		}

		$forwardConfigs = $this->findForwardConfigs();
		for ($i = 0; $i < count($forwardConfigs); $i++)
		{
			$forwardConfigs[$i]->freeze();
		}

		$messageResourceConfigs = $this->findMessageResourcesConfigs();
		for ($i = 0; $i < count($messageResourceConfigs); $i++)
		{
			$messageResourceConfigs[$i]->freeze();
		}
	}
}
?>
