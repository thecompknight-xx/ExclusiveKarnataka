<?php
/* $Id: Driver.php 352 2006-05-15 04:27:35Z mojavelinux $
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
import('horizon.sql.DriverManager');

/**
 * @package horizon.sql
 * @abstract
 * @author Dan Allen
 */
class Driver extends Object
{
	/**
	 * The scheme used to associate a connection url with a specific driver, i.e. mysql://
	 */
	var $prefix = null;

	function Driver()
	{
		DriverManager::registerDriver($this);
	}

	/**
	 * Attempts to make a database connection to the given URL. The driver should
	 * return "null" if it realizes it is the wrong kind of driver to connect to
	 * the given URL. This will be common, as when the {@link DriverManager} is asked
	 * to connect to a given URL it passes the URL to each loaded driver in turn.
	 * 
	 * The driver should throw an SQLException if it is the right driver to connect
	 * to the given URL but has trouble connecting to the database.
	 * 
	 * The java.util.Properties argument can be used to pass arbitrary string
	 * tag/value pairs as connection arguments.
	 *
	 * @param string $url
	 * @param Properties $info
	 *
	 * @return Connection
	 */
	function &connect($url, &$info)
	{
		die('Method <code>connect</code> in class <strong>Driver</strong> is abstract.');
	}

	/**
	 * Split the url into properties for the {@link Driver}
	 *
	 * @param string $url
	 * @param Properties $info
	 *
	 * @return Properties
	 */
	function &parseURL($url, &$info)
	{
		$urlProps =& new Properties();
		$urlProps->setDefaults($info);

		$url = trim($url);
		// handle a jdbc-like url with optional port and paramString
		if (!preg_match(';^' . $this->prefix . '://([^:/]+)(:([0-9]+))?/([^\?]+)\??(.*)$;', $url, $matches))
		{
			return ref(null);
		}

		list(, $host, , $port, $dbname, $paramString) = $matches;
		if ($paramString)
		{
			parse_str($paramString, $params);
			foreach ($params as $name => $value)
			{
				$urlProps->setProperty($name, $value);
			}
		}

		if (!empty($host))
		{
			$urlProps->setProperty('HOST', $host);	
		}

		if (!empty($port))
		{
			$urlProps->setProperty('PORT', $port);	
		}

		if (!empty($dbname))
		{
			$urlProps->setProperty('DBNAME', $dbname);	
		}

		return $urlProps;
	}

	/**
	 * Retrieves whether the driver thinks that it can open a connection to the
	 * given URL.
	 *
	 * @param string $url
	 *
	 * @return boolean
	 */
	function acceptsURL($url)
	{
		return ($this->parseURL($url, $tmp = null) != null);
	}
}
?>
