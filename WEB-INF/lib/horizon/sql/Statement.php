<?php
/* $Id: Statement.php 229 2005-06-27 03:10:23Z mojavelinux $
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
 * @author Dan Allen
 */
class Statement extends Object
{
	/**
	 * The connection associated with this Statement
	 *
	 * @var Connection
	 */
	var $connection = null;

	/**
	 * @var boolean
	 */
	var $isClosed = false;

	/**
	 * The results of the last executed statement
	 *
	 * @var ResultSet
	 */
	var $results = null;

	var $updateCount = null;

	function Statement(&$connection)
	{
		// @todo make sure connection is not closed or null
		$this->connection =& $connection;
	}

	function close()
	{
		if ($this->isClosed)
		{
			return;
		}

		$this->connection = null;
		$this->isClosed = true;
	}

	/**
	 * @return ResultSet
	 */
	function &executeQuery($sql)
	{
		die('Method <code>executeQuery</code> in class <strong>Statement</strong> is abstract.');
	}

	/**
	 * @return int
	 */
	function executeUpdate($sql)
	{
		die('Method <code>executeQuery</code> in class <strong>Statement</strong> is abstract.');
	}

	function &getConnection()
	{
		return $this->connection;
	}

	/**
	 * Retrieves the current result as a ResultSet object.
	 */
	function &getResultSet()
	{
		return $this->results;
	}

	function getUpdateCount()
	{
		return $this->updateCount;
	}
}
?>
