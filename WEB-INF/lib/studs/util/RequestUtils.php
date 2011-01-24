<?php
/* $Id: RequestUtils.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.beanutils.BeanUtils');
import('studs.StudsConstants');

/**
 * General purpose utility methods related to processing a servlet request
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.util
 * @access public
 */
class RequestUtils extends Object
{
	function &applicationInstance($className)
	{
		$clazz =& Clazz::forName($className);
		$instance =& $clazz->newInstance();
		return $instance;
	}

	function forwardURL(&$request, &$forward, &$moduleConfig)
	{
		$path = $forward->getPath();
		
		if (is_null($moduleConfig))
		{
			// @todo this should really dispatch to ModuleUtils
			$moduleConfig =& $request->getAttribute(c('StudsConstants::MODULE_KEY'));
		}

		$controllerConfig =& $moduleConfig->getControllerConfig();
		$forwardPattern = $controllerConfig->getForwardPattern();
		
		// if should be treated as-is, relative to context root, then make sure it is absolute
		// and just let it go through
		if ($forward->isContextRelative() || is_null($forwardPattern))
		{
			if ($path[0] != '/')
			{
				$path = '/' . $path;
			}

			return $path;
		}

		// use the non-null replacement pattern
		$fullPath = '';		
		$dollar = false;	
		for ($i = 0; $i < strlen($forwardPattern); $i++)
		{
			$ch = $forwardPattern[$i];
			if ($dollar)
			{
				switch ($ch)
				{
					case 'M':
						$fullPath .= $moduleConfig->getPrefix();
					break;

					case 'P':
						$fullPath .= $path;
					break;

					case '$':
						$fullPath .= '$';

					default:
						; // silently swallow
				}

				$dollar = false;
			}
			elseif ($ch == '$')
			{
				$dollar = true;
			}
			else
			{
				$fullPath .= $ch;
			}
		}

		return $fullPath;
	}

	function &createActionForm(&$request, &$mapping, &$moduleConfig, &$servlet)
	{
		$instance = null;
		$attribute = $mapping->getAttribute();
		if (is_null($attribute))
		{
			return $instance;
		}

		$name = $mapping->getName();
		if (is_null($name))
		{
			return $instance;
		}

		$config =& $moduleConfig->findFormBeanConfig($name);
		if (is_null($config))
		{
			throw_exception(new PhaseException("Invalid form-bean in action mapping: $name"));
			return $instance;
		}

		if ($mapping->getScope() == 'request')
		{
			$instance =& $request->getAttribute($attribute);
		}
		else
		{
			$session =& $request->getSession();
			$instance =& $session->getAttribute($attribute);
		}

		if (!is_null($instance))
		{
			return $instance;
		}

		$instance =& RequestUtils::applicationInstance($config->getType());
		$instance->setServlet($servlet);
		return $instance;
	}

	/**
	 * Look up and return current user locale, based on the specified parameters.
	 * Note that this method does not create a session if it doesn't already exist
	 *
	 * @param HttpServletRequest $request
	 * @param string $localeKey
	 * @return string
	 */
	function getUserLocale(&$request, $localeKey = null)
	{
		$locale = null;	
		$session =& $request->getSession(false);
		if (is_null($localeKey))
		{
			$localeKey = c('StudsConstants::LOCALE_KEY');
		}

		if (!is_null($session))
		{
			$locale = $session->getAttribute($localeKey);
		}
		
		if (is_null($locale))
		{
			$locale = $request->getLocale();
		}

		return $locale;
	}

	function populate(&$bean, &$request)
	{
		$properties = array();
		$names = $request->getParameterNames();

		foreach ($names as $name)
		{
			$values =& $request->getParameterValues($name);
			if (is_null($values) || count($values) == 1)
			{
				// preserve indexed key value
				if (key($values) > 0)
				{
					$properties[$name] =& $values;
				}
				else
				{
					$properties[$name] =& $values[0];
				}
			}
			// NOTE: the only issue here is if we were not
			// expecting multiple values and we are given them
			else
			{
				$properties[$name] =& $values;
			}
		}

		BeanUtils::populate($bean, $properties);
	}
}
?>
