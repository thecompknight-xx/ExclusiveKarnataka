<?php
/* $Id: HtmlMessagesTag.php 260 2005-07-10 04:47:51Z mojavelinux $
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

import('horizon.collections.iterators.ListIterator');
import('horizon.beanutils.ConvertUtils');
import('horizon.util.logging.Logger');
import('phase.tagext.BodyTagSupport');
import('studs.taglib.TagUtils');
import('studs.StudsConstants');
// NOTE: we need both of these imports to unserialize session messages correctly
import('studs.action.ActionMessage');
import('studs.action.ActionMessages');

/**
 * @package studs.taglib.html
 * @author Dan Allen
 */
class HtmlMessagesTag extends BodyTagSupport
{
	/**
	 * The key into which the message will be placed.
	 * @var string
	 */
	var $id;

	/**
	 * Specifies that the global action messages should be used.
	 * @var string
	 */
	var $message;

	/**
	 * The property field is mainly for errors cooresponding to a particular element, such
	 * as a form input.
	 * @var string
	 */
	var $property;

	var $name;

	var $localeKey;

	var $bundle;

	var $scope;

	var $_iterator;

	var $_messagesExist;

	function &getLog()
	{
		return Logger::getLogger('studs.taglib.html.HtmlMessagesTag');
	}

	function HtmlMessagesTag()
	{
		$this->init();
	}

	function init()
	{
		$this->id = null;
		$this->message = null;
		$this->property = null;
		$this->name = c('StudsConstants::ERRORS_KEY');
		$this->bundle = c('StudsConstants::MESSAGE_RESOURCES_KEY');
		$this->scope = 'page';
		$this->localeKey = c('StudsConstants::LOCALE_KEY');
	}

	function setId($id)
	{
		$this->id = $id;
	}

	function setMessage($message)
	{
		$this->message = $message;
	}

	function setProperty($property)
	{
		$this->property = $property;
	}

	function setName($name)
	{
		$this->name = $name;
	}

	function setLocale($localeKey)
	{
		$this->localeKey = $localeKey;
	}

	function setBundle($bundle)
	{
		$this->bundle = $bundle;
	}

	function setScope($scope)
	{
		$this->scope = $scope;
	}

	function doStartTag()
	{
		$log =& $this->getLog();

		$this->evaluateExpressions();

		$this->_messagesExist = false;

		$messages = null;

		$name = $this->name;

		if (ConvertUtils::convert($this->message, 'boolean'))
		{
			$name = c('StudsConstants::MESSAGES_KEY');
		}
		
		$log->debug('Retrieving messages for ' . $name . ' in ' . $this->scope . ' scope');

		// make sure the session is awake, 'cause we need it
		if ($this->scope == 'session')
		{
			$request =& $this->pageContext->getRequest();
			$request->getSession();
		}

		// QUESTION: assume ActionMessages object?
		$messages =& $this->pageContext->getAttribute($name, $this->scope);

		// NOTE: removing messages from all scopes (session scoped messages)
		$this->pageContext->removeAttribute($name, $this->scope);

		if (is_null($messages))
		{
			return c('Tag::SKIP_BODY');
		}

		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Found ' . $messages->size($this->property) . ' messages for property ' . $this->property);
		}

		// NOTE: if property is null, all messages are returned under the key
		$this->_iterator =& new ListIterator($messages->get($this->property));

		if (!$this->processNextMessage())
		{
			return c('Tag::SKIP_BODY');
		}

		// TODO: message header from resources file

		$this->_messagesExist = false;

		return c('Tag::EVAL_BODY_INCLUDE');
	}

	function doAfterBody()
	{
		if ($this->processNextMessage())
		{
			return c('Tag::EVAL_BODY_AGAIN');
		}
		else
		{
			return c('Tag::SKIP_BODY');
		}
	}

	function doEndTag()
	{
		// TODO: message footer
	}

	/**
	 * @return boolean If the next message existed and was processed
	 */
	function processNextMessage()
	{
		if (!$this->_iterator->hasNext())
		{
			$this->pageContext->removeAttribute($this->id);
			return false;
		}

		$log =& $this->getLog();

		$message =& $this->_iterator->next();
		$log->debug('Processing next message ' . $message->getKey());
		$msg = TagUtils::message($this->pageContext, $this->bundle, $this->localeKey, $message->getKey(), $message->getValues());
		if (is_null($msg))
		{
			$this->pageContext->removeAttribute($this->id);
		}
		else
		{
			$this->pageContext->setAttribute($this->id, $msg);
		}

		return true;
	}

	function evaluateExpressions()
	{
	}

	function release()
	{
		parent::release();
		$this->init();
	}
}
?>
