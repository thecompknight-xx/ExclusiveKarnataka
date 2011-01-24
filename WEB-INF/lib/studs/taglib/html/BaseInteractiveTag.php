<?php
/* $Id: BaseInteractiveTag.php 335 2006-03-24 15:49:39Z mojavelinux $
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

import('phase.tagext.BodyTagSupport');
import('horizon.beanutils.ConvertUtils');

/**
 * The <b>BaseInteractiveTag</b> is an abstract base tag for all
 * form element and link tags.  It carries all the various styles, events
 * and metadata that each tag will need to share.
 *
 * @package studs.taglib.html
 * @author Dan Allen
 */
class BaseInteractiveTag extends BodyTagSupport
{
	var $tabindex = null;	

	var $disabled = false;

	var $style = null;

	var $styleClass = null;

	var $styleId = null;

	var $title = null;

	var $alt = null;

	var $onclick = null;

	var $onchange = null;

	function setTabindex($tabindex)
	{
		$this->tabindex = $tabindex;
	}

	function setDisabled($disabled)
	{
		$this->disabled = ConvertUtils::convert($disabled, 'boolean');
	}

	function setStyle($style)
	{
		$this->style = $style;
	}

	function setStyleClass($styleClass)
	{
		$this->styleClass = $styleClass;
	}

	function setStyleId($styleId)
	{
		$this->styleId = $styleId;
	}

	function setTitle($title)
	{
		$this->title = $title;
	}

	function setOnclick($event)
	{
		$this->onclick = $event;
	}

	function setOnchange($event)
	{
		$this->onchange = $event;
	}

	/**
	 * Prepares the style attributes for inclusion in the component's XHTML tag.
	 */
	function renderStyleAttributes()
	{
		$styles = '';

		if (!is_null($this->style))
		{
			$styles .= ' style="' . $this->style . '"';
		}

		if (!is_null($this->styleClass))
		{
			$styles .= ' class="' . $this->styleClass . '"';
		}

		if (!is_null($this->styleId))
		{
			$styles .= ' id="' . $this->styleId . '"';
		}

		return $styles;
	}

	function renderMetaAttributes()
	{
		$meta = '';

		if (!is_null($this->title))
		{
			$meta .= ' title="' . $this->title . '"';
		}

		// NOTE: perhaps image tag should have an HtmlBaseImageTag parent?
		if (!is_null($this->alt) && (is_a($this, 'HtmlImageTag') || is_a($this, 'HtmlImgTag')))
		{
			$meta .= ' alt="' . $this->alt . '"';
		}

		return $meta;
	}

	function renderEventAttributes()
	{
		$events = '';

		if (!is_null($this->onclick))
		{
			$events .= ' onclick="' . $this->onclick . '"';
		}

		if (!is_null($this->onchange))
		{
			$events .= ' onchange="' . $this->onchange . '"';
		}

		return $events;
	}
}
?>
