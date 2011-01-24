<?php
/* $Id: Logger.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.util.logging.LogManager');
import('horizon.util.logging.LogLevel');

/**
 * NOTE: so that the log instance is not cached, and php does not have static class
 * variables, it is best to create a private method getLog() in each class
 * which will use the logging and have it return an instance of the logger.
 * you could even make a generic getLog() message that uses the debug_backtrace()
 * to figure out for which class it should be created
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 *
 * @package horizon.util.logging
 * @access public
 * @version $Id: Logger.php 370 2006-10-17 05:19:38Z mojavelinux $
 */
class Logger extends Object
{
	var $name;

	var $level;

	var $appenders;

	function Logger($name)
	{
		$this->name = $name;
		$this->level = c('LogLevel::DEBUG');
		$this->appenders = array();
	}

	function addAppender(&$appender)
	{
		$this->appenders[] =& $appender;
	}

	function setLevel($level)
	{
		$this->level = $level;
	}

	function getLevel()
	{
		return $this->level;
	}

	function log($level, $msg, $e = null)
	{
		if ($this->isLoggable($level))
		{
			if (is_a($e, 'RootException'))
			{
				$msg .= "\n" . $e->getStackTrace("\n");
			}

			// TODO: do we use an iterator in this case, if so?
			for ($i = 0; $i < count($this->appenders); $i++)
			{
				$this->appenders[$i]->append($level, $this->name, $msg);
			}
		}
	}

	/**
	 * NOTE: there is no reason to retain a reference to the error object
	 */
	function trace($msg, $e = null)
	{
		$this->log(c('LogLevel::TRACE'), $msg, $e);
	}

	/**
	 * NOTE: there is no reason to retain a reference to the error object
	 */
	function debug($msg, $e = null)
	{
		$this->log(c('LogLevel::DEBUG'), $msg, $e);
	}

	/**
	 * NOTE: there is no reason to retain a reference to the error object
	 */
	function info($msg, $e = null)
	{
		$this->log(c('LogLevel::INFO'), $msg, $e);
	}

	/**
	 * NOTE: there is no reason to retain a reference to the error object
	 */
	function warn($msg, $e = null)
	{
		$this->log(c('LogLevel::WARN'), $msg, $e);
	}

	/**
	 * NOTE: there is no reason to retain a reference to the error object
	 */
	function error($msg, $e = null)
	{
		$this->log(c('LogLevel::ERROR'), $msg, $e);
	}

	/**
	 * NOTE: there is no reason to retain a reference to the error object
	 */
	function fatal($msg, $e = null)
	{
		$this->log(c('LogLevel::FATAL'), $msg, $e);
	}

	function isLoggable($level)
	{
		if (is_string($level))
		{
			$level = LogLevel::toLevel($level);
		}
		
		return $level >= $this->level;
	}

	function &getLogger($name)
	{
		$manager =& LogManager::getLogManager();
		$logger =& $manager->getLogger($name);	
		return $logger;
	}
}
