<?php
/* $Id: CoreOutTag.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('phase.tagext.BodyTagSupport');
import('phase.support.ELEvaluator');
import('horizon.beanutils.ConvertUtils');

/**
 * Generic output of a value, allowing for escaping of XML/XHTML and
 * a fallback default if the value is empty or null.
 *
 * <b>EL-aware attributes</b>
 * <ul>
 *   <li>value</li>
 * </ul>
 *
 * @package phase.tags.core
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @access public
 *
 * TODO: I am not sure I have the logic correct with default attribute vs body
 */
class CoreOutTag extends BodyTagSupport
{
	var $value;

	var $escapeXml;

	var $default;

	var $valueExpression;

	var $bodyIsDefault;

	function CoreOutTag()
	{
		$this->init();
	}

	function init()
	{
		unset($this->value);
		$this->value = null;
		$this->valueExpression = null;
		$this->escapeXml = true;
		$this->default = '';
		$this->bodyIsDefault = false;
	}

	function setValue($value)
	{
		$this->valueExpression = $value;
	}

	/**
	 * @param boolean $escapeXml
	 */
	function setEscapeXml($escapeXml)
	{
		$this->escapeXml = ConvertUtils::convert($escapeXml, 'boolean');
	}

	function setDefault($default)
	{
		$this->default = $default;
	}

	function getValue()
	{
		return $this->value;
	}

	function getEscapeXml()
	{
		return $this->escapeXml;
	}

	function getDefault()
	{
		return $this->default;
	}

	function doStartTag()
	{
		$this->evaluateExpressions();

		if (!is_empty($this->value))
		{
			echo ($this->escapeXml && is_scalar($this->value)) ? htmlspecialchars($this->value) : $this->value;
		}
		elseif (!is_empty($this->default))
		{
			echo ($this->escapeXml && is_scalar($this->default)) ? htmlspecialchars($this->default) : $this->default;
		}
		else
		{
			$this->bodyIsDefault = true;
			return c('Tag::EVAL_BODY_BUFFERED');
		}

		return c('Tag::SKIP_BODY');
	}

	function doEndTag()
	{
		if ($this->bodyIsDefault && !is_empty($this->bodyContent))
		{
			echo $this->escapeXml ? htmlspecialchars($this->bodyContent) : $this->bodyContent;
		}

		return c('Tag::EVAL_PAGE');
	}

	function evaluateExpressions()
	{
		if (!is_null($this->valueExpression))
		{
			// @note should we expect that value is scaler vs string at this point?
			$this->value = ELEvaluator::evaluate('value', $this->valueExpression, 'string', $this->pageContext);
		}
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
