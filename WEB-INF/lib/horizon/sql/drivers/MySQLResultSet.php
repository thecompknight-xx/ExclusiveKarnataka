<?php
/* $Id: MySQLResultSet.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.sql.ResultSet');

/**
 * @package horizon.sql.drivers
 * @author Dan Allen
 */
class MySQLResultSet extends ResultSet
{
	var $updateCount = null;

	var $updateId = -1;

	function MySQLResultSet(&$resultId, &$connection, $update = false)
	{
		parent::ResultSet($resultId, $connection);
		if ($update)
		{
			$this->updateCount = mysql_affected_rows($connection->getLink());
			$this->updateId = mysql_insert_id($connection->getLink());
		}
		else
		{
			$this->numRows = mysql_num_rows($resultId);
			$this->numFields = mysql_num_fields($resultId);
		}
	}

	function close()
	{
		if (!$this->isClosed && !is_null($this->resultId))
		{
			mysql_free_result($this->resultId);
		}

		$this->resultId = null;
		$this->isClosed = true;
	}

	function next()
	{
		$rowMap = mysql_fetch_array($this->resultId, MYSQL_ASSOC);
		if ($rowMap !== false)
		{
			$this->rowMap = $rowMap;
			$this->rowByIndex = array_values($rowMap);
			$this->row++;
			return true;
		}

		return false;
	}

	/**
	 * @access package
	 */
	function getUpdateCount()
	{
		return $this->updateCount;
	}

	/**
	 * @access package
	 */
	function getUpdateID()
	{
		return $this->updateId;
	}
}
?>
