<?php
/* $Id: LoopTagStatus.php 188 2005-04-07 04:52:31Z mojavelinux $
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

/**
 * Exposes the current status of an iteration.  Properties can be tested for
 * location within the loop.
 *
 * @package phase.tagext
 * @author Dan Allen
 */
class LoopTagStatus
{
	var $current = null;

	var $idx = 0;

	var $cnt = 1;

	var $last = false;

	/*
	var $begin;

	var $end;

	var $step;
	*/

	function LoopTagStatus(&$current, $idx, $cnt, $last, $begin = null, $end = null, $step = null)
	{
		$this->current =& $current;
		$this->idx = $idx;
		$this->cnt = $cnt;
		$this->last = $last;
		/*
		$this->begin = $begin;
		$this->end = $end;
		$this->step = $step;
		*/
	}

	function &getCurrent()
	{
		return $this->current;
	}

	function getIndex()
	{
		return $this->idx;
	}

	function getCount()
	{
		return $this->cnt;
	}

	function isFirst()
	{
		return ($this->idx == 0);
	}

	function isLast()
	{
		return $this->last;
	}

	/*
	// only if specified
	function getBegin()
	{
		return $this->begin;
	}

	// only if specified
	function getEnd()
	{
		return $this->end;
	}

	// only if specified
	function getStep()
	{
		return $this->step;
	}
	*/
}
?>
