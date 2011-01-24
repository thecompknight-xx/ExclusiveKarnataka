<?php
/* $Id: FormatFormatDateTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('phase.tagext.TagSupport');
import('phase.support.ELEvaluator');
import('horizon.util.StringUtils');
 
/**
 * A handler for &lt;formatDate&gt; that accepts attributes as Strings
 * and evaluates them as expressions.
 * @todo This tag is very incomplete right now and basically has what we need to get by
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package phase.tags.fmt
 * @access public
 */
class FormatFormatDateTag extends TagSupport
{
	var $value;

	var $dateStyle;

	var $timeStyle;

	var $pattern;

	function FormatFormatDateTag()
	{
		$this->init();
	}

	function init()
	{
		$this->value = null;
		$this->dateStyle = null;
		$this->timeStyle = null;
		$this->pattern = null;
	}

	function setValue($value)
	{
		$this->value = $value;
	}

	function setDateStyle($dateStyle)
	{
		$this->dateStyle = $dateStyle;
	}

	function setTimeStyle($timeStyle)
	{
		$this->timeStyle = $timeStyle;
	}

	function setPattern($pattern)
	{
		$this->pattern = $pattern;
	}

	function setType($type)
	{
		$this->type = $type;
	}

	function doEndTag()
	{
		// @todo what format are we going to require dates in? int, Date object or strings?
		// I was using strtotime() on the value previously
		$value = ELEvaluator::evaluate($this->value, 'int');
		$this->out('echo date(' . StringUtils::quote($this->pattern) . ', ' . $value . ');');
	}
	
	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
