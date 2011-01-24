<?php
/* $Id: HtmlCheckboxTag.php 335 2006-03-24 15:49:39Z mojavelinux $
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

import('studs.taglib.html.BaseInputTag');
import('studs.taglib.TagUtils');
import('horizon.beanutils.ConvertUtils');

/**
 * @package studs.taglib.html
 * @author Greg Heartsfield
 * @author Dan Allen
 */
class HtmlCheckboxTag extends BaseInputTag
{
	/**
	 * Override the doStartTag() to capture the body, which can be used as the
	 * label.
	 */
	function doStartTag()
	{
		parent::doStartTag();
		return c('Tag::EVAL_BODY_BUFFERED');
	}

	/**
	 * TODO: perhaps we can use "indexed" to automatically append
	 * the [] at the end of the property name, therefore not requiring
	 * it from the front end
	 */
	function renderTag()
	{
		if (is_null($this->value))
		{
			$this->value = 'on';
		}

		/* we would like to do this, but cannot violate xhtml id rules
		if (is_null($this->styleId))
		{
			$this->styleId = $this->property;
		}
		*/

		$xhtml = '<input type="checkbox" name="' . $this->getInputName() . '" value="' . $this->value . '"';

		if ($this->isChecked())
		{
			$xhtml .= ' checked="checked"';
		}

		$xhtml .= $this->renderStyleAttributes();
		$xhtml .= $this->renderMetaAttributes();
		$xhtml .= ' />';
		return $xhtml;
	}

	/**
	 * Lookup the value of the property on this form bean and determine if it
	 * is equal to the target or has a boolean 'true' value.
	 * @protected
	 */
	function isChecked()
	{
		$result = TagUtils::lookup($this->pageContext, c('StudsConstants::BEAN_KEY'), $this->property);
		if (is_a($result, 'Object'))
		{
			$result = $result->toString();
		}

		return ($this->value == $result || ConvertUtils::convert($result, 'boolean'));
	}

	/**
	 * The body of the checkbox tag can be used as a label, so output that label here.
	 */
	function doEndTag()
	{
		if (!is_empty($this->bodyContent))
		{
			echo ' ' . $this->bodyContent;
		}
	}
}
?>
