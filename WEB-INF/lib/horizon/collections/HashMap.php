<?php
/* $Id: HashMap.php 370 2006-10-17 05:19:38Z mojavelinux $
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
 * @package horizon.collections
 * @author Dan Allen
 *
 * NOTE: it could be a problem that this class doesn't deal with
 * references
 */
class HashMap extends Object
{
	/**
	 * @var array
	 * @access private
	 */
	var $_entries;

	function HashMap($map = array())
	{
		$this->_entries = $map;
	}

	function clear()
	{
		$this->entries = array();
	}

	function contains($value)
	{
		return in_array($value, $this->_entries);
	}

	function containsKey($key)
	{
		return array_key_exists($key, $this->_entries);
	}

	function elements()
	{
		return array_values($this->_entries);
	}

	function &get($key)
	{
		if (!array_key_exists($key, $this->_entries))
		{
			$nil = ref(null);
			return $nil;
		}

		return $this->_entries[$key];
	}

	function isEmpty()
	{
		return count($this->_entries) == 0;
	}

	function keys()
	{
		return array_keys($this->_entries);
	}

	// NOTE: be sure to note, not putting by reference!!
	function put($name, $value)
	{
		$this->_entries[$name] = $value;	
	}

	function remove($name)
	{
		if (array_key_exists($name, $this->_entries))
		{
			unset($this->_entries[$name]);
		}
	}

	function size()
	{
		return count($this->_entries);
	}
}
?>
