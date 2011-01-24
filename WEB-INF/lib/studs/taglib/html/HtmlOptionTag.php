<?php
/* $Id: HtmlOptionTag.php 335 2006-03-24 15:49:39Z mojavelinux $
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

/**
 * @package studs.taglib.html
 * @author Dan Allen
 */
class HtmlOptionTag extends BaseInputTag
{
	var $filter = true;

	var $label = null;

	function setFilter($filter)
	{
		$this->filter = $filter;
	}

	function setLabel($label) {
		$this->label = $label;
	}

	function doStartTag()
	{
		return c('Tag::EVAL_BODY_BUFFERED');
	}

	function doEndTag()
	{
		// disallow styleId since it would be invalid xhtml
		$this->styleId = null;

		$parent =& $this->getParent();
		if (!is_a($parent, 'HtmlSelectTag'))
		{
			throw_exception(new PhaseException('Illegal use of "option"-style tag without "select" as a direct parent'));
			return;
		}

		$selectedValue = $parent->getSelectedValue();

		$xhtml = '<option value="' . $this->value . '"';
		if ($this->value == $selectedValue)
		{
			$xhtml .= ' selected="selected"';
		}

		$label = $this->label;
		if (is_null($label))
		{
			$label = $this->bodyContent;
			if ($this->bodyContent == "")
			{
				$label = $this->value;
			}
		}

		$xhtml .= $this->renderStyleAttributes();
		$xhtml .= $this->renderMetaAttributes();
		$xhtml .= '>' . ($this->filter ? htmlspecialchars($label) : $label) . '</option>';

		echo $xhtml;
	}
}
?>
