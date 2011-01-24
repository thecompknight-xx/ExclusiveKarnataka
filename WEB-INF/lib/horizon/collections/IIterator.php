<?php
/* $Id: IIterator.php 212 2005-06-21 21:23:55Z mojavelinux $
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
 * Class <b>IIterator</b> is an interface for iterator implementations.
 *
 * The comments for each method decribe the exact behavior the method should
 * implement.
 *
 * When an iterator is created for some object, it's imperative that the
 * object doesn't change for the duration of the iteration. This may or may
 * not lead to unexpected results, depending on the object iterated over.
 * However, it is possible to modify the object returned by the
 * {@link next()} method of the iterator. For example, when
 * iterating over arrays with an {@link ListIterator}, no elements
 * should be removed from or added to the array, but individual elements may
 * be altered.
 *
 * Given an iterator <var>$it</var>, the iteration loop is run as follows:
 *
 * <pre>
 *   for ($it &= new IIterator(); $it->hasNext();)
 *   {
 *   	$item =& $it->next();
 *   }
 * </pre>
 *
 * @package horizon.collections
 * @abstract
 * @author Vincent Oostindie
 * @author Dan Allen
 */
class IIterator extends Object
{
	/**
	 * Create a new iterator that's immediately ready for use.  Normally,
	 * the constructor calls {@link reset()}.
	 */
	function IIterator() {}

	/**
	 * Returns the next ordered element from the iterator. The behavior of this
	 * method is undefined if {@link hasNext()} returns <kbd>false</kbd>.
	 *
	 * @return object
	 */
	function &next()
	{
		die('Method <b>next</b> of class <b>IIterator</b> is not implemented.');
	}

	/**
	 * Returns true if the iterator has remaining elements.
	 *
	 * @return bool
	 */
	function hasNext()
	{
		die('Method <b>hasNext</b> of class <b>IIterator</b> is not implemented.');
	}

	/**
	 * Removes the last returned element from the iterator that produced it.
	 *
	 * @return void
	 */
	function remove()
	{
		die('Method <b>remove</b> of class <b>IIterator</b> is not implemented.');
	}
}
?>
