<?php
/* $Id: StringReader.php 188 2005-04-07 04:52:31Z mojavelinux $
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

/**
 * @package horizon.io
 * @author Dan Allen
 */
class StringReader extends Reader
{
	var $pos = 0;

	var $length = null;

	function StringReader($string)
	{
		$this->handle = $string;
		$this->length = strlen($string);
	}

	function read(&$buffer, $offset = 0, $len = -1)
	{
		if ($len == 0)
		{
			return 0;
		}
		elseif ($len == -1)
		{
			$data = substr($this->handle, $this->pos);
		}
		else
		{
			$data = substr($this->handle, $this->pos, $len);
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
		$char = $this->handle{$this->pos++};
		return is_null($char) ? -1 : ord($char);
	}

	function mark()
	{
		$this->mark = $this->pos;
	}

	function close()
	{
		$this->handle = null;
	}

	function reset()
	{
		$this->pos = $this->mark;
	}

	function skip($length)
	{
		$this->pos += $length;
		if ($this->pos >= $this->length)
		{
			$length = $length - ($this->pos - $this->length);
			$this->pos = $this->length;
		}
		else
		{
			return $length;
		}
	}

	function ready()
	{
		if (is_null($this->handle) || $this->pos >= $this->length)
		{
			return false;
		}

		return true;
	}
}
?>
