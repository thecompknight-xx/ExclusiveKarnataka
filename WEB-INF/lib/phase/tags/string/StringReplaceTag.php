<?php
/* $Id: StringReplaceTag.php 263 2005-07-12 02:47:28Z mojavelinux $
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
import('phase.support.ELEvaluator');
import('horizon.util.StringUtils');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package phase.tags.str
 * @access public
 */
class StringReplaceTag extends BodyTagSupport
{
	var $replace;

	var $replaceExpression;

	var $with;

	var $withExpression;

	var $newlineToken;

	function StringReplaceTag()
	{
		$this->init();
	}

	function init()
	{
		$this->replace = '';
		$this->with = '';
		$this->newlineToken = 'LF';
	}

	function setReplace($replace)
	{
		$this->replaceExpression = $replace;
	}

	function setWith($with)
	{
		$this->withExpression = $with;
	}

	function setNewlineToken($newlineToken)
	{
		$this->newlineToken = $newlineToken;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();
		$this->replace = str_replace($this->newlineToken, "\n", $this->replace);
		$this->with = str_replace($this->newlineToken, "\n", $this->with);
		return parent::doStartTag();
	}

	function doEndTag()
	{
		$this->bodyContent = trim($this->bodyContent);
		// make sure we have something to replace
		if ($this->replace != '' && $this->bodyContent != '')
		{
			echo str_replace(html_entity_decode($this->replace), html_entity_decode($this->with), $this->bodyContent);
		}
	}

	function evaluateExpressions()
	{
		if (!is_null($this->replaceExpression))
		{
			$this->replace = ELEvaluator::evaluate('replace', $this->replaceExpression, 'string', $this->pageContext);
		}

		if (!is_null($this->withExpression))
		{
			$this->with = ELEvaluator::evaluate('replace', $this->withExpression, 'string', $this->pageContext);
		}
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
