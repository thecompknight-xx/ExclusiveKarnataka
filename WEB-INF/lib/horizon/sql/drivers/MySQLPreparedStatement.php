<?php
/* $Id: MySQLPreparedStatement.php 324 2006-03-11 05:42:59Z mojavelinux $
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

import('horizon.sql.PreparedStatement');
import('horizon.sql.drivers.MySQLResultSet');

/**
 * Break the sql query up into an array of static sql which
 * surround the dynamic parts and then create another array
 * which holds these fill values.  If they are not congruent,
 * throw a SQL error when executing the statement.
 *
 * @package horizon.sql.drivers
 * @author Dan Allen
 *
 * TODO: perhaps abstract the execSQL() method away so that it can be
 * shared by all these execute methods
 */
class MySQLPreparedStatement extends PreparedStatement
{
	function &executeQuery()
	{
		if (!is_null($this->results))
		{
			$this->results->close();
		}

		$sql = $this->generateQuery();
		return $this->execSQL($sql);
	}

	function &executeUpdate()
	{
		if (!is_null($this->results))
		{
			$this->results->close();
		}

		$sql = $this->generateQuery();
		return $this->execSQL($sql, true);
	}

	/**
	 * Set a parameter with a string value.
	 *
	 * @param int $paramIndex the 1-based parameter value
	 * @param string $value the value of the parameter, unescaped
	 *
	 * @return void
	 */
	function setString($paramIndex, $value)
	{
		// TODO: make sure not out of range, etc
		$this->parameterValues[$paramIndex - 1] = '\'' . mysql_escape_string($value) . '\'';	
	}

	function setInt($paramIndex, $value)
	{
		// TODO: make sure not out of range, etc
		$this->parameterValues[$paramIndex - 1] = intval($value);	
	}

	function setFloat($paramIndex, $value)
	{
		// TODO: make sure not out of range, etc
		$this->parameterValues[$paramIndex - 1] = floatval($value);
	}

	/**
	 * @access protected
	 * TODO: I need to figure out how to inherit methods from Statement and
	 * MySQLStatement
	 */
	function &execSQL($sql, $update = false)
	{
		$result = mysql_query($sql);
		$this->results =& new MySQLResultSet($result, $this->connection, $update);
		if ($update)
		{
			$this->updateCount = $this->results->getUpdateCount();
			return $this->updateCount;
		}
		else
		{
			return $this->results;
		}
	}
}
?>
