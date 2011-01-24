<?php
/* $Id: PgSQLConnection.php 352 2006-05-15 04:27:35Z mojavelinux $
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
import('horizon.sql.drivers.PgSQLStatement');
import('horizon.sql.drivers.PgSQLPreparedStatement');

/**
 * @package horizon.sql.drivers
 * @author Dan Allen
 */
class PgSQLConnection extends Connection
{
	var $host = 'localhost';

	var $port = 5432;

	var $user = 'nobody';

	function PgSQLConnection($host, $port, &$info, $database, $url) 
	{
		parent::Connection($host, $port, &$info, $database, $url);
	}


	/**
	 * @see Connection#close()
	 */
	function close()
	{
		if (!is_null($this->link))
		{
			pg_close($this->link);
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
		$this->link = pg_connect('host=' . $this->host . ' port=' . $this->port . ' dbname=' . $this->database . ' user=' . $this->user . ' password=' . $this->password);
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

		$stmt =& new PgSQLStatement($this);
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

		$stmt =& new PgSQLPreparedStatement($this, $sql);
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
