<?php
/* $Id: ModuleUtils.php 351 2006-05-15 04:01:19Z mojavelinux $
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

import('studs.StudsConstants');

/**
 * General purpose utility methods related to module processing.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.util
 * @access public
 */
class ModuleUtils extends Object
{
	/**
	 * Return the current ModuleConfig object stored in request, if it exists,
	 * null otherwise.
	 *
	 * @param HttpServletRequest $request
	 * @return ModuleConfig
	 */
	function &getModuleConfig(&$request, &$servletContext)
	{
		$config =& $request->getAttribute(c('StudsConstants::MODULE_KEY'));

		// grab the default module if this lookup fails and we pass in a ServletContext
		if (is_null($config) && !is_null($servletContext))
		{
			$config =& $servletContext->getAttribute(c('StudsConstants::MODULE_KEY'));
		}

		return $config;
	}

	/**
	 * Get the module name (or prefix) to which the specified URI belongs.
	 *
	 * @access public
	 *
	 * @param HttpServletRequest $request
	 * @param ServletContext $servletContext
	 *
	 * @return string The module prefix or '' for the default module
	 */
	function getModuleName(&$request, &$servletContext)
	{
		$prefix = '';
		$prefixes = ModuleUtils::getModulePrefixes($servletContext);
		// handle the case of only default module
		if (count($prefixes) == 0)
		{
			return $prefix;
		}

		$matchPath = $request->getServletPath();

		$lastSlash = 0;
		while ($prefix == '' && ($lastSlash = strrpos($matchPath, '/')) !== false)
		{
			$matchPath = substr($matchPath, 0, $lastSlash);
			
			// match against the list of module prefixes
			if (in_array($matchPath, $prefixes))
			{
				$prefix = $matchPath;
			}
		}

		return $prefix;
	}

	/**
	 * Return the list of module prefixes that are defined for this web
	 * application.
	 *
	 * @param ServletContext $servletContext
	 * @return array
	 */
	function getModulePrefixes(&$servletContext)
	{
		return $servletContext->getAttribute(c('StudsConstants::MODULE_PREFIXES_KEY'));
	}

	/**
	 * Select the module to which the specified request belongs, and add
	 * corresponding request attributes to this request.
	 *
	 * @access public
	 * @return void
	 */
	function selectModule(&$request, &$servletContext, $prefix = null)
	{
		if (is_null($prefix))
		{
			$prefix = ModuleUtils::getModuleName($request, $servletContext);
		}

		// expose the resources for this module
		$config =& $servletContext->getAttribute(c('StudsConstants::MODULE_KEY') . $prefix);	

		if (!is_null($config))
		{
			$request->setAttribute(c('StudsConstants::MODULE_KEY'), $config);
		}
		else
		{
			$request->removeAttribute(c('StudsConstants::MODULE_KEY'));
		}

		$resources =& $servletContext->getAttribute(c('StudsConstants::MESSAGE_RESOURCES_KEY') . $prefix);
		if (!is_null($resources))
		{
			$request->setAttribute(c('StudsConstants::MESSAGE_RESOURCES_KEY'), $resources);
		}
		else
		{
			$request->removeAttribute(c('StudsConstants::MESSAGE_RESOURCES_KEY'));
		}
	}
}
