<?php
/* $Id: Tag.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * The <b>Tag</b> interface defines the basic protocol between phase
 * tag and its implementation class.  It lays out the life cycle and the
 * methods to be invoked at the start and the end tag.
 *
 * The phase parser invokes setOutputStream and setParent before invoking the
 * tag action methods.  There are two main actions: doStartTag and doEndTag.
 * Once all appropriate properties have been initialized, the doStartTag and
 * doEndTag methods can be invoked on the tag handler.  Between the start and
 * end tags, the tag handler is assumed to hold a state that must be preserved.
 *
 * @package phase.tagext
 * @abstract
 * @author Dan Allen
 * @version $Id: Tag.php 188 2005-04-07 04:52:31Z mojavelinux $
 */
class Tag
{
	/**
	 * Set the current page context.
	 * This method is invoked by the compiled phase page prior to doStartTag()
	 *
	 * @param PageContext $pc The page context present at the time of tag execution
	 */
	function setPageContext(&$pageContext) {}

	/**
	 * Set the parent (closest enclosing tag handler) of this
	 * tag handler.
	 *
	 * @param Tag $t The parent tag, or <kbd>null</kbd> if a top level tag
	 */
	function setParent(&$t)	{}

	/**
	 * Get the parent, which is the closest enclosing tag handler
	 *
	 * @return Tag the current parent, or null if top level
	 */
	function &getParent() {}

	function doStartTag() {}

	function doEndTag() {}

	function doAfterBody() {}

	function release() {}
}
?>
