<?php
/* $Id: SQLLogAppender.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.util.logging.LogLevel');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 *
 * @package horizon.util.logging
 * @access public
 * @version $Id: SQLLogAppender.php 188 2005-04-07 04:52:31Z mojavelinux $
 */
class SQLLogAppender extends Object // implements LogAppender
{
	var $connection = null;

	var $url = null;

	var $sql = null;

	function append($level, $name, $msg)
	{
		$conn =& $this->getConnection();
		$stmt =& $conn->prepareStatement($this->sql);
		$stmt->setString(1, $name);
		$stmt->setString(1, $msg);
		$stmt->setInt(3, $level);
		$stmt->setString(4, LogLevel::valueOf($level));
		$stmt->executeUpdate();
		$this->closeConnection($conn);
	}

	function &getConnection()
	{
		if (is_null($this->connection))
		{
			$this->connection =& DriverManager::getConnection($this->url);
		}

		// reopen connection as necessary
		if ($this->connection->isClosed())
		{
			$this->connection->connect();
		}

		return $this->connection;	
	}

	function closeConnection(&$conn)
	{
		// TODO: add catch stuff
		if (!is_null($conn))
		{
			$conn->close();
		}
	}
}
?>
