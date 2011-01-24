<?php
/* $Id: ConvertUtils.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package horizon.beanutils
 */
class ConvertUtils
{
	function convert($value, $type)
	{
		$method = '_convert' . ucfirst($type);
		return ConvertUtils::$method($value);
	}

	function _convertString($value)
	{
		// if we are already dealing with a string
		if (is_string($value))
		{
			return $value;
		}

		// handle special cases, then just cast to string when all else fails
		if (is_null($value))
		{
			return 'null';
		}
		elseif (is_object($value))
		{
			if (method_exists($value, 'toString'))
			{
				return $value->toString();
			}
			
			return var_dump($value, true);
		}
		elseif (is_bool($value))
		{
			return $value ? 'true' : 'false';
		}
		else
		{
			return (string) $value;
		}
	}

	function _convertBoolean($value)
	{
		// if we are already dealing with a boolean,
		// just return it
		if (is_bool($value))
		{
			return $value;
		}

		// look for explicit "true" values
		$value = strtolower($value);
		if ($value == '1'
			|| $value == 'on'
			|| $value == 'true'
			|| $value == 'yes'
			|| $value == 'y')
		{
			return true;
		}

		// everything else is false
		return false;
	}
}
?>
