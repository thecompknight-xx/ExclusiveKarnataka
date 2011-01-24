<?php
/* $Id: TagHandlerPool.php 370 2006-10-17 05:19:38Z mojavelinux $
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
 * Since a page will use a number of tags, often with the same tag class,
 * it makes sense to minimize the amount of work done to obtain an instance
 * of that tag.  In PHP it takes virtually no time to instantiate a class,
 * so it really makes no sense to keep instances around.  We really save the
 * time looking for a class by name, importing it and creating a Clazz instance
 * cooresponding to that class name.
 *
 * I really question the usefulness of the release() method when used by the
 * jakarta tag pool implementation since you don't know when it will be executed.
 *
 * This tag pool basically just keeps around the Clazz object associated with
 * a tag so that it can quickly fire off a new instance.  When the instance is
 * returned, it calles its release() method and throws it away.  Using this pool,
 * however, allows us to change the functionality in the future.
 *
 * @package phase.runtime
 * @author Dan Allen
 */
class TagHandlerPool
{
	var $handlers;

	function TagHandlerPool()
	{
		$this->handlers = array();
	}

	/**
	 * For now we will use a singleton to get a handle on the tag handler pool.  Really
	 * we should keep an instance of the tag handler pool in the generated page to use.
	 */
	function &getInstance()
	{
		static $instance;

		if (is_null($instance))
		{
			$instance = new TagHandlerPool();
		}

		return $instance;
	}

	/**
	 * @param String $className the fully qualified name of the Tag class
	 *
	 * @note we don't need a reference here, we just want to make a very fast copy
	 */
	function &borrowTag($className)
	{
		if (isset($this->handlers[$className]))
		{
			$clazz =& $this->handlers[$className];
		}
		else
		{
			$clazz =& Clazz::forName($className);	
			$this->handlers[$className] =& $clazz;
		}

		$return =& $clazz->newInstance();
		return $return;
	}

	function returnTag($className, &$handler)
	{
		// for now we just release the handler and throw it away
		// do we really need this if we are destroying the tag??
		$handler->release();
	}

	function release()
	{
		// do nothing
	}
}
?>
