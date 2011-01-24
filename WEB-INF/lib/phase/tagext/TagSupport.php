<?php
/* $Id: TagSupport.php 218 2005-06-21 22:29:30Z mojavelinux $
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

// TODO: these constants actually go in the interface, but we aren't including interfaces
// until we are at PHP5, so push these up at that time
def('Tag::SKIP_BODY', 0);
def('Tag::EVAL_BODY_INCLUDE', 1);
def('Tag::EVAL_BODY_BUFFERED', 2);
def('Tag::EVAL_BODY_AGAIN', 3);
def('Tag::SKIP_PAGE', 5);
def('Tag::EVAL_PAGE', 6);

/**
 * The root of all taglib implementations.
 *
 * NOTE: Each time a tag is used, a new instance of taglib is created, so all
 * values are set to their instantiation state when used.  This may change
 * later.
 *
 * @package phase.tagext
 * @abstract
 * @author Dan Allen
 */
class TagSupport extends Object // implements Tag
{
	var $pageContext;

	/**
	 * @var Tag
	 */
	var $parent;

	function setParent(&$parent)
	{
		$this->parent =& $parent;
	}

	/**
	 * The tag instance most closely enclosing this tag instance.
	 */
	function &getParent()
	{
		return $this->parent;
	}

	function doStartTag()
	{
		return c('Tag::SKIP_BODY');
	}

	function doEndTag()
	{
		return c('Tag::EVAL_PAGE');
	}

	function doAfterBody()
	{
		return c('Tag::SKIP_BODY');
	}

	function release()
	{
		unset($this->parent);
	}

	function setPageContext(&$pageContext)
	{
		$this->pageContext =& $pageContext;
	}
}
?>
