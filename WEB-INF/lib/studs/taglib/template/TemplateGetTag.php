<?php
/* $Id: TemplateGetTag.php 263 2005-07-12 02:47:28Z mojavelinux $
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

import('phase.tagext.TagSupport');
/**
 * This is the tag handler for &lt;template:get&gt;, which gets 
 * content from the request scope and either includes the content or prints 
 * it, depending upon the value of the content's direct attribute.
 *
 * @package studs.taglib.template
 * @author Timur Vafin
 * @author Dan Allen <br />
 * <b>Credits: David Geary</b>
 */
class TemplateGetTag extends TagSupport
{
	/**
	* The name of the content that this tag includes (or prints).
	*/
	var $name;

	function init()
	{
		$this->name	= null;
	}

	/**
	* Set the name attribute
	* @param name The name of the content to get.
	*/
	function setName($name)
	{
		$this->name = $name;
	}

	/**
	* Print content named by setName() or include it, depending
	* on the content's direct attribute.
	*/
	function doStartTag()
	{
		$map =& ContentMapStack::peek($this->pageContext);
		
		$content =& $map->get($this->name);

		if ($content != null)
		{
			if ($content->isDirect())
			{
				echo $content->toString();
			}
			else
			{
				$this->pageContext->doInclude($content->toString());
			}
		}
		
		return c('Tag::SKIP_BODY');	
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
