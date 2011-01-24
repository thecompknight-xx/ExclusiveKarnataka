<?php
/* $Id: HtmlTextTag.php 335 2006-03-24 15:49:39Z mojavelinux $
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
 * Handles the rendering of an html <input type="text" /> tag
 * or a <textarea> tag (if multiline="true" is specified)
 *
 * @package studs.taglib.html
 * @author Dan Allen
 */
class HtmlTextTag extends BaseInputTag
{
	var $maxlength = null;

	var $multiline = false;

	function setMaxlength($maxlength)
	{
		$this->maxlength = $maxlength;
	}

	function setMultiline($multiline) {
		$this->multiline = ConvertUtils::convert($multiline, 'boolean');
	}

	function renderTag()
	{
		if (!is_null($this->value))
		{
			$value = $this->value;
		}
		else
		{
			$value = htmlspecialchars(TagUtils::lookup($this->pageContext, c('StudsConstants::BEAN_KEY'), $this->property));
		}

		if (is_null($this->styleId))
		{
			$this->styleId = $this->getInputName();
		}

		if ($this->multiline) {
			$xhtml = '<textarea ';
		}
		else {
			$xhtml = '<input type="text" ';
		}

		$xhtml .= 'name="' . $this->getInputName() . '"';
		if (!$this->multiline) {
			$xhtml .= ' value="' . $value . '"';
		}

		// TODO: could use some javascript to ensure maxlength on textarea
		if (!$this->multiline && !is_null($this->maxlength))
		{
			$xhtml .= ' maxlength="' . intval($this->maxlength) . '"';
		}

		$xhtml .= $this->renderStyleAttributes();
		$xhtml .= $this->renderMetaAttributes();
		if ($this->multiline) {
			$xhtml .= '>' . $value . '</textarea>';
		}
		else {
			$xhtml .= ' />';
		}

		return $xhtml;
	}

	function release() {
		$this->maxlength = null;
		$this->multiline = false;
		parent::release();
	}
}
?>
