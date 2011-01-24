<?php
/* $Id: DetachedResult.php 352 2006-05-15 04:27:35Z mojavelinux $
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
 * @author Dan Allen
 *
 * NOTE: this should really be a SQLResult and Result an interface
 */
class DetachedResult extends Object /* implements Result */
{
	/**
	 * An array of associative row results
	 */
	var $rowsMap = array();

	/**
	 * An array of indexed row results
	 */
	var $rowsByIndex = array();	

	var $isLimited = false;
	
	function DetachedResult(&$rs, $startRow = 0, $maxRows = -1)
	{
		//$rsmd =& $rs->getMetaData();	
		//$noOfColumns = $rsmd->getColumnCount();

		// throw away all rows up to startRow
		for ($i = 0; $i < $startRow; $i++)
		{
			$rs->next();
		}

		// process rows up to maxRows
		$processedRows = 0;
		while ($rs->next())
		{
			// we have reached the limit for this query
			if ($maxRows != -1 && $processedRows == $maxRows)
			{
				$this->isLimited = true;
				break;
			}

			// @fixme: this map is actually returning an associative array, which changes things
			// for our JSTL forEach tag because we have to reference items as row[columnName]
			// rather than row.columnName, which would work with a Map implementation
			$this->rowsMap[] = $rs->getRowMap();	
			$this->rowsByIndex[] = $rs->getRowByIndex();
			$processedRows++;
		}
	}

	function getRows()
	{
		return $this->rowsMap;
	}

	function getRowsByIndex()
	{
		return $this->rowsByIndex;
	}

	/**
	 * Return the number of rows processed by this result.
	 *
	 * @return int The number of rows processed or -1 if the result could not
	 *     be initialized
	 */
	function getRowCount()
	{
		if (is_null($this->rowsMap))
		{
			return -1;
		}

		return count($this->rowsMap);
	}

	/**
	 * The static method toResult converts a ResultSet
	 * into a Result, which is a convenience interface
	 * to quickly get desired information from a sql
	 * result, though more memory intensive.
	 */
	function &toResult(&$rs, $startRow = 0, $maxRows = -1)
	{
		$result =& new DetachedResult($rs, $startRow, $maxRows);
		return $result;
	}
}
?>
