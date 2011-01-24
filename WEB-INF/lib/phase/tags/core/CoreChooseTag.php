<?php
/* $Id: CoreChooseTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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
import('phase.PhaseException');

/**
 * @package phase.tags.core
 * @access public
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class CoreChooseTag extends TagSupport
{
	/**
	 * can be 'opened', 'closed' or 'initial' where the default is 'initial'
	 * @var string
	 */
	var $subtagGateStatus = null;

	var $satisfied;

	var $whenCondition;

	var $otherwiseCondition;

	function CoreChooseTag()
	{
		$this->init();
	}

	function init()
	{
		$this->subtagGateStatus = 'initial';
		$this->satisfied = false;
		$this->containsWhen = false;
		$this->containsOtherwise = false;
	}

	/**
	 * This method will determine where we are in the conditional statement, update
	 * that position and return the conditional operator to be used.
	 *
	 * @param string $type The short name of the tag being evaluated
	 *
	 * @return string The conditional operator to be used
	 * @throws IllegalStateException
	 */
	function acquireExclusivity($type = 'otherwise')
	{
		if ($type == 'when')
		{
			if ($this->subtagGateStatus == 'initial')
			{
				$this->subtagGateStatus = 'opened';
			}
			else if ($this->subtagGateStatus == 'closed')
			{
				throw_exception(new IllegalStateException('Misplaced "when"-style conditional tag inside of the "choose" tag'));
				return;
			}
		}
		else
		{
			if ($this->subtagGateStatus != 'opened')
			{
				throw_exception(new IllegalStateException('Misplaced "otherwise"-style conditional tag inside of the "choose" tag')); return;
			}

			$this->subtagGateStatus = 'closed';
		}
	}

	/**
	 * Check to see if the condition in this choose group has been previously satisfied
	 */
	function conditionSatisfied()
	{
		return $this->satisfied;
	}

	/**
	 * Assign the condition as being satisified for this group
	 */
	function markConditionSatisfied()
	{
		$this->satisfied = true;
	}

	// potentially make this tag a boolean argument which will tell
	// if it passed the condition...that way we don't need markConditionSatisfied
	function markWhenConditionUsed()
	{
		$this->whenCondition = true;
	}

	function markOtherwiseConditionUsed()
	{
		$this->otherwiseCondition = true;
	}

	function whenConditionUsed()
	{
		return $this->whenCondition;
	}

	function otherwiseConditionUsed()
	{
		return $this->otherwiseCondition;
	}

	function doStartTag()
	{
		return c('Tag::EVAL_BODY_INCLUDE');
	}

	function doEndTag()
	{
		if (!$this->whenConditionUsed())
		{
			throw_exception(new PhaseException('Illegal "choose" tag without child "when" tag'));
		}
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
