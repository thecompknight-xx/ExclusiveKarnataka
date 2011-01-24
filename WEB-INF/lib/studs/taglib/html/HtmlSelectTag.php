<?php
/* $Id: HtmlSelectTag.php 337 2006-05-09 04:18:58Z mojavelinux $
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
import('studs.taglib.TagUtils');

/**
 * @package studs.taglib.html
 * @author Dan Allen
 */
class HtmlSelectTag extends BaseInteractiveTag
{
	var $property = null;

	var $value = null;

	var $selectedValue = null;

	function setProperty($property)
	{
		$this->property = $property;
	}

	function getInputName()
	{
		return str_replace('.', chr(183), $this->property);
	}

	function setValue($value)
	{
		$this->value = $value;
	}

	// TODO: handle multiple selects
	function getSelectedValue()
	{
		return $this->selectedValue;
	}

	function doStartTag()
	{
		if (!is_null($this->value))
		{
			$this->selectedValue = $this->value;
		}
		else
		{
			$this->selectedValue = TagUtils::lookup($this->pageContext, c('StudsConstants::BEAN_KEY'), $this->property);
		}

		if (is_null($this->styleId))
		{
			$this->styleId = $this->getInputName();
		}

		$xhtml = '<select name="' . $this->getInputName() . '"';
		$xhtml .= $this->renderStyleAttributes();
		$xhtml .= $this->renderMetaAttributes();
		$xhtml .= $this->renderEventAttributes();
		$xhtml .= '>';
		echo $xhtml;
		return c('Tag::EVAL_BODY_INCLUDE');
	}

	function doEndTag()
	{
		echo '</select>';
		return c('Tag::EVAL_PAGE');
	}

	function release()
	{
		$this->selectedValue = null;
		$this->property = null;
		parent::release();
	}
}
?>
