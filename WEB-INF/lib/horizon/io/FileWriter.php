<?php
/* $Id: FileWriter.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('horizon.io.FileLock');
import('horizon.io.IOException');
import('horizon.io.Writer');

/**
 * @package horizon.io
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @access public
 */
class FileWriter extends Writer
{
	function FileWriter(&$file, $append = false)
	{
		if ($append)
		{
			$mode = 'a';
		}
		else
		{
			$mode = 'w';
		}

		// try {
		if (is_resource($file))
		{
			$this->_handle =& $file;
		}
		elseif (is_object($file) && is_a($file, 'File'))
		{
			$path = $file->getPath();
			$this->_handle = fopen($path, $mode);
		}
		else
		{
			$this->_handle = fopen($file, $mode);
		}
		// } catch (RootException $e) {
		if ($e = catch_exception())
		{
			throw_exception(new IOException('File could not be opened for writing: ' . String::valueOf($file) . '; reason: ' . $e->getMessage(), $e));
		}
		// }
	}

	/**
	 * Lock the file using an exclusive lock (since the operation is write)
	 *
	 * @param boolean $blocking Whether or not to block operations natively
	 */
	function &lock($blocking = true)
	{
		$lock =& new FileLock($this->_handle, false, $blocking);
		if (!$lock->isValid())
		{
			return ref(null);
		}

		return $lock;
	}

	// @todo implement offset and length
	function write($data, $offset = 0, $length = null)
	{
		fwrite($this->_handle, $data);
	}

	function ready()
	{
		return $this->_handle ? true : false;
	}

	/**
	 * Truncate the file to 0 length.
	 * This method is provided so that a FileWriter can be created
	 * and then locked before the file is truncated.
	 */
	function truncate()
	{
		ftruncate($this->_handle, 0);
	}

	function close()
	{
		@fclose($this->_handle);
		$this->_handle = null;
	}
}
?>
