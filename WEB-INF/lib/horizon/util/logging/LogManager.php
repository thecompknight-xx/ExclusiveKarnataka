<?php
/* $Id: LogManager.php 263 2005-07-12 02:47:28Z mojavelinux $
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

import('horizon.util.Properties');
import('horizon.collections.HashMap');
import('horizon.util.logging.LogLevel');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 *
 * @package horizon.util.logging
 * @access public
 * @version $Id: LogManager.php 263 2005-07-12 02:47:28Z mojavelinux $
 */

// comma seperated list of places to find the config file
def('LogManager::CONFIG', 'logging.properties,resources/logging.properties');

class LogManager
{
	var $loggers = null;

	var $props = null;

	var $rootLevel = null;

	var $appenders = array();

	var $categories = array();

	function LogManager()
	{
		$this->props =& new Properties();
		$this->loggers =& new HashMap();
		$this->rootLevel = c('LogLevel::DEBUG');
	}

	function &getLogger($name)
	{
		$logger =& $this->loggers->get($name);
		
		if (is_null($logger))
		{
			$logger =& new Logger($name);
			$logger->setLevel($this->resolveLevel($name));
			for ($i = 0; $i < count($this->appenders); $i++)
			{
				$logger->addAppender($this->appenders[$i]);
			}

			$this->loggers->put($name, $logger);
		}

		return $logger;
	}

	function readConfiguration()
	{
		// use the default class loader to grab the logging.properties file
		$configs = explode(',', c('LogManager::CONFIG'));
		$found = false;
		foreach ($configs as $config)
		{
			$is =& Clazz::getResourceAsStream($config);
			if (!is_null($is))
			{
				$found = true;
				break;
			}
		}

		// abort if no logging configuration files were found
		if (!$found)
		{
			return;
		}

		$this->props->load($is);
		$is->close();
		$root = preg_split('/, */', $this->props->getProperty('logging.rootLogger'));
		$this->rootLevel = LogLevel::toLevel($root[0]);
		$propsCallback = array();
		for ($i = 1; $i < count($root); $i++)
		{
			$appenderClazz =& Clazz::forName($this->props->getProperty('logging.appender.' . $root[$i]));
			if (bubble_exception()) return;
			$appender =& $appenderClazz->newInstance();
			$this->appenders[] =& $appender;
			$propsCallback['logging.appender.' . $root[$i]] =& $appender;
		}

		$propNames = $this->props->propertyNames();
		foreach ($propNames as $name)
		{
			if (preg_match('/(logging\.appender\.[^\.]+)\.(.*)/', $name, $matches))
			{
				$obj =& $propsCallback[$matches[1]];
				// NOTE: probably should use beanutils here
				$method = 'set' . ucfirst($matches[2]);
				$obj->$method($this->props->getProperty($name));
			}
			else if (preg_match('/logging\.logger\.(.+)/', $name, $matches))
			{
				// @fixme: using -1 is a hack here
				$catLevel = LogLevel::toLevel($this->props->getProperty($name), -1);
				if ($catLevel != -1)
				{
					$this->categories[$matches[1]] = $catLevel;
				}
			}
		}
	}

	/**
	 * Given a logger name, assume the root level to begin with, then search in each of the
	 * categories (a category is all or part of a fully qualified class name) for
	 * a specific log level.  The closer the category is to the qualified class name,
	 * the more preference it is given.
	 *
	 * @param string $name The name of this logger, typically a fully qualified class name
	 *
	 * @return The level to use for this logger
	 */
	function resolveLevel($name)
	{
		$level = $this->rootLevel;
		$parts = explode('.', $name); 
		$category = '';
		while (count($parts))
		{
			$category .= array_shift($parts);
			// QUESTION: do we only want lower log levels, or more specific
			// category names?  which has preference?
			if (array_key_exists($category, $this->categories))
			{
				$level = $this->categories[$category];
			}

			$category .= '.';
		}

		return $level;
	}

	function getProperty($name)
	{
		return $this->props->getProperty($name);	
	}

	function &getLogManager()
	{
		static $instance;

		if (!isset($instance))
		{
			$instance = new LogManager();
			$instance->readConfiguration();
		}

		return $instance;
	}
}
?>
