<?php
/* $Id: PgSQLStatement.php 324 2006-03-11 05:42:59Z mojavelinux $
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
import('horizon.sql.drivers.MySQLResultSet');

/**
 * @package horizon.sql.drivers
 * @author Dan Allen
 */
class PgSQLStatement extends Statement
{
	function &executeQuery($sql)
	{
		if (!is_null($this->results))
		{
			$this->results->close();
		}

		return $this->execSQL($sql);
	}

	function executeUpdate($sql)
	{
		if (!is_null($this->results))
		{
			$this->results->close();
		}

		return $this->execSQL($sql, true);
	}

	/**
	 * @access protected
	 */
	function &execSQL($sql, $update = false)
	{
		$result = pg_query($this->connection, $sql);
		$this->results =& new PgSQLResultSet($result, $this->connection, $update);
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
