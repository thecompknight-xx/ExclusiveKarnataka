<?php
/* $Id: HtmlFormTag.php 359 2006-05-15 04:49:32Z mojavelinux $
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

import('phase.tagext.TagSupport');

import('studs.StudsConstants');
import('studs.taglib.TagUtils');
import('studs.util.RequestUtils');

/**
 * Render a form element on the page based on information from
 * the Studs configuration so as to associate this form to an action.
 *
 * TODO: allow for the "name" attribute which references a defined form
 *
 * @package studs.taglib.html
 * @author Dan Allen
 */
class HtmlFormTag extends TagSupport
{
	/**
	 * The action URL to which the form should be submitted.  This URL should be the
	 * name of a mapping from struts-config.xml that cooresponds to a form bean
	 */
	var $action = null;

	var $enctype = null;

	var $method = null;

	var $beanName = null;

	var $beanScope = null;

	var $mapping = null;

	var $type = null;

	function HtmlFormTag()
	{
		$this->init();
	}

	function init()
	{
		$this->action = null;
		$this->method = 'POST';
		unset($this->mapping);
		$this->mapping = null;
		$this->type = null;
	}

	function setAction($action)
	{
		$this->action = $action;
	}

	function setMethod($method)
	{
		$this->method = strtoupper($method);
	}

	function setType($type)
	{
		$this->type = $type;
	}

	function doStartTag()
	{
		$this->lookup();

		$output = $this->renderStartTag();

		$output .= $this->renderToken();

		echo $output;

		$this->pageContext->setAttribute(c('StudsConstants::FORM_KEY'), $this, 'request');

		$this->initFormBean();

		return c('Tag::EVAL_BODY_INCLUDE');
	}

	function doEndTag()
	{
		$this->pageContext->removeAttribute(c('StudsConstants::FORM_KEY'), 'request');
		$this->pageContext->removeAttribute(c('StudsConstants::BEAN_KEY'), 'request');

		$output = $this->renderEndTag();

		echo $output;

		return c('Tag::EVAL_PAGE');
	}

	function renderStartTag()
	{
		$method = is_null($this->method) ? 'POST' : $this->method;
		$action = TagUtils::getActionMappingURL($this->action, $this->pageContext);
		// TODO: run encodeURL on the action

		return '<form name="' . $this->beanName . '" method="' . $method . '" action="' . $action . '">';
	}

	/**
	 * Look in the session for an active token and if found, place it
	 * as a hidden field in the form using the transaction token parameter
	 * name that Studs can discover upon form submit.
	 */
	function renderToken()
	{
		$results = '';
		$session =& $this->pageContext->getSession();
		if (!is_null($session))
		{
			$token = $session->getAttribute(c('StudsConstants::TRANSACTION_TOKEN_KEY'));
			if (!is_null($token))
			{
				$results = '<input type="hidden" name="' . c('StudsConstants::TRANSACTION_TOKEN_PARAM') . '" value="' . $token . '" />';
			}
		}

		return $results;
	}

	function renderEndTag()
	{
		return '</form>';
	}

	/**
	 * @throws {@link PhaseException} If a required value cannot be looked up
	 * @return void
	 */
	function lookup()
	{
		$moduleConfig =& TagUtils::getModuleConfig($this->pageContext);		
		$mappingName = TagUtils::getActionMappingName($this->action);
		$this->mapping =& $moduleConfig->findActionConfig($mappingName);
		if (is_null($this->mapping))
		{
			throw_exception(new PhaseException('Cannot retrieve mapping for action ' . $mappingName));
			return;
		}

		$this->beanName = $this->mapping->getAttribute();	
		$this->beanScope = $this->mapping->getScope();
	}

	function initFormBean()
	{
		$bean =& $this->pageContext->getAttribute($this->beanName, $this->beanScope);
		// create the bean if it doesn't already exist
		if (is_null($bean))
		{
			$context =& $this->pageContext->getServletContext();
			$servlet =& $context->getAttribute(c('StudsConstants::ACTION_SERVLET_KEY'));	
			// allow for type override
			if (!is_null($this->type))
			{
				$bean =& RequestUtils::applicationInstance($this->type);
				if ($bean != null)
				{
					$bean->setServlet($servlet);
				}
			}
			else
			{
				$bean =& RequestUtils::createActionForm(
					$this->pageContext->getRequest(),
					$this->mapping,
					TagUtils::getModuleConfig($this->pageContext),
					$servlet
				);
			}

			if (!is_a($bean, 'ActionForm'))
			{
				throw_exception(new PhaseException('Cannot create instance of action form ' . $this->mapping->getName()));
				return;
			}

			$bean->reset($this->mapping, $this->pageContext->getRequest());
		}

		$this->pageContext->setAttribute(c('StudsConstants::BEAN_KEY'), $bean, 'request');
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
