<?php
/* $Id: ManagerServlet.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package stratus.servlets
 * @author Dan Allen
 */
class ManagerServlet extends HttpServlet
{
	/**
	 * The wrapper container associated with this servlet.
	 */
	var $wrapper = null;

	/**
	 * The context associated with this web application.
	 */
	var $context = null;

	function &getWrapper()
	{
		return $this->wrapper;
	}

	function setWrapper(&$wrapper)
	{
		$this->wrapper =& $wrapper;
		if (is_null($wrapper))
		{
			$this->context = null;
		}
		else
		{
			$this->context =& $wrapper->getParent();
		}
	}

	function doGet(&$request, &$response)
	{
		$command = $request->getPathInfo();
		if ($command == '/list')
		{
			$this->listEntries();
		}
	}

	function listEntries()
	{
		echo $this->context->getPath();
	}
}
?>
