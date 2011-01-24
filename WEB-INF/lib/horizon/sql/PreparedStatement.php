<?php
/* $Id: PreparedStatement.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.sql.Statement');

/**
 * @package horizon.sql
 * @abstract
 * @author Dan Allen
 */
class PreparedStatement extends Statement
{
	var $originalSql = null;

	var $staticSqlStrings = array();

	var $parameterValues = array();

	function PreparedStatement(&$connection, $sql)
	{
		parent::Statement($connection);
		$this->originalSql = $sql;
		$this->staticSqlStrings = explode('?', $sql);
	}

	/**
	 * @access protected
	 * @return string
	 */
	function generateQuery()
	{
		$sendQuery = $this->staticSqlStrings[0];
		$numStaticParts = count($this->staticSqlStrings);
		for ($i = 1; $i < $numStaticParts; $i++)
		{
			// TODO: make sure we have a parameter for this index
			$sendQuery .= $this->parameterValues[$i - 1];
			$sendQuery .= $this->staticSqlStrings[$i];	
		}

		return $sendQuery;
	}

	function setString($paramIndex, $value)
	{
		die('Method <code>setString</code> in class <strong>PreparedStatement</strong> is abstract.');
	}

	function setInt($paramIndex, $value)
	{
		die('Method <code>setInt</code> in class <strong>PreparedStatement</strong> is abstract.');
	}

	function setFloat($paramIndex, $value)
	{
		die('Method <code>setFloat</code> in class <strong>PreparedStatement</strong> is abstract.');
	}
}
?>
