<?php
/* $Id: MessageResourcesConfig.php 188 2005-04-07 04:52:31Z mojavelinux $
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
import('horizon.beanutils.ConvertUtils');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.config
 */
class MessageResourcesConfig
{
	var $configured = false;

	var $factory = null;

	var $key = null;

	var $nullValue = true;

	var $parameter = null;

	function MessageResourcesConfig()
	{
		$this->factory = 'studs.util.PropertyMessageResources';
		$this->key = c('StudsConstants::MESSAGE_RESOURCES_KEY');
	}

	function getFactory()
	{
		return $this->factory;
	}

	function setFactory($factory)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->factory = $factory;
	}

	function getKey()
	{
		return $this->key;
	}

	function setKey($key)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->key = $key;
	}

	function getNull()
	{
		return $this->nullValue;
	}
	
	function setNull($nullValue)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->nullValue = ConvertUtils::convert($nullValue, 'boolean');
	}

	function getParameter()
	{
		return $this->parameter;
	}

	function setParameter($parameter)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->parameter = $parameter;
	}

	function freeze()
	{
		$this->configured = true;
	}
}
?>
