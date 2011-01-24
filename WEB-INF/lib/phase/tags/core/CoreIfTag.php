<?php
/* $Id: CoreIfTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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

/**
 * @package phase.tags.core
 * @access public
 * @author Dan Allen
 */
class CoreIfTag extends TagSupport // implements ELTag
{
	var $test;

	var $testExpression;

	function CoreIfTag()
	{
		$this->init();
	}

	function init()
	{
		unset($this->test);
		$this->test = null;
		$this->testExpression = null;
	}

	function setTest($test)
	{
		$this->testExpression = $test;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();
		if ($this->test)
		{
			return c('Tag::EVAL_BODY_INCLUDE');	
		}

		return parent::doStartTag();
	}

	function release()
	{
		parent::release();
		$this->init();
	}

	function evaluateExpressions()
	{
		if ($this->testExpression != null)
		{
			$this->test = ELEvaluator::evaluate('test', $this->testExpression, 'boolean', $this->pageContext);
		}
	}
}
?>
