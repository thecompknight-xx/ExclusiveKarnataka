<?php
/* $Id: FileNotFoundException.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.io.IOException');

/**
 * Signals that an attempt to open the file denoted by a specified pathname has failed.
 *
 * This exception will be thrown by the IO constructors when a file/url with
 * the specified pathname does not exist. It will also be thrown by these
 * constructors if the file does exist but for some reason is inaccessible, for
 * example when an attempt is made to open a read-only file for writing.
 * 
 * @package horizon.io
 * @author Dan Allen
 */
class FileNotFoundException extends IOException
{
	/**
	 * Constructs a FileNotFoundException with the specified detail message.
	 * @param string message The detail of the exception
	 */
	function FileNotFoundException($message = null)
	{
		parent::IOException($message);
	}
}
?>
