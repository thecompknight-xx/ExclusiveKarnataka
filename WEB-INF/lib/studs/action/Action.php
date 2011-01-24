<?php
/* $Id: Action.php 352 2006-05-15 04:27:35Z mojavelinux $
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
import('studs.util.ModuleUtils');
import('studs.action.ActionMessages');

/**
 * @package studs.action
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @access public
 */
class Action extends Object
{
	/**
	 * The controller servlet to which we are attached (ActionServlet)
	 * @var HttpServlet
	 * @access protected
	 */
	var $servlet = null;

	/**
	 * Return the controller servlet instance to which we are attached.
	 *
	 * @return ActionServlet
	 */
	function &getServlet()
	{
		return $this->servlet;
	}

	/**
	 * Set the controller servlet instance to which we are attached
	 *
	 * @param ActionServlet $servlet
	 * @return void
	 */
	function setServlet(&$servlet)
	{
		$this->servlet =& $servlet;
	}

	/**
	 * Process the specified HTTP request, and create the corresponding HTTP
	 * response (or forward to another web component that will create it).
	 * Return an {@link ActionForward} instance describing where and how
	 * control should be forwarded, or <kbd>null</kbd> if the response has
	 * already been completed.
	 *
	 * @param ActionMapping $mapping
	 * @param ActionForm $form
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 * @return ActionForward
	 */
	function &execute(&$mapping, &$form, &$request, &$response)
	{
		// Override this method to provide functionality
		return ref(null);
	}

    /**
     * Return the default message resources for the current module.
     *
     * @param request The HttpServletRequest we are processing
	 * @return MessageResources
     */
	function &getResources(&$request, $key = null)
	{
		if (is_null($key))
		{
			$key = c('StudsConstants::MESSAGE_RESOURCES_KEY');
		}
		
		// identify the current module
		$servletContext =& $this->servlet->getServletContext();
		$moduleConfig =& ModuleUtils::getModuleConfig($request, $servletContext);

		// return the requested messese resources instance
		return $servletContext->getAttribute($key . $moduleConfig->getPrefix());
	}

	/**
	 * Generate a new transaction token, to be used for enforcing a single request
	 * for a particular transaction
	 *
	 * @param HttpServletRequest $request The request we are processing
	 * @return string
	 */
	function generateToken(&$request)
	{
		$session =& $request->getSession();
		return md5($session->getId() . gettimemillis());
	}

