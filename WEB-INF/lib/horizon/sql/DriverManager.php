<?php
/* $Id: DriverManager.php 352 2006-05-15 04:27:35Z mojavelinux $
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

/**
 * Provides a factor for obtaining database connections based on a DSN string.
 *
 * Example:
 * <code>
 *	Class.forName("horizon.sql.drivers.MySQLDriver");
 * 	$conn =& DriverManager::getConnection("mysql://localhost:3306/testDB", "user", "password");
 * </code>
 *
 * @package horizon.sql
 * @author Dan Allen
 */
class DriverManager extends Object
{
	var $drivers = array();

	/**
	 * Return a Singleton instance of this class.  This is used in place
	 * of having a <i>static</i> {@link drivers} property which PHP
	 * does not support.
	 *
	 * @return DriverManager
	 */
	function &getInstance()
	{
		static $instance;

		if (is_null($instance))
		{
			$instance = new DriverManager();
		}

		return $instance;
	}

	function &getConnection($url, $username = null, $password = null)
	{
		$instance =& DriverManager::getInstance();	
		$props =& new Properties();
		if (!is_null($username))
		{
			$props->setProperty('user', $username);
		}
		
		if (!is_null($password))
		{
			$props->setProperty('password', $password);
		}

		$connection = null;
		$numDrivers = count($instance->drivers);
		for ($i = 0; $i < $numDrivers; $i++)
		{
			$connection =& $instance->drivers[$i]->connect($url, $props);
			if (!is_null($connection))
			{
				break;
			}
		}

		return $connection;
	}

	/**
	 * Attempts to locate a driver that understands the given URL.  Iterate
	 * through the drivers available until a match is found based on the
	 * {@link Driver} method {@link acceptsURL()}.
	 *
	 * @param string $url
	 *
	 * @return Driver
	 */
	function &getDriver($url)
	{
		$instance =& DriverManager::getInstance();	
		$numDrivers = count($instance->drivers);
		for ($i = 0; $i < $numDrivers; $i++)
		{
			if ($instance->drivers[$i]->acceptsURL($url))
			{
				return $instance->drivers[$i];
			}
		}

		return ref(null);
	}

	/**
	 * Registers the given driver with the {@link DriverManager}.
	 */
	function registerDriver(&$driver)
	{
		$instance =& DriverManager::getInstance();	
		$instance->drivers[] =& $driver;
	}
}
?>
