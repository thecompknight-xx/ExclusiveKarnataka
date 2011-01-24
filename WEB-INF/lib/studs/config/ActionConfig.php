<?php
/* $Id: ActionConfig.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.beanutils.ConvertUtils');

/**
 * Object representing the configuration information of an
 * <kbd><action/></kbd> element from a Studs configuration
 * file.
 *
 * IMPLEMENTATION NOTES: include/forward have been merged to forward, no exceptions are caught yet,
 * the prefix/suffix have been eliminated for now
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.config
 */
class ActionConfig extends Object
{
    /**
     * Has configuration of this component been completed?
	 * @access protected
	 * @var boolean
     */
	var $configured = false;

    /**
     * The set of local forward configurations for this action, if any,
     * keyed by the name of the forward
	 * @access protected
	 * @var array
     */
	var $forwards = array();

	/**
	 * The set of local exception configurations for this action, if any,
	 * keyed by the type of the exception
	 * @access protected
	 * @var array
	 */
	 var $exceptions = array();

    /**
     * The module configuration with which we are associated.
	 * @access protected
	 * @var ModuleConfig
     */
	var $moduleConfig = null;

    /**
     * The request-scope or session-scope attribute name under which our
     * form bean is accessed, if it is different from the form bean's
     * specified <var>name</var>.
	 * @access protected
	 * @var string
     */
	var $attribute = null;

    /**
     * Context-relative path of the web application resource that will process
     * this request via {@link RequestDispatcher.doForward()}, instead of instantiating
     * and calling the {@link Action} class specified by "type".
	 * Exactly one of <kbd>forward</kbd>, <kbd>include</kbd> or
	 * <kbd>type</kbd> must be specified.
	 * @access protected
	 * @var string
     */
	var $forward = null;

    /**
     * Context-relative path of the web application resource that will process
     * this request via {@link RequestDispatcher::doInclude()}, instead of instantiating
     * and calling the {@link Action} class specified by "type".
	 * Exactly one of <kbd>forward</kbd>, <kbd>include</kbd> or
	 * <kbd>type</kbd> must be specified.
	 * @access protected
	 * @var string
     */
	var $include = null;

    /**
     * Context-relative path of the input form to which control should be
     * returned if a validation error is encountered.  Required if "name"
     * is specified and the input bean returns validation errors.
	 * @access protected
	 * @var string
     */
	var $input = null;

    /**
     * Name of the form bean, if any, associated with this Action.
	 * @access protected
	 * @var string
     */
	var $name = null;

    /**
     * General purpose configuration parameter that can be used to pass
     * extra information to the Action instance selected by this Action.
	 * @access protected
	 * @var string
     */
	var $parameter = null;

    /**
     * Context-relative path of the submitted request, starting with a
     * slash ("/") character, and omitting any filename extension if
     * extension mapping is being used.
     */
	var $path = null;

    /**
     * Identifier of the scope ("request" or "session") within which
     * our form bean is accessed, if any.
	 * @access protected
	 * @var string
     */
	var $scope = 'session';

    /**
	 * Fully qualified class name of the {@link Action} class to be used to
	 * process requests for this mapping if the <kbd>forward</kbd> and
	 * <kbd>include</kbd> properties are not set.
	 * @access protected
	 * @var string
     */
	var $type = null;

    /**
     * Should this Action be configured as the default one for this
     * application?
	 * @access protected
	 * @var boolean
     */
	var $unknown = false;

    /**
     * Should the {@link validate()} method of the form bean associated
     * with this action be called?
	 * @access protected
	 * @var boolean
     */
	var $validate = true;

	function getModuleConfig()
	{
		return $this->moduleConfig;
	}

	function setModuleConfig(&$moduleConfig)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}
	
		$this->moduleConfig =& $moduleConfig;
	}

	function getAttribute()
	{
		if (is_null($this->attribute))
		{
			return $this->name;
		}
		else
		{
			return $this->attribute;
		}
	}

	function setAttribute($attribute)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->attribute = $attribute;
	}

	function getForward()
	{
		return $this->forward;
	}

	function setForward($forward)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->forward = $forward;
	}

	function setInclude($include)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->include = $include;
	}

	function getInclude()
	{
		return $this->include;
	}

	function getInput()
	{
		return $this->input;
	}

	function setInput($input)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->input = $input;
	}

	function getName()
	{
		return $this->name;
	}

	function setName($name)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->name = $name;
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

	function getPath()
	{
		return $this->path;
	}

	function setPath($path)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->path = $path;
	}

	function getScope()
	{
		return $this->scope;
	}

	function setScope($scope)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->scope = $scope;
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

	function isUnknown()
	{
		return $this->unknown;
	}

	function setUnknown($unknown)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->unknown = ConvertUtils::convert($unknown, 'boolean');
	}

	function isValidate()
	{
		return $this->validate;
	}

	function setValidate($validate)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->validate = ConvertUtils::convert($validate, 'boolean');
	}

	function addExceptionConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->exceptions[$config->getType()] =& $config;
	}

	function addForwardConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		$this->forwards[$config->getName()] =& $config;
	}

	function &findExceptionConfig($type)
	{
		if (!isset($this->exceptions[$type]))
		{
			return ref(null);
		}

		return $this->exceptions[$type];
	}

	function findExceptionConfigs()
	{
		return array_values($this->exceptions);
	}

	function &findForwardConfig($name)
	{
		if (!isset($this->forwards[$name]))
		{
			return ref(null);
		}

		return $this->forwards[$name];
	}

	function findForwardConfigs()
	{
		return array_values($this->forwards);
	}

	function removeExceptionConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		unset($this->exceptions[$config->getType()]);
	}

	function removeForwardConfig(&$config)
	{
		if ($this->configured)
		{
			throw_exception(new IllegalStateException('Configuration is frozen')); return;
		}

		unset($this->forwards[$config->getName()]);
	}

	function freeze()
	{
		$this->configured = true;

		$exceptionConfigs = $this->findExceptionConfigs();
		for ($i = 0; $i < count($exceptionConfigs); $i++)
		{
			$exceptionConfigs[$i]->freeze();
		}

		$forwardConfigs = $this->findForwardConfigs();
		for ($i = 0; $i < count($forwardConfigs); $i++)
		{
			$forwardConfigs[$i]->freeze();
		}
	}
}
?>
