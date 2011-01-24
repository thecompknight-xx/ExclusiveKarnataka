<?php
/* $Id: LogLevel.php 321 2006-03-11 05:07:42Z mojavelinux $
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

def('LogLevel::ALL',   0);
def('LogLevel::TRACE', 10000);
def('LogLevel::DEBUG', 20000);
def('LogLevel::INFO',  30000);
def('LogLevel::WARN',  40000);
def('LogLevel::ERROR', 50000);
def('LogLevel::FATAL', 60000);
def('LogLevel::OFF',   1E100);

/**
 * @package horizon.util.logging
 * @author Dan Allen
 */
class LogLevel
{
	function valueOf($level)
	{
		if ($level == c('LogLevel::TRACE'))
		{
			return 'TRACE';
		}

		if ($level == c('LogLevel::DEBUG'))
		{
			return 'DEBUG';
		}

		if ($level == c('LogLevel::INFO'))
		{
			return 'INFO';
		}

		if ($level == c('LogLevel::WARN'))
		{
			return 'WARN';
		}

		if ($level == c('LogLevel::ERROR'))
		{
			return 'ERROR';
		}

		if ($level == c('LogLevel::FATAL'))
		{
			return 'FATAL';
		}
	}

	function toLevel($val, $defaultLevel = null)
	{
		if (is_null($defaultLevel))
		{
			$defaultLevel = c('LogLevel::DEBUG');
		}

		// if there is no value specifid, use the default
		if (empty($val))
		{
			return $defaultLevel;
		}

		$val = strtoupper($val);

		if ($val == 'ALL')
		{
			return c('LogLevel::ALL');
		}

		if ($val == 'FATAL')
		{
			return c('LogLevel::FATAL');
		}

		if ($val == 'ERROR')
		{
			return c('LogLevel::ERROR');
		}

		if ($val == 'WARN')
		{
			return c('LogLevel::WARN');
		}

		if ($val == 'INFO')
		{
			return c('LogLevel::INFO');
		}

		if ($val == 'DEBUG')
		{
			return c('LogLevel::DEBUG');
		}

		if ($val == 'TRACE')
		{
			return c('LogLevel::TRACE');
		}

		if ($val == 'OFF')
		{
			return c('LogLevel::OFF');
		}

		return c('LogLevel::DEBUG');
	}
}
?>
