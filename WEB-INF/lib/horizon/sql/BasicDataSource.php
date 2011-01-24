<?php
/* $Id: BasicDataSource.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * Basic implementation of horizon.sql.DataSource that is configured
 * via JavaBean properties.
 *
 * This implementation differs from the PDBC implementation from jakarta
 * because we are not using a Pool and I was getting pretty confused looking
 * at that source code...to me, it seems easier just to have an initialize()
 * method and then return the connection on getConnection()
 *
 * @package horizon.sql
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class BasicDataSource extends Object /* implements DataSource */
{
	var $configured = false;

	var $connection = null;

	var $driverClassName = null;

	var $url = null;

	var $username = null;

	var $password = null;

	var $connectionProperties = null;

	function BasicDataSource()
	{
		$this->connectionProperties =& new Properties();
	}

	function getDriverClassName()
	{
		return $this->driverClassName;
	}

	function setDriverClassName($driverClassName)
	{
		$this->driverClassName = $driverClassName;
	}

	function getPassword()
	{
		return $this->password;
	}

	function setPassword($password)
	{
		$this->password = $password;
	}

	function getUrl()
	{
		return $this->url;
	}

	function setUrl($url)
	{
		$this->url = $url;
	}

	function getUsername()
	{
		return $this->username;
	}

	function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * NOTE: we are cheating here a little since we aren't using a connection pool.  Instead
	 * of creating a datasource and using it to get the connection, the datasource sets the
	 * connection as a local property and we just return that...
	 */
	function &getConnection()
	{
		$this->initializeConnection();
		return $this->connection;
	}

	/**
	 * Create (if necessary) and return the internal data source we are
     * using to manage our connections.
	 *
	 * @throws SQLException if the data source cannot be created
	 */
	function &initializeConnection()
	{
		// return data source if we already configured it
		if ($this->configured)
		{
			return;
		}

		// load the sql driver class
		if (!is_null($this->driverClassName))
		{
			Clazz::forName($this->driverClassName);
		}
		
		$driver =& DriverManager::getDriver($this->url);
		if (!is_null($this->username))
		{
			$this->connectionProperties->setProperty('user', $this->username);
		}

		if (!is_null($this->password))
		{
			$this->connectionProperties->setProperty('password', $this->password);
		}

		$this->connection =& $driver->connect($this->url, $this->connectionProperties);
	}
}
?>
