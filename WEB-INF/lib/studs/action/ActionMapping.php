<?php
/* $Id: ActionMapping.php 352 2006-05-15 04:27:35Z mojavelinux $
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

import('studs.action.ActionForward');
import('studs.config.ActionConfig');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.action
 * @access public
 */
class ActionMapping extends ActionConfig
{
	/**
	 * @return ExceptionConfig
	 */
	function &findException(&$type)
	{
		$config = null;
		while (true)
		{
			$name = $type->getName();
			$config =& $this->findExceptionConfig($name);
			if (!is_null($config))
			{
				return $config;
			}

			$config =& $this->moduleConfig->findExceptionConfig($name);
			if (!is_null($config))
			{
				return $config;
			}

			$type =& $type->getSuperClass();
			if (is_null($type))
			{
				break;
			}
		}

		return ref(null);
	}

	function &findForward($name)
	{
		$forwardConfig =& $this->findForwardConfig($name);
		if (is_null($forwardConfig))
		{
			$forwardConfig =& $this->moduleConfig->findForwardConfig($name);	
		}

		return $forwardConfig;
	}

	function findForwards()
	{
		$results = array();
		$forwardConfigs = $this->findForwardConfigs();
		for ($i = 0; $i < count($forwardConfigs); $i++)
		{
			$results[] = $forwardConfigs[$i]->getName();
		}

		return $results;
	}

	function &getInputForward()
	{
		$controllerConfig =& $this->moduleConfig->getControllerConfig();
		if ($controllerConfig->isInputForward())
		{
			return $this->findForward($this->getInput());
		}
		else
		{
			$af =& new ActionForward($this->getInput());
			return $af;
		}
	}
}
?>
