<?php
/* $Id: ApplicationDispatcher.php 370 2006-10-17 05:19:38Z mojavelinux $
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
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package stratus.core
 * @access public
 * @todo the whole "wrap request/response" thing I am not getting, see if we can make sense of it
 */
class ApplicationDispatcher extends Object /* implements RequestDispatcher */
{
	var $wrapper = null;

	var $context = null;

	var $servletPath = null;

	var $pathInfo = null;

	var $queryString = null;

	var $name = null;

	/**
	 * Are we performing an include rather than a forward?
	 * @var boolean
	 */
	var $including = false;

	// @todo: I am really not sure how to prevent this from being
	// reinitialized each time
	var $pageContext = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('stratus.core.ApplicationDispatcher');
		return $logger;
	}

	function ApplicationDispatcher(&$wrapper, $servletPath, $pathInfo, $queryString, $name)
	{
		$log =& ApplicationDispatcher::getLog();

		$this->wrapper =& $wrapper;
		$this->context =& $this->wrapper->getParent();
		$this->servletPath = $servletPath;
		$this->pathInfo = $pathInfo;
		$this->queryString = $queryString;
		$this->name = $name;

		if ($log->isLoggable('DEBUG'))
		{
			$log->debug(
				'[' . $this->context->getPath() . '] servletPath=' . $this->servletPath . ';' .
				'pathInfo=' . $this->pathInfo . ';' . 'queryString=' . $this->queryString . ';' . 'name=' . $this->name
			);
		}
	}

	/**
	 * @return void
	 */
	function doInclude(&$request, &$response)
	{
		$log =& ApplicationDispatcher::getLog();

		$this->including = true;
		// @todo: when we include we really want wrappers around copies
		// of the current request/response so that certain features are
		// either disabled or intercepted.  Such examples include disabling
		// setHeader() in the response on an include
		$wrequest = $request->makeCopy();
		$wresponse = $response->makeCopy();
		$requestUri = '';
		$contextPath = $this->context->getPath();

		// @note how can context path be null?
		if (!is_null($contextPath))
		{
			$requestUri .= $request->generateControllerPath($contextPath);
			$wrequest->setAttribute(c('ServletConstants::INC_CONTEXT_PATH_KEY'), $contextPath);
		}

		if (!is_null($this->servletPath))
		{
			$requestUri .= $this->servletPath;
			$wrequest->setAttribute(c('ServletConstants::INC_SERVLET_PATH_KEY'), $this->servletPath);
		}

		if (!is_null($this->pathInfo))
		{
			$requestUri .= $this->pathInfo;
			$wrequest->setAttribute(c('ServletConstants::INC_PATH_INFO_KEY'), $this->pathInfo);
		}

		if (!is_null($this->queryString))
		{
			$wrequest->setAttribute(c('ServletConstants::INC_QUERY_STRING_KEY'), $this->queryString);
			// @todo: merge parameters?
			// $wrequest->mergeParameters($this->queryString);
		}

		if (strlen($requestUri) > 0)
		{
			$wrequest->setAttribute(c('ServletConstants::INC_REQUEST_URI_KEY'), $requestUri);
		}

		// TODO: not currently handling the named include

		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('[' . $this->context->getPath() . '] Path Based Include');
		}

		$this->invoke($wrequest, $wresponse);
	}

	/**
	 * @return void
	 */
	function doForward(&$request, &$response)
	{
		$log =& ApplicationDispatcher::getLog();

		// make sure we are not committed and reset output
		if ($response->isCommitted())
		{
			throw_exception(new IllegalStateException('Cannot forward after response has been submitted'));
		}

		$response->resetBuffer();
		$this->including = false;

		// named dispatcher forward
		if (is_null($this->servletPath) && is_null($this->pathInfo))
		{
			if ($log->isLoggable('DEBUG'))
			{
				$log->debug('[' . $this->context->getPath() . '] Named Dispatcher Forward');
			}

			$this->invoke($request, $response);	
		}
		else
		{
			if ($log->isLoggable('DEBUG'))
			{
				$log->debug('[' . $this->context->getPath() . '] Path Based Forward');
			}

			$wrequest = $request->makeCopy();
			$contextPath = $this->context->getPath();
			$wrequest->setContextPath($contextPath);
			$wrequest->setRequestURI($contextPath . $this->servletPath . $this->pathInfo);
			$wrequest->setServletPath($this->servletPath);
			$wrequest->setPathInfo($this->pathInfo);
			if (!is_null($this->queryString))
			{
				$wrequest->setQueryString($this->queryString);
				// @todo $wrequest->mergeParameters($this->queryString);
			}

			$this->invoke($wrequest, $response);
		}

		// at this point set the response to be suspended since a forward is final
		$response->setSuspended(true);
	}

	function invoke(&$request, &$response)
	{
		$log =& ApplicationDispatcher::getLog();
		$servlet =& $this->wrapper->allocate();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('[' . $this->context->getPath() . '] Servicing ' . ($this->including ? 'include' : 'forward') . ' request for servlet matching ' . $this->servletPath);
		}

		$servlet->service($request, $response);
	}
}
?>
