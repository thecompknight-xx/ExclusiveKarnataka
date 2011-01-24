<?php
/* $Id: InvokerServlet.php 373 2006-10-17 05:27:54Z mojavelinux $
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

import('stratus.core.StandardWrapper');
import('stratus.servlets.HttpServlet');

/**
 * @package stratus.servlets
 * @author Dan Allen
 *
 * TODO: still need to save the servlet so that we don't have to keep looking it up
 */
class InvokerServlet extends HttpServlet
{
	var $wrapper = null;

	var $context = null;

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

	function &getWrapper()
	{
		return $this->wrapper;
	}

	function destroy()
	{
		; // no actions necessary
	}

	function init()
	{
	}

	function doGet(&$request, &$response)
	{
		$this->serveRequest($request, $response);
	}

	function doPost(&$request, &$response)
	{
		$this->serveRequest($request, $response);
	}

	function serveRequest(&$request, &$response)
	{
		// @todo determine if we are "included"
		$inRequestURI = $request->getRequestURI();
		$inServletPath = $request->getServletPath();
		$inPathInfo = $request->getPathInfo();
		
		// identify the outgoing servlet name or class and outgoing path info
		$pathInfo = $inPathInfo;
		$servletClass = substr($pathInfo, 1);
		$slash = strpos($servletClass, '/');
		if ($slash !== false)
		{
			$pathInfo = substr($servletClass, $slash);
			$servletClass = substr($servletClass, 0, $slash);
		}
		else
		{
			$pathInfo = '';	
		}

		// @todo cache the pattern so it is caught next time and serviced

		// @todo lookup the cache and if not yet looked up, create and save it
		$wrapper =& new StandardWrapper();
		$wrapper->setServletClass($servletClass);
		$this->context->addChild($wrapper);

		// make a copy of the request
		$wrequest = $request->makeCopy();
		$wrequest->setRequestURI($inRequestURI);
		$wrequest->setServletPath($inServletPath . '/' . $servletClass);
		if (strlen($pathInfo) == 0)
		{
			$wrequest->setPathInfo(null);
			// @todo $wrequest->setPathTranslated(null);
		}
		else
		{
			$wrequest->setPathInfo($pathInfo);
			$servletContext =& $this->getServletContext();
			// @todo $wrequest->setPathTranslated($servletContext->getRealPath($pathInfo));
		}

		$instance =& $wrapper->allocate();
		$instance->service($wrequest, $response);	
		// @todo deallocate and clean up
	}
}
?>
