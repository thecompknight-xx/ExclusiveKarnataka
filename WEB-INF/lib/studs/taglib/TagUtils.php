<?php
/* $Id: TagUtils.php 260 2005-07-10 04:47:51Z mojavelinux $
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
import('studs.util.RequestUtils');
import('studs.util.ModuleUtils');
import('horizon.beanutils.PropertyUtils');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package stuts.taglib
 * @access public
 */
class TagUtils extends Object
{
	function message(&$pageContext, $bundleKey, $localeKey, $key, $values)
	{
		$request =& $pageContext->getRequest();
		$resources =& $request->getAttribute($bundleKey);
		// @todo kind of a hack, we should create a local method for this
		$locale = RequestUtils::getUserLocale($pageContext->getRequest(), $localeKey);

		$message = null;
		if (is_null($values))
		{
			$message = $resources->getMessage($locale, $key);
		}
		else
		{
			$message = $resources->getMessage($locale, $key, $values);
		}

		return $message;
	}

	/**
	 * Given an action, prepend a workable servlet mapping so that it will be
	 * caught and processed by the ActionServlet.
	 *
	 * @param string $action The action URL to be processed
	 * @param PageContext $pageContext The PageContext instance associated with
	 * this page
	 *
	 * @return string A URL that will be caught by the ActionServlet and processed
	 */
	function getActionMappingURL($action, &$pageContext)
	{
		$request =& $pageContext->getRequest();

		// get the context path + the front controller script
		$value = $request->generateControllerPath($request->getContextPath());
		$config =& $request->getAttribute(c('StudsConstants::MODULE_KEY'));
		if (!is_null($config))
		{
			$value .= $config->getPrefix();
		}

		$servletContext =& $pageContext->getServletContext();
		$servletMapping = $servletContext->getAttribute(c('StudsConstants::SERVLET_MAPPING_KEY'));
		if (!is_null($servletMapping))
		{
			$queryString = null;
			$question = strpos($action, '?');
			if ($question !== false)
			{
				$queryString = substr($action, $question);
				$action = substr($action, 0, $question);
			}

			if ($action[0] != '/')
			{
				$action = '/' . $action;
			}

			$servletMapping =& new String($servletMapping);
			if ($servletMapping->startsWith('*.'))
			{
				$value .= $action . String::valueOf($servletMapping->substring(1));
			}
			elseif ($servletMapping->endsWith('/*'))
			{
				$value .= String::valueOf($servletMapping->substring(0, $servletMapping->length() - 2)) . $action;
			}
			elseif ($servletMapping->equals('/'))
			{
				$value .= '/';
			}

			if (!is_null($queryString))
			{
				$value .= $queryString;
			}
		}
		else
		{
			if ($action[0] != '/')
			{
				$action = '/' . $action;
			}

			$value .= $action;
		}

		return $value;
	}

	/**
	 * This method extracts the action mapping from a form action URL so
	 * it can be used to lookup the cooresponding ActionConfig.  It also
	 * conveniently prefixes the actionMapping with a '/' if left off for
	 * astetic purposes.
	 *
	 * @param string $action The form action URL (ex. saveUser === /saveUser.do)
	 *
	 * @return string The action mapping cooresponding to an ActionConfig
	 */
	function getActionMappingName($action)
	{
		$action = preg_replace(';\?.*;', '', $action);

		if ($action[0] != '/')
		{
			$action = '/' . $action;
		}

		return $action;
	}

	/**
	 * Return the ModuleConfig object if it exists, null if otherwise.  The PageContext
	 * is used to get to the ServletContext.
	 *
	 * @return ModuleConfig
	 */
	function &getModuleConfig(&$pageContext)
	{
		return ModuleUtils::getModuleConfig($pageContext->getRequest(), $pageContext->getServletContext());
	}

	/**
	 * Lookup the bean by name in the given scope (or across all scopes if
	 * scope is <kbd>null</kbd>) and return the value of the specified property
	 * on that bean.
	 *
	 * @return Object
	 */
	function &lookup(&$pageContext, $name, $property, $scope = null)
	{
		$bean =& $pageContext->findAttribute($name, $scope);
		// FIXME: throw exception if bean not found
		if (is_null($property))
		{
			return $bean;
		}

		// try {
		// FIXME: seems to be a problem with bean utils where top level
		// index properties are ignored!!
		if (strpos($property, '[') === false)
		{
			$value =& PropertyUtils::getProperty($bean, $property);
		}
		else
		{
			$value =& PropertyUtils::getIndexedProperty($bean, $property);
		}
		// } catch (RootException ex) {
		// FIXME: catch some exceptions here, convert to JspException
		return $value;
	}

	/**
	 * Return the default data source for the current module.
	 *
	 * @param request The HttpServletRequest we are processing
	 * @return DataSource
	 * @throws ServletException If the datasource with the specified key could not be found
	 */
	function &getDataSource(&$pageContext, $key = null)
	{
		if (is_null($key))
		{
			$key = c('StudsConstants::DATA_SOURCE_KEY');
		}

		// identify the current module
		$request =& $pageContext->getRequest();
		$servletContext =& $pageContext->getServletContext();
		$moduleConfig =& ModuleUtils::getModuleConfig($request, $servletContext);

		// return the requested data source instance
		$ds =& $servletContext->getAttribute($key . $moduleConfig->getPrefix());
		if (is_null($ds))
		{
		   throw_exception(new ServletException('The datasource with the requested key \'' . $key . '\' has not been configured'));
		   return;
		}

		return $ds;
	}
}
?>