    /**
     * Return the default data source for the current module.
     *
     * @param request The HttpServletRequest we are processing
	 * @return DataSource
	 * @throws ServletException If the datasource with the specified key could not be found
	 */
	function &getDataSource(&$request, $key = null)
	{
		if (is_null($key))
		{
			$key = c('StudsConstants::DATA_SOURCE_KEY');
		}

		// identify the current module
		$servletContext =& $this->servlet->getServletContext();
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

	/**
	 * Return the user's currently selected Locale.  RequestUtils are used
	 * just in case we are using the session to override the container supplied
	 * locale.
	 *
	 * @param HttpServletRequest $request
	 * @return string
	 */
	function getLocale(&$request)
	{
		return RequestUtils::getUserLocale($request, null);
	}

	/**
	 * Tuck the locale away in the session, which is either the manual
	 * override specified by the parameter or the one retrieved from
	 * the container
	 *
	 * @param HttpServletRequest $request
	 * @param string $locale
	 * @return void
	 */
	function setLocale($request, $locale)
	{
		$session =& $request->getSession();
		if (is_null($locale))
		{
			$locale = $request->getLocale();
		}

		$session->setAttribute(c('StudsConstants::LOCALE_KEY'), $locale);
	}

	/**
	 * Returns <kbd>true</kbd> if the current form's cancel button was
	 * pressed.  Note that this processing is done in the controller servlet
	 * and if this attribute is true, the validation would have been skipped
	 * for any associated {@link ActionForm}.
	 *
	 * @param HttpServletRequest $request The request we are processing
	 * @return boolean
	 */
	function isCancelled(&$request)
	{
		return $request->getAttribute(c('StudsConstants::CANCEL_KEY'));
	}

	/**
	 * Return <kbd>true</kbd> if there is a transaction token stored
	 * in the user's current session, and the value submitted as a request
	 * parameter with this action matches it.  Returns <kbd>false</kbd>
	 * <ul>
	 * <li>No transaction token saved in the session</li>
	 * <li>No transaction token included as a request parameter</li>
	 * <li>The included transaction token value does not match the
	 *     transaction token in the user's session</li>
	 * </ul>
	 *
	 * <p>It is necessary to ensure the session is created so that the user
	 * does not have to create a session just to use this feature.  We assume
	 * use of this feature is requesting that a session exists.</p>
	 *
	 * @param HttpServletRequest $request The request we are processing
	 * @param boolean $reset Reset the token after checking it
	 * @return boolean Whether or not the token is valid
	 */
	function isTokenValid(&$request, $reset = false)
	{
		$session =& $request->getSession();

		$saved = $session->getAttribute(c('StudsConstants::TRANSACTION_TOKEN_KEY'));
		if (is_null($saved))
		{
			return false;
		}

		if ($reset)
		{
			$session->removeAttribute(c('StudsConstants::TRANSACTION_TOKEN_KEY'));
		}

		$token = $request->getParameter(c('StudsConstants::TRANSACTION_TOKEN_PARAM'));
		if (is_null($token))
		{
			return false;
		}

		return ($saved == $token);
	}

	/**
	 * Reset the saved transaction token in the user's session.  This
	 * indicates that transactional token checking will not be needed
	 * on the next request that is submitted.
	 *
	 * @param HttpServletRequest $request The request we are processing
	 * @return void
	 */
	function resetToken(&$request)
	{
		$session =& $request->getSession(false);
		if (is_null($session))
		{
			return;	
		}

		$session->removeAttribute(c('StudsConstants::TRANSACTION_TOKEN_KEY'));
	}

	/**
	 * Retrieves any existing messages placed in the request by previous
	 * actions.  This method could be called instead of creating a new
	 * collection of messages at the beginning of an {@link Action}.
	 * This will prevent {@link Action#saveMessages()} from wiping out any
	 * existing messages.
	 */
	function &getMessages(&$request, $inSession = false)
	{
		$scope =& $request;
		if ($inSession)
		{
			$scope =& $request->getSession();
		}

		$messages =& $scope->getAttribute(c('StudsConstants::MESSAGES_KEY'));
		if (is_null($messages))
		{
			$messages =& new ActionMessages();
		}

		return $messages;
	}

	/**
	 * Save the specified error keys into the appropriate request attribute
	 * (or session attribute) if the collection is not empty. Otherwise, ensure
	 * that the request attribute is not created.
	 *
	 * @param HttpServletRequest $request
	 * @param ActionMessages $errors
	 * @param String $inSession Use the session object for storing errors
	 * @return void
	 */
	function saveErrors(&$request, &$errors, $inSession = false)
	{
		$scope =& $request;
		if ($inSession)
		{
			$scope =& $request->getSession();
		}

		if (is_null($errors) || $errors->isEmpty())
		{
			$scope->removeAttribute(c('StudsConstants::ERRORS_KEY'));
		}
		else
		{
			$scope->setAttribute(c('StudsConstants::ERRORS_KEY'), $errors);
		}
	}

	/**
	 * Save the specified messages keys into the appropriate request attribute
	 * (or session attribute) if the collection is not empty. Otherwise, ensure
	 * that the request attribute is not created.
	 *
	 * @param HttpServletRequest $request
	 * @param ActionMessages $messages
	 * @param String $inSession Use the session object for storing messages
	 * @return void
	 */
	function saveMessages(&$request, &$messages, $inSession = false)
	{
		$scope =& $request;
		if ($inSession)
		{
			$scope =& $request->getSession();
		}

		if (is_null($messages) || $messages->isEmpty())
		{
			$scope->removeAttribute(c('StudsConstants::MESSAGES_KEY'));
		}
		else
		{
			$scope->setAttribute(c('StudsConstants::MESSAGES_KEY'), $messages);
		}
	}

	/**
	 * Save a new transaction token in the user's current session,
	 * creating a new session if necessary.
	 *
	 * @param HttpServletRequest $request The request we are processing
	 * @return void
	 */
	function saveToken(&$request)
	{
		$session =& $request->getSession();
		$session->setAttribute(c('StudsConstants::TRANSACTION_TOKEN_KEY'), ref($this->generateToken($request)));
	}
}
?>
