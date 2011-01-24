<?php
/* $Id: ServletUtils.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package stratus.util
 * @author Dan Allen
 */
class ServletUtils extends Object
{
	/**
	 * Return a context-relative path, beginning with a "/", that represents
	 * the canonical version of the specified path after ".." and "." elements
	 * are resolved out.  If the specified path attempts to go outside the
	 * boundaries of the current context (i.e. too many ".." path elements are
	 * present), return <kbd>null</kbd> instead.
	 *
	 * @param string $path
	 * @access public
	 * @return string
	 */
	function normalize($path)
	{
		if (is_null($path))
		{
			return null;
		}

		$normalized = $path;

		// replace backslashes '\' with a forward slash '/'
		if (strpos($normalized, '\\') !== false)
		{
			$normalized = str_replace('\\', '/', $normalized);
		}

		// make sure it begins with a '/'
		if (strpos($normalized, '/') !== 0)
		{
			$normalized = '/' . $normalized;
		}
		
		// if ends in directory command ('.' or '..'), append a '/'
		if (substr($normalized, -2) == '/.' || substr($normalized, -3) == '/..')
		{
			$normalized .= '/';
		}
		
		// replace references to current directory '/./' or repeated slashes '//'
		$normalized = preg_replace(';/\.?(?=/);', '', $normalized);

		// replace references to parent directory
		while (true)
		{
			$index = strpos($normalized, '/../');
			if ($index === false)
			{
				break;
			}
			// trying to go outside of context
			elseif ($index === 0)
			{
				return null;
			}
			else
			{
				$index2 = strrpos(substr($normalized, 0, $index - 1), '/');
				$normalized = substr($normalized, 0, $index2) . substr($normalized, $index + 3);
			}
		}

		return $normalized;
	}
}
?>
