<?php
/* $Id: FileReader.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('horizon.io.Reader');
import('horizon.io.FileNotFoundException');
import('horizon.io.FileLock');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.io
 */
class FileReader extends Reader
{
	var $_filePath = null;

	/**
	 * @throws FileNotFoundException
	 */
	function FileReader(&$file)
	{
		if (is_resource($file))
		{
			$this->_handle =& $file;
		}
		elseif (is_object($file) && $file->getClassName() == 'horizon.io.File')
		{
			$this->_filePath = $file->getPath();
			$this->_handle = @fopen($this->_filePath, 'rb');
		}
		else
		{
			$this->_filePath = $file;
			$this->_handle = @fopen($this->_filePath, 'rb');
		}

		if (!$this->_handle)
		{
			throw_exception(new FileNotFoundException('File could not be found \'' . $file . '\''));
		}
	}

	/**
	 * Lock the file using a shared lock (since the operation is read)
	 *
	 * @param boolean $shared Whether the lock should be shared
	 * @param boolean $blocking Whether or not to block operations natively
	 */
	function &lock($shared = true, $blocking = false)
	{
		$lock =& new FileLock($this->_handle, $shared, $blocking);
		if (!$lock->isValid())
		{
			return ref(null);
		}

		return $lock;
	}

	/**
	 * Reads up to len bytes of data from the file pointer into a string
	 * reference An attempt is made to read as many as len bytes, but a smaller
	 * number may be read, possibly zero (if end of file). The number of bytes
	 * actually read is returned as an integer.  The offset is the position in
	 * the buffer string which the characters should be stored.
	 */
	function read(&$buffer, $offset = 0, $len = -1)
	{
		$pos = ftell($this->_handle);
		
		if ($len == 0)
		{
			return 0;
		}
		// @todo make sure the file is not locked too
		elseif ($len == -1 && $pos == 0 && !is_null($this->_filePath))
		{
			$data = file_get_contents($this->_filePath);
			// url reading will fail to seek since they are remote...silence any
			// errors, we do our best to go to the end of the file, but we cannot
			// guarantee it for this very reason.
			@fseek($this->_handle, 0, SEEK_END);
		}
		elseif ($len == 1)
		{
			$data = fgetc($this->_handle);
		}
		else
		{
			$data = fread($this->_handle, $len);
		}

		$dataSize = strlen($data);
		if ($dataSize == 0)
		{
			return -1;
		}
		elseif ($offset == 0)
		{
			$buffer = $data;
		}
		else
		{
			$buffer = substr($buffer, 0, $offset) . $data . substr($buffer, $dataSize + 1);
		}

		return $dataSize;
	}

	function readChar()
	{
		$char = fgetc($this->_handle);
		return $char === false ? -1 : ord($char);
	}

	function skip($len)
	{
		$data = fread($this->_handle, $len);
		return strlen($data);
	}

	function mark()
	{
		$this->_mark = ftell($this->_handle);
	}

	function ready()
	{
		if (!$this->_handle || feof($this->_handle))
		{
			return false;
		}

		return true;
	}

	function reset()
	{
		fseek($this->_handle, $this->_mark);
	}

	/**
	 * Closes this file input stream and releases any system resources associated with the stream.
	 *
	 * @return void
	 * @throws IOException
	 */
	function close()
	{
		if ($this->_handle)
		{
			if (!@fclose($this->_handle))
			{
				// throw_exception(new IOException());
			}
		}
	}
}
?>
