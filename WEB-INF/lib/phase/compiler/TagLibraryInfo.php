<?php
/* $Id: TagLibraryInfo.php 352 2006-05-15 04:27:35Z mojavelinux $
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
 * A tag library, which includes an array of tags that match the jsp page.
 *
 * @package phase.compiler
 * @author Dan Allen
 */
class TagLibraryInfo
{
	var $tags = array();

	var $prefix;

	var $version;

	var $uri;

	function addTag(&$tag)
	{
		$this->tags[] =& $tag;
	}

	function &getTags()
	{
		return $this->tags;
	}

	/**
	 * Find a tag in this library by name.
	 *
	 * @param string $name The name of the the tag
	 * @return TagInfo The tag corresponding to the name, or null if not found
	 */
	function &getTag($name)
	{
		for ($i = 0; $i < count($this->tags); $i++)
		{
			if ($this->tags[$i]->getName() == $name)
			{
				return $this->tags[$i];
			}
		}

		return ref(null);
	}

	function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}

	function getPrefix()
	{
		return $this->prefix;
	}

	function setVersion($version)
	{
		$this->version = $version;
	}

	function getVersion()
	{
		return $this->version;
	}

	function setUri($uri)
	{
		$this->uri = $uri;
	}

	function getUri()
	{
		return $this->uri;
	}
}
?>
