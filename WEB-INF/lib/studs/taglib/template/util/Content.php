<?php
/* $Id: Content.php 263 2005-07-12 02:47:28Z mojavelinux $
 *
 * Copyright 2003-2004 Dan Allen, Mojavelinux.com (dan.allen@mojavelinux.com)
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
 *  A utility file for templates.
 *
 *  <p>
 *  This represents template content, which is included by templates. 
 *  Templates can also treat content as plain text and print it to the
 *  implicit out variable.
 * 
 *  This simple class maintain two properties:
 *  <ul>
 *  <li><i>content</i>: A string representing either a URI or text.</li>
 *  <li><i>direct</i>: If true, content is printed; otherwise content is 
 *  included (default is false).</li>
 *  </ul>
 *  </p>
 *
 * @author Timur Vafin <br />
 * <b>Credits:</b> David Geary
 */
class Content extends Object
{
	/**
	 *  Templates regard this as content to be either included or 
	 *  printed directly.<br> This is a blank final that is
	 *  set at construction.
	 * 
	 */
	var $content;

	/**
	 * Represents a boolean; if true, content is included, otherwise
	 * content is printed.
	 */
	var $direct;

	/**
	 * The only constructor.
	 *
	 * @param content The content's URI
	 * @param direct Is content printed directly (true) or included (false)?
	 */
	function Content($content, $direct)
	{
		$this->content = $content;
		$this->direct = $direct;
	}

	/**
	 * @return Content 
	 */
	function getContent()
	{
		return $this->content;
		
	}

	/**
	 * Is content to be printed directly (isDirect() == true) <br>
	 * instead of included (isDirect() == false)?
	 */
	function isDirect()
	{
		return $this->direct;
	}

	/**
	 * @return string A string representation of the content
	 */
	function toString()
	{ 
		return $this->content;
	}
}
?>
