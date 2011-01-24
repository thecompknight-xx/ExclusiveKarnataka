<?php
/* $Id: BaseInputTag.php 337 2006-05-09 04:18:58Z mojavelinux $
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

import('studs.taglib.html.BaseInteractiveTag');

/**
 * @package studs.taglib.html
 * @author Dan Allen
 */
class BaseInputTag extends BaseInteractiveTag
{
	var $value = null;

	var $property = null;
	
	function setValue($value)
	{
		$this->value = $value;
	}

	function setProperty($property)
	{
		$this->property = $property;
	}

	/**
	 * The input name is the same as the property,
	 * except that it replaces '.' with a placeholder
	 * so that nested properties can be properly supported.
	 */
	function getInputName()
	{
		return str_replace('.', chr(183), $this->property);
	}

	function doStartTag()
	{
		echo $this->renderTag();

		return c('Tag::SKIP_BODY');
	}

	/**
	 * @abstract
	 */
	function renderTag()
	{
	}
}
?>
