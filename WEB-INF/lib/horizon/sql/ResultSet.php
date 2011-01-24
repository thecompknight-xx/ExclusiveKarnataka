<?php
/* $Id: ResultSet.php 188 2005-04-07 04:52:31Z mojavelinux $
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
class ResultSet extends Object
{
	/**
	 * The internal PHP connection to this result set.
	 *
	 * @var resource
	 */
	var $resultId;

	/**
	 * The connection with which this result set is associated.
	 *
	 * @var resource
	 */
	var $connection;

	/**
	 * The row number of the internal cursor on this result set.
	 *
	 * @var int
	 */
	var $row;

	/**
	 * The data for the current row as an associative array.
	 *
	 * @var array
	 */
	var $rowMap;

	/**
	 * The data for the current row as an 0-based indexed array.
	 *
	 * @var array
	 */
	var $rowByIndex;

	var $numFields;

	var $numRows;

	var $isClosed = false;

	var $wasNull = false;

	function ResultSet(&$resultId, &$connection)
	{
		$this->connection =& $connection;
		$this->resultId =& $resultId;
	}

	/**
	 * Close the result set
	 *
	 * @return void
	 */
	function close()
	{
		die('Method <code>close</code> in class <code>ResultSet</code> is abstract.');
	}

	/**
	 * Advance the internal cursor to the next row of the result set.
	 *
	 * @return void
	 */
	function next()
	{
		die('Method <code>next</code> in class <code>ResultSet</code> is abstract.');
	}

	function getObject($column)
	{
		// columns are 1 based, but PHP starts at 0
		if (is_int($column))
		{
			$column--;
			$object =& $this->rowByIndex[$column];
		}
		else
		{
			$object =& $this->rowMap[$column];
		}

		$this->wasNull = is_null($object);
		return $object;
	}

	/**
	 * Get the value of the column name or column index (1 based) for the
	 * current row.  We let PHP handle the types.
	 *
	 * @param string $column
	 *
	 * @return mixed
	 */
	function getString($column)
	{
		return (string) $this->getObject($column);
	}

	function getInt($column)
	{
		return (int) $this->getObject($column);
	}

	function getFloat($column)
	{
		return (float) $this->getObject($column);
	}

	/**
	 * Retrieves the current row number.
	 *
	 * @return int
	 */
	function getRow()
	{
		return $this->row;
	}

	function getRowMap()
	{
		return $this->rowMap;
	}

	function getRowByIndex()
	{
		return $this->rowByIndex;
	}

	/**
	 * Retrieves whether the cursor is on the last row of this ResultSet object.
	 *
	 * @return boolean
	 */
	function isLast()
	{
		return ($this->row == $this->numRows);
	}

	/**
	 * Reports whether the last column read had a value of SQL NULL. Note that
	 * you must first call one of the getter methods on a column to try to read
	 * its value and then call the method wasNull to see if the value read was
	 * SQL NULL.
	 *
	 * @return boolean
	 */
	function wasNull()
	{
		return $this->wasNull;
	}
}
?>
