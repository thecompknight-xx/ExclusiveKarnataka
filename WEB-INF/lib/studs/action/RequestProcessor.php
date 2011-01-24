<?php
/* $Id: RequestProcessor.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('stratus.ServletException');
import('studs.action.ActionForward');
import('studs.util.RequestUtils');
import('horizon.util.logging.Logger');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package studs.action
 * @access public
 */
class RequestProcessor
{
	/**
	 * The controller servlet with which we are associated
	 * @var ActionServlet
	 * @access protected
	 */
	var $servlet;

	/**
	 * The ModuleConfig with which we are associated
	 * @var ModuleConfig
	 * @access protected
	 */
	var $moduleConfig;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 *
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('studs.action.RequestProcessor');
		return $logger;
	}

	/**
	 * Return the ServletContext for the web application in which we are running
	 *
	 * @return ServletContext
	 */
	function &getServletContext()
	{
		$sc =& $this->servlet->getServletContext();
		return $sc;
	}

	/**
	 * Initialize the request processor instance
	 *
	 * @param ActionServlet $servlet The servlet with which we are associated
	 * @param ApplicationConfig $config The module configuration  with which we are associated
	 * @return void
	 */
	function init(&$servlet, &$moduleConfig)
	{
		$this->servlet =& $servlet;	
		$this->moduleConfig =& $moduleConfig;
	}

	/**
	 * Process an {@link HttpServletRequest} and create the corresponding
	 * {@link HttpServletResponse}
	 *
	 * @param HttpServletRequest $request The servlet request we are processing
	 * @param HttpServletResponse $response The servlet response we are delivering
	 * @return void
	 */
	function process(&$request, &$response)
	{
		$path = $this->processPath($request, $response);
		if (is_null($path))
		{
			return;
		}

		$log =& RequestProcessor::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Processing a "' . $request->getMethod() . '" for path "' . $path . '"');
		}

		// select a Locale for the current user
		$this->processLocale($request, $response);

		// set appropriate headers
		$this->processContent($request, $response);
		$this->processNoCache($request, $response);

		$mapping =& $this->processActionMapping($request, $response, $path);
		if (is_null($mapping))
		{
			return;
		}

		$form =& $this->processActionForm($request, $response, $mapping);
		$this->processActionFormPopulate($request, $response, $form, $mapping);
		if (!$this->processActionFormValidate($request, $response, $form, $mapping))
		{
			return;
		}

		// process a direct forward specified by this mapping
		if (!$this->processForward($request, $response, $mapping))
		{
			return;
		}

		// process a direct include specified by this mapping
		if (!$this->processInclude($request, $response, $mapping))
		{
			return;
		}

		// create or acquire the Action instance to process the request
		$action =& $this->processActionCreate($request, $response, $mapping);
		if (is_null($action))
		{
			return;
		}

		// call the action instance itself
		$forward =& $this->processActionExecute($request, $response, $action, $form, $mapping);

		// process the returned ActionForward instance
		$this->processActionForward($request, $response, $forward);
	}

	/**
	 * @return void
	 */
	function processNoCache(&$request, &$response)
	{
		$controllerConfig =& $this->moduleConfig->getControllerConfig();
		if ($controllerConfig->isNocache())
		{
			$response->setHeader('Pragma', 'No-cache');
			$response->setHeader('Cache-Control', 'no-cache');
			$response->setHeader('Expires', 1);
		}
	}

	/**
	 * Identify and return the path info component (from the request URI) that
	 * we will use to select an <b>ActionMapping</b>.  If no such
	 * path can be identified, create an error response and return
	 * <kbd>null</kbd>.
	 *
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 * @return string The path component of the URI or null
	 */
	function processPath(&$request, &$response)
	{
		$path = $request->getPathInfo();
		if (!is_null($path) && strlen($path) > 0)
		{
			return $path;
		}

		$path = new String($request->getServletPath());
		$prefix = $this->moduleConfig->getPrefix();

		if (!$path->startsWith($prefix))
		{
			// @todo send error header to response
			return null;
		}

		$path = $path->substring(strlen($prefix));
		$slash = $path->lastIndexOf('/');
		$period = $path->lastIndexOf('.');
		if ($period >=0 && $period > $slash)
		{
			$path = $path->substring(0, $period);
		}

		$path = $path->toString();
		return $path;
	}

	/**
	 * Select the mapping used to process the selection path for this request.
	 * If no mapping can be identified, create an error response and return
	 * <kbd>null</kbd>.
	 *
	 * @param HttpServletRequest $request The servlet request we are processing
	 * @param HttpServletResponse $response The servlet response we are creating
	 * @param string $path The portion of the request URI for selecting a mapping
	 */
	function &processActionMapping(&$request, &$response, $path)
	{
		$mapping =& $this->moduleConfig->findActionConfig($path);
		if (!is_null($mapping))
		{
			return $mapping;
		}

		$log =& RequestProcessor::getLog();

		// locate the mapping for unknown paths (find the fallback action-mapping basically)
		$actionConfigs = $this->moduleConfig->findActionConfigs();
		for ($i = 0; $i < count($actionConfigs); $i++)
		{
			if ($actionConfigs[$i]->isUnknown())
			{
				if ($log->isLoggable('INFO'))
				{
					$log->info('Invalid path "' . $path . '" was requested, but the \'unknown\' flag was set for path "' . $actionConfigs[$i]->getPath() . '"');
				}

				return $actionConfigs[$i];
			}
		}

		// no mapping can be found to process this request, treat it as if it is a file not found (404)
		$msg = 'Invalid path ' . $path . ' was requested';
		$log->warn($msg);
		$response->sendError(c('HttpServletResponse::SC_NOT_FOUND'), $msg);
		$nil =& ref(null);
		return $nil;
	}

	function &processActionForm(&$request, &$response, &$mapping)
	{
		$instance =& RequestUtils::createActionForm($request, $mapping, $this->moduleConfig, $this->servlet);
		if (is_null($instance))
		{
			return $instance;
		}

		$log =& RequestProcessor::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Storing ActionForm instance in scope "' . $mapping->getScope() . '" using attribute key "' . $mapping->getAttribute() . '"');
		}

		if ($mapping->getScope() == 'request')
		{
			$request->setAttribute($mapping->getAttribute(), $instance);
		}
		else
		{
			$session =& $request->getSession();	
			$session->setAttribute($mapping->getAttribute(), $instance);
		}

		return $instance;
	}

	/**
	 * Populate the properties of the specified ActionForm instance from
	 * the request parameters included with this request.  In addition,
	 * request attribute StudsConstants::CANCEL_KEY will be set if
	 * the request was submitted with a cancel button.
	 *
	 * @return void
	 * @throws ServletException
	 */
	function processActionFormPopulate(&$request, &$response, &$form, &$mapping)
	{
		if (is_null($form))
		{
			return;
		}
		
		$form->setServlet($this);
		$form->reset($mapping, $request);

		RequestUtils::populate($form, $request);

		// set the cancellation request attribute if appropriate
		// QUESTION: why don't we skip populating the form bean?
		if (!is_null($request->getParameter(c('StudsConstants::CANCEL_PARAM'))) ||
			!is_null($request->getParameter(c('StudsConstants::CANCEL_PARAM') . '.x')))
		{
			$request->setAttribute(c('StudsConstants::CANCEL_KEY'), ref(true));
		}
	}

	function processActionFormValidate(&$request, &$response, &$form, &$mapping)
	{
		if (is_null($form))
		{
			return true;
		}

		// skip the validation if the cancel key was pressed
		if (!is_null($request->getAttribute(c('StudsConstants::CANCEL_KEY'))))
		{
			return true;
		}
		
		// check if validation has been turned off for this form
		if (!$mapping->isValidate())
		{
			return true;
		}

		$errors =& $form->validate($mapping, $request);	
		if (is_null($errors) || $errors->isEmpty())
		{
			return true;
		}

		$input = $mapping->getInput();
		if (is_null($input))
		{
			// send a INTERNAL_SERVER_ERROR to the response
			return null;
		}

		$request->setAttribute(c('StudsConstants::ERRORS_KEY'), $errors);
		
		$controllerConfig =& $this->moduleConfig->getControllerConfig();
		if ($controllerConfig->isInputForward())
		{
			$forward =& $mapping->findForward($input);
			$this->processActionForward($request, $response, $forward);
		}
		else
		{
			$this->doForward($this->moduleConfig->getPrefix() . $input, $request, $response);
		}

		return false;
	}

	/**
	 * Return an {@link Action} instance that will be used to process
	 * the current request, creating a new one if necessary.
	 *
	 * @todo make sure the action instance can be created
	 * @return Action
	 */
	function &processActionCreate(&$request, &$response, &$mapping)
	{
		$className = $mapping->getType();

		$log =& RequestProcessor::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Looking for Action instance in application scope for class "' . $className . '"');
		}

		$instance =& RequestUtils::applicationInstance($className);
		$instance->setServlet($this->servlet);
		return $instance;
	}

	/**
	 * @todo catch any exceptions here an handle them
	 */
	function &processActionExecute(&$request, &$response, &$action, &$form, &$mapping)
	{
		// try {
		$result =& $action->execute($mapping, $form, $request, $response);
		// } catch (RootException $e) {
		if ($e = catch_exception())
		{
			$result =& $this->processException($request, $response, $e, $form, $mapping);
		}
		// }

		return $result;
	}

	/**
	 * @return void
	 */
	function processContent(&$request, &$response)
	{
		$controllerConfig =& $this->moduleConfig->getControllerConfig();
		$contentType = $controllerConfig->getContentType();
		if (!is_null($contentType))
		{
			$response->setContentType($contentType);
		}
	}

	/**
	 * @return ActionForward
	 */
	function &processException(&$request, &$response, &$exception, &$form, &$mapping)
	{
		// check if there a defined handler for this exception
		$config =& $mapping->findException($exception->getClass());
		if (is_null($config))
		{
			$log =& RequestProcessor::getLog();
			$log->warn('An unhandled exception was thrown of type "' . $exception->getClassName() . '"; message = ' . $exception->getMessage());
			// TODO: check type of exception and throw it appropriately
			throw_exception(new ServletException(null, $exception)); return;
		}

		$handler =& RequestUtils::applicationInstance($config->getHandler());	
		$exec =& $handler->execute($exception, $config, $mapping, $form, $request, $response);
		return $exec;
	}

	/**
	 * Process a forward requested by this mapping (if any).  Return
	 * <kbd>true</kbd> if standard processing should continue, or
	 * <kbd>false</kbd> if we have already handled this request.
	 *
	 * <b>IMPLEMENTATION NOTE</b>: This differs from the Struts implementation
	 * since Struts does not use the forwardPattern substitution here (though it should)
	 *
	 * @param $request HttpServletRequest The servlet request we are processing
	 * @param $response HttpServletResponse The servlet response we are creating
	 * @param $mapping ActionMapping The ActionMapping we are using
	 */
	function processForward(&$request, &$response, &$mapping)
	{
		$forwardPath = $mapping->getForward();
		if (is_null($forwardPath))
		{
			return true;
		}

		// @todo: which one should we use??
		//$uri = $this->moduleConfig->getPrefix() . $forwardPath;
		$uri = RequestUtils::forwardURL($request, new ActionForward(null, $forwardPath), ref(null));
		$this->doForward($uri, $request, $response);
		return false;
	}

	/**
	 * Process an include requested by this mapping (if any).  Return
	 * <kbd>true</kbd> if standard processing should continue, or
	 * <kbd>false</kbd> if we have already handled this request.
	 *
	 * <b>IMPLEMENTATION NOTE</b>: This differs from the Struts implementation
	 * since Struts does not use the forwardPattern substitution here (though it should)
	 *
	 * @param $request HttpServletRequest The servlet request we are processing
	 * @param $response HttpServletResponse The servlet response we are creating
	 * @param $mapping ActionMapping The ActionMapping we are using
	 */
	function processInclude(&$request, &$response, &$mapping)
	{
		$includePath = $mapping->getInclude();
		if (is_null($includePath))
		{
			return true;
		}

		// @todo: which one should we use?
		//$uri = $this->moduleConfig->getPrefix() . $includePath;
		$uri = RequestUtils::forwardURL($request, new ActionForward(null, $includePath), ref(null));
		$this->doInclude($uri, $request, $response);
		return false;
	}

	/**
	 * Automatically select a Locale for the current user, if requested.
	 * The idea here is that if the controller is configured to do so, we
	 * can allow locales to be set into the session scope (such as if a user
	 * selected a language of choice) which would override the container supplied
	 * preference for locale.
	 *
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 * @return void
	 */
	function processLocale(&$request, &$response)
	{
		// check if we are to select the locale from session or if we just use
		// the one from the HttpServletRequest
		$controllerConfig =& $this->moduleConfig->getControllerConfig();
		if (!$controllerConfig->getLocale())
		{
			return;
		}

		// determine if a locale has already been set
		$session =& $request->getSession();
		if (!is_null($session->getAttribute(c('StudsConstants::LOCALE_KEY'))))
		{
			return;
		}

		// just use the locale set by the container
		$locale = $request->getLocale();
		if (!is_null($locale))
		{
			$log =& RequestProcessor::getLog();
			if ($log->isLoggable('DEBUG'))
			{
				$log->debug('Setting user locale "' . $locale . '"');
			}

			$session->setAttribute(c('StudsConstants::LOCALE_KEY'), $locale);
		}
	}

	/**
	 * @return void
	 */
	function processActionForward(&$request, &$response, &$forward)
	{
		if (is_null($forward))
		{
			return null;
		}

		$uri = RequestUtils::forwardURL($request, $forward, ref(null));

		$log =& RequestProcessor::getLog();
		if ($log->isLoggable('DEBUG'))
		{
			$log->debug('Delegating via forward to "' . $uri . '"');
		}

		if ($forward->isRedirect())
		{
			if ($uri[0] == '/')
			{
				$uri = $request->generateControllerPath($request->getContextPath()) . $uri;
			}

			$response->sendRedirect($uri);
		}
		else
		{
			$this->doForward($uri, $request, $response);
		}
	}

	/**
	 * Do a forward to specified uri using request dispatcher.
	 *
	 * @param string $uri Context-relative uri to forward to
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 *
	 * @return void
	 */
	function doForward($uri, &$request, &$response)
	{
		$servletContext =& $this->getServletContext();
		$rd =& $servletContext->getRequestDispatcher($uri);
		if (is_null($rd))
		{
			$response->sendError(c('HttpServletResponse::SC_INTERNAL_SERVER_ERROR'), 'Cannot get request dispatcher for path ' . $uri);
			return;
		}

		$rd->doForward($request, $response);
	}

	/**
	 * Do an include to specified uri using request dispatcher.
	 *
	 * @param string $uri Context-relative uri to include
	 * @param HttpServletRequest $request
	 * @param HttpServletResponse $response
	 *
	 * @return void
	 */
	function doInclude($uri, &$request, &$response)
	{
		$servletContext =& $this->getServletContext();
		$rd =& $servletContext->getRequestDispatcher($uri);
		if (is_null($rd))
		{
			$response->sendError(c('HttpServletResponse::SC_INTERNAL_SERVER_ERROR'), 'Cannot get request dispatcher for path ' . $uri);
			return;
		}

		$rd->doInclude($request, $response);
	}
}
?>
