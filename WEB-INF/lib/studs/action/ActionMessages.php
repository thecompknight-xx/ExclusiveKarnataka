<?php
/* $Id: ActionMessages.php 218 2005-06-21 22:29:30Z mojavelinux $
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
 * @package studs.action
 * @access public
 */

/**
 * The "property name" marker to use for global messages, as opposed to
 * those related to a specific property.
 */
def('ActionMessages::GLOBAL_MESSAGE', 'studs.action.GLOBAL_MESSAGE');

class ActionMessages
{
	/**
	 * The accumulated set of messages (represented as arrays)
	 * for each property, keyed by property name.
	 * @var array
	 */
	var $messages = array();

	/**
	 * The current number of the property/key being added.  This is used
	 * to maintain the order messages are added.
	 */
	var $count = 0;

	/**
	 * Add the message to the list of message under a given property name
	 * @param string $property
	 * @param string $message
	 * @returns void
	 */
	function add($property, $message)
	{
		$list =& $this->messages[$property];

		if (!isset($list))
		{
			$list = array();
			$this->messages[$property] =& $list;
		}

		$list[$this->count++] =& $message;
	}

	/**
	 * Clear all the messages in all the lists
	 * @returns void
	 */
	function clear()
	{
		$this->messages = array();
	}

	/**
	 * Determine if there are any messages as all
	 * @returns boolean
	 */
	function isEmpty()
	{
		return count($this->messages) == 0;
	}

	/**
	 * Determine either the size of all the messages
	 * or the number of messages under a given property
	 * @returns int
	 */
	function size($property = null)
	{
		$total = 0;

		if (is_null($property))
		{
			foreach ($this->messages as $list)
			{
				$total += count($list);
			}
		}
		else
		{
			$list =& $this->messages[$property];
			if (isset($list))
			{
				$total = count($list);
			}
		}

		return $total;
	}

	/**
	 * Get the list of messages under a given property
	 * or return the list of all the messages if the property
	 * provided is <kbd>null</kbd>.
	 * @returns array
	 */
	function &get($property = null)
	{
		$messages = array();

		if (is_null($property))
		{
			if (count($this->messages) > 0)
			{
				foreach ($this->messages as $list)
				{
					foreach ($list as $index => $message)
					{
						$messages[$index] = $message;
					}
				}
				
				ksort($messages);
			}
		}
		else
		{
			$list =& $this->messages[$property];
			if (isset($list))
			{
				$messages =& $list;
			}
		}

		return $messages;
	}

	/**
	 * Return a list of the properties which are in use
	 * @returns array
	 */
	function properties()
	{
		return array_keys($this->messages);
	}
}
?>
