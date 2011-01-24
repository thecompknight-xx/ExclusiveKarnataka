<?php
/* $Id: Reader.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package horizon.io
 * @author Dan Allen
 */
class Reader extends Object
{
	var $_handle = null;

	var $_mark = 0;

	/**
	 * Read a set number of characters into the buffer
	 */
	function read(&$buffer, $offset = 0, $length = null) {}

	/**
	 * Read a single character in the stream
	 */
	function readChar() {}

	/**
	 * Close the stream
	 */
	function close() {}

	/**
	 * Skip a certain number of characters
	 */
	function skip($length) {}

	/**
	 * Mark the present position in the stream
	 */
	function mark() {}

	/**
	 * Reset the stream
	 */
	function reset() {}

	/**
	 * Tell whether the stream is ready to be read
	 */
	function ready() {}
}
?>
