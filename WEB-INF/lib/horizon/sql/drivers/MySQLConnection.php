<?php
/* $Id: MySQLConnection.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('horizon.sql.Connection');
import('horizon.sql.drivers.MySQLStatement');
import('horizon.sql.drivers.MySQLPreparedStatement');

/**
 * @package horizon.sql.drivers
 * @author Dan Allen
 */
class MySQLConnection extends Connection
{
	var $host = 'localhost';

	var $port = 3306;

	var $user = 'nobody';

	/**
	 * @see Connection#close()
	 */
	function close()
	{
		if (!is_null($this->link))
		{
			mysql_close($this->link);
			catch_exception();
			$this->link = null;
		}

		$this->isClosed = true;
	}

	/**
	 * @see Connection#connect()
	 */
	function connect()
	{
		// NOTE: we won't use pconnect for now so that we have better control
		$this->link = mysql_connect($this->host . ':' . $this->port, $this->user, $this->password);
		mysql_select_db($this->database, $this->link);
		// TODO: check for errors
		$this->isClosed = false;
	}

	/**
	 * @see Connection#createStatement()
	 */
	function &createStatement()
	{
		if ($this->isClosed)
		{
			// TODO: throw error
			return;
		}

		$stmt =& new MySQLStatement($this);
		return $stmt;
	}

	/**
	 * @see Connection#prepareStatement($sql)
	 */
	function &prepareStatement($sql)
	{
		if ($this->isClosed)
		{
			// TODO: throw error
			return;
		}

		$stmt =& new MySQLPreparedStatement($this, $sql);
		return $stmt;	
	}

	/**
	 * @access package
	 */
	function &getLink()
	{
		if (is_null($this->link) || $this->isClosed)
		{
			// TODO: throw error
			return ref(null);
		}

		return $this->link;
	}
}
?>
