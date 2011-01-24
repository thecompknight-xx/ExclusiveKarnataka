<?php
/* $Id: FormatMessageTag.php 263 2005-07-12 02:47:28Z mojavelinux $
 *
 * Copyright 2003-2004 Dan Allen, Mojavelinux.com (dan.allen@mojavelinux.com)
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
import('studs.taglib.TagUtils');

/**
 * Maps key to localized message and performs parametric replacement.
 *
 * @package phase.tags.format
 * @author Dan Allen
 * @author Timur Vafin
 *
 * FIXME: we are relying on the MessageResources bundle from Studs,
 * but should only have to use LocalizationBundle from Stratus.
 */
class FormatMessageTag extends TagSupport
{
	var $key;

	var $keyExpression;

	var $arg0;

	var $arg1;

	var $arg2;

	var $arg3;

	var $arg4;

	var $bundle;

	var $localeKey;

	function FormatMessageTag()
	{
		$this->init();
	}

	function init()
	{
		$this->key = null;
		$this->arg0 = null;
		$this->arg1 = null;
		$this->arg2 = null;
		$this->arg3 = null;
		$this->arg4 = null;
		$this->bundle = c('StudsConstants::MESSAGE_RESOURCES_KEY');
		$this->localeKey = c('StudsConstants::LOCALE_KEY');
	}

	function setKey($key)
	{
		$this->keyExpression = $key;
	}

	function setBundle($bundle)
	{
		$this->bundle = $bundle;
	}

	function setLocale($localeKey)
	{
		$this->localeKey = $localeKey;
	}

	function setArg0($arg0)
	{
		$this->arg0 = $arg0;
	}

	function setArg1($arg1)
	{
		$this->arg1 = $arg1;
	}

	function setArg2($arg2)
	{
		$this->arg2 = $arg2;
	}

	function setArg3($arg3)
	{
		$this->arg3 = $arg3;
	}

	function setArg4($arg4)
	{
		$this->arg4 = $arg4;
	}
	
	function evaluateExpressions()
	{
		if (!is_null($this->keyExpression))
		{
			$this->key = ELEvaluator::evaluate('value', $this->keyExpression, 'string', $this->pageContext);
		}

		// optimize argument evaluation by checking for nulls
		if (!is_null($this->arg0))
		{
			$this->arg0 = ELEvaluator::evaluate('value', $this->arg0, 'string', $this->pageContext);
		}

		if (!is_null($this->arg1))
		{
			$this->arg1 = ELEvaluator::evaluate('value', $this->arg1, 'string', $this->pageContext);
		}

		if (!is_null($this->arg2))
		{
			$this->arg2 = ELEvaluator::evaluate('value', $this->arg2, 'string', $this->pageContext);
		}

		if (!is_null($this->arg3))
		{
			$this->arg3 = ELEvaluator::evaluate('value', $this->arg3, 'string', $this->pageContext);
		}

		if (!is_null($this->arg4))
		{
			$this->arg4 = ELEvaluator::evaluate('value', $this->arg4, 'string', $this->pageContext);
		}
	}
	
	function doEndTag()
	{
		// TODO: do we want to extend BodyTagSupport and allow 'value' to
		// come from the body text?
		$this->evaluateExpressions();

		echo TagUtils::message(
			$this->pageContext,
			$this->bundle,
			$this->localeKey,
			$this->key,
			array(
				$this->arg0,
				$this->arg1,
				$this->arg2,
				$this->arg3,
				$this->arg4
			)
		);
		
		return c('Tag::EVAL_PAGE');
	}
	
	
	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
