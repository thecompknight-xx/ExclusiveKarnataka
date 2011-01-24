<?php
/* $Id: ListIterator.php 220 2005-06-23 19:38:30Z mojavelinux $
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

import('horizon.collections.IIterator');

/**
 * Class <b>ListIterator</b> offers an implementation of the
 * {@link Iterater} interface for iterating over the elements in an array.
 *
 * The advantage of this iterator over the built-in array iteration functions
 * is two-fold:
 *
 * <ol>
 *   <li>
 *     This is a genuine {@link Iterater}, and can therefore be used in
 *     generic algorithms.
 *   </li>
 *   <li>
 *     It's possible to run multiple iterations on the same array concurrently.
 *     However, the underlying collection may not be modified while using the iterator.
 *   </li>
 * </ol>
 *
 * This class uses the keys of the array when the iterator is created so that
 * it can operate independently of a second iterator.
 *
 * @package horizon.collections.iterators
 * @author Dan Allen
 * @access public
 */
class ListIterator extends IIterator
{
	/**
	 * The array over which to iterate
	 *
	 * @var array
	 */
	var $array;

	/**
	 * Current iteration index
	 *
	 * @var int
	 */
	var $index;

	/**
	 * Array of keys for the collection
	 *
	 * @var array
	 */
	var $keys;

	/**
	 * The length of the collection
	 *
	 * @var int
	 */
	var $length;

	/**
	 * Setup an iterator object for an native PHP array.
	 *
	 * @param array $array The array from which to create an iterator.
	 */
	function ListIterator(&$array)
	{
		$this->array =& $array;
		$this->index = -1;
		$this->keys = array_keys($array);
		$this->length = count($array);
	}

	/**
	 * @return object
	 */
	function &next()
	{
		// @todo make sure our keys have not changed, we cannot have a modification
		// to the collection while we are iterating

		if ($this->index + 1 >= $this->length)
		{
			// @todo throw proper error
			throw_exception(new RootException('Next element not available'));
		}

		return $this->array[$this->keys[++$this->index]];
	}

	/**
	 * @returns bool
	 */
	function hasNext()
	{
		return $this->index + 1 < $this->length;
	}

	/**
	 * @return void
	 */
	function remove()
	{
		if ($this->index >= 0)
		{
			// NOTE: the array will be passed by reference
			array_splice($this->array, $this->index, 1);
			$this->length--;
			$this->index--;
		}
		else
		{
			throw_exception(new IllegalStateException('Array index out of bounds'));
		}
	}
}
?>
