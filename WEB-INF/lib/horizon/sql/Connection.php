<?php
/* $Id: Connection.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package horizon.sql
 * @abstract
 */
class Connection extends Object
{
	var $host = null;

	var $port = null;

	var $database = null;

	var $user = null;

	var $password = null;

	var $url = null;

	var $link = null;

	var $props = null;

	var $isClosed = true;

	/**
	 * @access package
	 */
	function Connection($host, $port, &$info, $database, $url)
	{
		// NOTE: we use empty() here because getProperty will have returned a empty
		// string for any null values, so it means it might not have been set
		if (!empty($host))
		{
			$this->host = $host;
		}

		if (!empty($port))
		{
			$this->port = $port;
		}

		$this->database = $database;

		$user = $info->getProperty('user');
		if (!empty($user))
		{
			$this->user = $user;
		}

		$this->password = $info->getProperty('password');
		$this->url = $url;
		$this->props =& $info;
		$this->connect();
	}

	/**
	 * Releases this Connection object's database and resources immediately
	 * instead of waiting for them to be automatically released.
	 *
	 * @access public
	 * @return void
	 */
	function close()
	{
		die('Method <code>close</code> in class <code>Connection</code> is abstract.');
	}

	/**
	 * Establish a connection to the database.
	 *
	 * @access protected
	 * @return void
	 */
	function connect()
	{
		die('Method <code>connect</code> in class <code>Connection</code> is abstract.');
	}

	/**
	 * Creates a Statement object for sending SQL statements to the database.
	 *
	 * @access public
	 * @return Statement
	 */
	function createStatement()
	{
		die('Method <code>createStatement</code> in class <code>Connection</code> is abstract.');
	}

	/**
	 * Check to see if the connection to the database has been closed
	 *
	 * @access public
	 * @return boolean
	 */
	function isClosed()
	{
		return $this->isClosed;
	}

	/**
	 * Creates a PreparedStatement object for sending parameterized SQL
	 * statements to the database.
	 *
	 * @param string $sql
	 *
	 * @return PreparedStatement
	 */
	function prepareStatement($sql)
	{
		die('Method <code>prepareStatement</code> in class <code>Connection</code> is abstract.');
	}
}
?>
