<?php
/* $Id: String.php 352 2006-05-15 04:27:35Z mojavelinux $
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
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.lang
 */
class String extends Object
{
	/**
	 * @access protected
	 */
	var $_str = null;

	function String($value)
	{
		$this->_str = (string) $value;
	}

	function charAt($index)
	{
		return $this->_str{$index};
	}

	function concat($str)
	{
		return new String($this->_str . $str);	
	}

	function endsWith($suffix)
	{
		return substr($this->_str, -strlen($suffix)) == $suffix;
	}

	function equals($str)
	{
		return $this->_str == $str;
	}

	// @todo need offset here
	function lastIndexOf($str)
	{
		$pos = strrpos($this->_str, $str);
		return $pos === false ? -1 : $pos;
	}

	function length()
	{
		return strlen($this->_str);
	}

	function indexOf($str, $fromIndex = 0)
	{
		$pos = strpos($this->_str, $str, $fromIndex);
		return $pos === false ? -1 : $pos;
	}

	// @todo offset for prefix
	function startsWith($prefix)
	{
		return (strlen($prefix) == 0 || strpos($this->_str, $prefix) === 0) ? true : false;
	}

	function substring($beginIndex, $endIndex = null)
	{
		if (is_null($endIndex))
		{
			$endIndex = strlen($this->_str);
		}

		$endIndex -= $beginIndex;

		return new String(substr($this->_str, $beginIndex, $endIndex));
	}

	function toLowerCase()
	{
		return new String(strtolower($this->_str));
	}

	function toUpperCase()
	{
		return new String(strtoupper($this->_str));
	}

	function toString()
	{
		return $this->_str;
	}

	function trim()
	{
		return new String(trim($this->_str));
	}

	/**
	 * NOTE: we don't need to pass this by reference since it is being used read-only
	 * @static
	 */
	function valueOf($o)
	{
		if (is_null($o))
		{
			return 'null';
		}
		elseif (is_object($o))
		{
			if (is_a($o, 'Object'))
			{
				return $o->toString();
			}
			else
			{
				return var_dump($o, true);
			}
		}
		elseif (is_bool($o))
		{
			return $o ? 'true' : 'false';
		}
		else
		{
			return (string)$o;
		}
	}
}
?>
