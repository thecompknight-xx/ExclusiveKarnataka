<?php
/* $Id: HtmlSubmitTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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
class HtmlSubmitTag extends BaseInteractiveTag
{
	var $value = null;

	var $label = null;

	function setValue($value)
	{
		$this->value = $value;
	}

	function doStartTag()
	{
		return c('Tag::EVAL_BODY_BUFFERED');
	}

	function doEndTag()
	{
		$this->label = trim($this->value);
		if (strlen($this->label) == 0 && strlen(trim($this->bodyContent)) != 0)
		{
			$this->label = trim($this->bodyContent);
		}

		if (strlen($this->label) == 0)
		{
			$this->label = 'Submit';
		}

		echo $this->renderTag();

		return c('Tag::EVAL_PAGE');
	}

	function renderTag()
	{
		$xhtml = '<input type="submit" value="' . $this->label . '"';
		$xhtml .= $this->renderStyleAttributes();
		$xhtml .= $this->renderMetaAttributes();
		$xhtml .= ' />';

		return $xhtml;
	}
}
?>
