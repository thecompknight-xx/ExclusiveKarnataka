<?php
/* $Id: CoreSetTag.php 263 2005-07-12 02:47:28Z mojavelinux $
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

/**
 * @package phase.tags.core
 * @access public
 * @author Dan Allen <dan.allen@mojavelinux.com>
 *
 * TODO: handle case when 'var' is an EL expression and we have to use the property attribute
 */
class CoreSetTag extends BodyTagSupport
{
	var $var;

	var $value;

	var $valueExpression;

	var $scope;

	var $bodyIsValue;

	function CoreSetTag()
	{
		$this->init();
	}

	function init()
	{
		unset($this->value);
		$this->value = null;
		$this->valueExpression = null;
		$this->scope = 'page';
		$this->var = null;
		$this->bodyIsValue = false;
	}

	function setVar($var)
	{
		$this->var = $var;
	}

	function setValue($value)
	{
		$this->valueExpression = $value;
	}

	// @todo perhaps add a Util call to resolve scope
	function setScope($scope)
	{
		$this->scope = $scope;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();

		// @fixme this doesn't work if we never set the value attribute!!!
		if ($this->value != '')
		{
			// @todo make sure 'var' is set
			$this->pageContext->setAttribute($this->var, $this->value, $this->scope);
			return c('Tag::SKIP_BODY');
		}

		$this->bodyIsValue = true;
		return parent::doStartTag();
	}

	function doEndTag()
	{
		if ($this->bodyIsValue)
		{
			$this->pageContext->setAttribute($this->var, trim($this->bodyContent), $this->scope);
		}

		return c('Tag::EVAL_PAGE');
	}

	function evaluateExpressions()
	{
		if (!is_null($this->valueExpression))
		{
			$this->value =& ELEvaluator::evaluate('value', $this->valueExpression, 'object', $this->pageContext);
		}
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
