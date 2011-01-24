<?php
/* $Id: DataSourceConfig.php 188 2005-04-07 04:52:31Z mojavelinux $
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
 * @package studs.config
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class DataSourceConfig extends Object
{
	var $configured = false;

	var $properties = array();

	var $type = null;

	var $key = null;

	function DataSourceConfig()
	{
		$this->key = c('StudsConstants::DATA_SOURCE_KEY');
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

	function getType()
	{
		return $this->type;
	}

	function setType($type)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->type = $type;
	}

	function &getProperties()
	{
		return $this->properties;
	}

	function addProperty($name, $value)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->properties[$name] = $value;
	}

	/**
	 * Freeze the configuration of this data source.
	 * @return void
	 */
	function freeze()
	{
		$this->configured = true;
	}
}
?>
