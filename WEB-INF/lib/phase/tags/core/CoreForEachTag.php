<?php
/* $Id: CoreForEachTag.php 321 2006-03-11 05:07:42Z mojavelinux $
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
import('phase.tagext.LoopTagStatus');
import('phase.support.ELEvaluator');
import('horizon.collections.IteratorUtils');

/**
 * @package phase.tags.core
 * @access public
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class CoreForEachTag extends TagSupport
{
	/**
	 * Holds the collection resolved from expression.
	 */
	var $items;

	/**
	 * Holds the collection expression.
	 */
	var $itemsExpression;

	/**
	 * The attribute name under which to store the current item.
	 */
	var $var;

	/**
	 * The scope to use when setting attributes.
	 */
	var $scope;

	/**
	 * The attribute name under which to store the LoopTagStatus
	 * for the current iteration.
	 */
	var $varStatus;

	/**
	 * The active iterator for the collection.
	 */
	var $iterator;

	/**
	 * The index of the current item.
	 */
	var $index;

	/**
	 * A 1-based index that is incremented on each iteration
	 */
	var $count;

	var $begin;

	var $end;

	var $step;

	function CoreForEachTag()
	{
		$this->init();
	}

	function setItems($items)
	{
		$this->itemsExpression = $items;
	}

	function setVar($var)
	{
		$this->var = $var;
	}

	function setScope($scope)
	{
		$this->scope = $scope;
	}

	function setBegin($begin)
	{
		$this->begin = $begin;
	}

	function setEnd($end)
	{
		$this->end = $end;
	}

	function setStep($step)
	{
		$this->step = $step;
	}

	function setVarStatus($varStatus)
	{
		$this->varStatus = $varStatus;
	}

	function init()
	{
		unset($this->items);
		$this->items = null;
		$this->itemsExpression = null;
		$this->var = null;
		$this->scope = 'page';
		$this->varStatus = null;
		$this->iterator = null;
		unset($this->item);
		$this->item = null;
		$this->index = 0;
		$this->count = 1;
		$this->last = false;
		$this->begin = null;
		$this->end = null;
		$this->step = null;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();

		if (!is_null($this->items))
		{
			if (is_scalar($this->items))
			{
				$this->items = array($this->items);
			}

			$this->iterator =& IteratorUtils::getIterator($this->items);
		}
		elseif (!is_null($this->begin) && !is_null($this->end))
		{
			$range = range($this->begin, $this->end);
			$this->iterator =& IteratorUtils::getIterator($range);
		}

		if (is_null($this->iterator) || !$this->iterator->hasNext())
		{
			$this->iterator = null;
			return parent::doStartTag();	
		}

		// NOTE: only discard if we have items, not for ranges
		if (!is_null($this->begin) && !is_null($this->items))
		{
			$this->discard($this->begin);
		}

		$this->exposeVariables();
		return c('Tag::EVAL_BODY_INCLUDE');	
	}

	function doAfterBody()
	{
		if (!is_null($this->step))
		{
			$this->discard($this->step - 1);
		}

		if (!is_null($this->iterator) && $this->iterator->hasNext()
			&& (is_null($this->end) || $this->index <= $this->end))
		{
			$this->exposeVariables();
			return c('Tag::EVAL_BODY_AGAIN');
		}

		return parent::doAfterBody();
	}

	function doEndTag()
	{
		if (!is_null($this->iterator))
		{
			$this->unexposeVariables();
		}

		return parent::doEndTag();
	}

	function evaluateExpressions()
	{
		if (!is_null($this->itemsExpression))
		{
			$this->items =& ELEvaluator::evaluate('items', $this->itemsExpression, 'object', $this->pageContext);
		}
	}

	/**
	 * Place the current item into the specified scope as well as the loop
	 * status for this iterator if appropriate variable names have been
	 * provided.
	 *
	 * @param boolean $first Specifies if this is the first iteration
	 */
	function exposeVariables()
	{
		$item =& $this->iterator->next();

		if (!is_null($this->var))
		{
			if (is_null($item))
			{
				$this->pageContext->removeAttribute($this->var, $this->scope);
			}
			else
			{
				$this->pageContext->setAttribute($this->var, $item, $this->scope);
			}
		}
		
		// we asked for a var status, let's tuck it away in the scope
		if (!is_null($this->varStatus))
		{
			$this->pageContext->setAttribute($this->varStatus, new LoopTagStatus($item, $this->index++, $this->count++, !$this->iterator->hasNext()), $this->scope);
		}
	}

	function unexposeVariables()
	{
		if (!is_null($this->var))
		{
			$this->pageContext->removeAttribute($this->var);
		}

		if (!is_null($this->varStatus))
		{
			$this->pageContext->removeAttribute($this->varStatus);
		}
	}

	function discard($begin, $updateIndex = true)
	{
		while ($begin-- > 0 && $this->iterator->hasNext())
		{
			$this->iterator->next();
			if ($updateIndex)
			{
				$this->index++;
			}
		}
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
