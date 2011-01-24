<?php
/* $Id: StudsRuleSet.php 188 2005-04-07 04:52:31Z mojavelinux $
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


import('horizon.xml.digester.Rule');
import('horizon.xml.digester.RuleSetBase');

/**
 * The set of Digester rules required to parse a Studs
 * configuration file (<i>struts-config.xml</i>).
 *
 * @package studs.config
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @author Craig R. McClanahan
 *
 * TODO: not validating xml
 */
class StudsRuleSet extends RuleSetBase
{
	function addRuleInstances(&$digester)
	{
		// Data Sources Configuration
		$digester->addObjectCreate(
			'struts-config/data-sources/data-source',
			'studs.config.DataSourceConfig',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/data-sources/data-source'
		);
		$digester->addSetNext(
			'struts-config/data-sources/data-source',
			'addDataSourceConfig',
			'studs.config.DataSourceConfig'
		);
		$digester->addRule(
			'struts-config/data-sources/data-source/set-property',
			new AddPropertyRule()
		);

		// Action Mappings Configuration
		$digester->addObjectCreate(
			'struts-config/action-mappings/action',
			'studs.action.ActionMapping',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/action-mappings/action'
		);
		$digester->addSetNext(
			'struts-config/action-mappings/action',
			'addActionConfig',
			'studs.config.ActionConfig'
		);
		$digester->addSetProperty(
			'struts-config/action-mappings/action/set-property',
			'property',
			'value'
		);
		$digester->addObjectCreate(
			'struts-config/action-mappings/action/exception',
			'studs.config.ExceptionConfig',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/action-mappings/action/exception'
		);
		$digester->addSetNext(
			'struts-config/action-mappings/action/exception',
			'addExceptionConfig',
			'studs.config.ExceptionConfig'
		);
		$digester->addSetProperty(
			'struts-config/action-mappings/action/exception/set-property',
			'property',
			'value'
		);
		$digester->addObjectCreate(
			'struts-config/action-mappings/action/forward',
			'studs.action.ActionForward',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/action-mappings/action/forward'
		);
		$digester->addSetNext(
			'struts-config/action-mappings/action/forward',
			'addForwardConfig',
			'studs.config.ForwardConfig'
		);
		$digester->addSetProperty(
			'struts-config/action-mappings/action/forward/set-property',
			'property',
			'value'
		);

		// Controller Configuration
		$digester->addObjectCreate(
			'struts-config/controller',
			'studs.config.ControllerConfig',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/controller'
		);
		$digester->addSetNext(
			'struts-config/controller',
			'setControllerConfig',
			'studs.config.ControllerConfig'
		);
		$digester->addSetProperty(
			'struts-config/controller/set-property',
			'property',
			'value'
		);

		// Form Beans Configuration
		$digester->addObjectCreate(
			'struts-config/form-beans/form-bean',
			'studs.action.ActionFormBean',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/form-beans/form-bean'
		);
		$digester->addSetNext(
			'struts-config/form-beans/form-bean',
			'addFormBeanConfig',
			'studs.config.FormBeanConfig'
		);

		// Global Exceptions Configuration
		$digester->addObjectCreate(
			'struts-config/global-exceptions/exception',
			'studs.config.ExceptionConfig',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/global-exceptions/exception'
		);
		$digester->addSetNext(
			'struts-config/global-exceptions/exception',
			'addExceptionConfig',
			'studs.config.ExceptionConfig'
		);
		$digester->addSetProperty(
			'struts-config/global-exceptions/exception/set-property',
			'property',
			'value'
		);

		// Global Forwards Configuration
		$digester->addObjectCreate(
			'struts-config/global-forwards/forward',
			'studs.action.ActionForward',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/global-forwards/forward'
		);
		$digester->addSetNext(
			'struts-config/global-forwards/forward',
			'addForwardConfig',
			'studs.config.ForwardConfig'
		);
		$digester->addSetProperty(
			'struts-config/global-forwards/forward/set-property',
			'property',
			'value'
		);

		// Message Resources Configuration
		$digester->addObjectCreate(
			'struts-config/message-resources',
			'studs.config.MessageResourcesConfig',
			'className'
		);
		$digester->addSetProperties(
			'struts-config/message-resources'
		);
		$digester->addSetNext(
			'struts-config/message-resources',
			'addMessageResourcesConfig',
			'studs.config.MessageResourcesConfig'
		);
	}
}

/**
 * Class that calls {@link addProperty()} for the top object
 * on the stack.  In this case, the object has in internal array
 * of properties as opposed to getter/setter methods for each property
 */
class AddPropertyRule extends Rule
{
	function begin($namespace, $name, $attributes)
	{
		$top =& $this->digester->peek();
		$top->addProperty($attributes['property'], $attributes['value']);
	}
}
?>
