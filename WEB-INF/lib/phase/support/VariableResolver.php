<?php
/* $Id: VariableResolver.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.text.Scanner');
import('horizon.io.StringReader');
import('horizon.util.logging.Logger');
import('horizon.beanutils.NestedBeanStack');

/**
 * @package phase.support
 * @author Dan Allen
 *
 * FIXME: somehow 'null' is getting into the VariableResolver, which should have been already handled
 */
class VariableResolver
{
	/**
	 * @static
	 */
	function &getLog()
	{
		return Logger::getLogger('phase.support.VariableResolver');
	}

	/**
	 * Resolve a scoped variable at runtime.  The variable will be in EL form and
	 * will be dereferenced until the last element is reached.
	 *
	 * @static
	 * @return The value of the resolved variable
	 */
	function &resolveVariable($nestedProperty, &$pageContext)
	{
		$log =& VariableResolver::getLog();
		$log->debug('Resolving EL variable: ' . $nestedProperty);

		// try {

		// first we handle the initial lookup of the variable (we need a bean to work with)
		if (preg_match('/^(.+?)(\.|\[|$)(.*)/', $nestedProperty, $matches))
		{
			list(, $source, $delimiter, $remainder) = $matches;

			// if we are dealing with a variable scope or mapped parameter list
			if (in_array($source, array('pageScope', 'requestScope', 'sessionScope', 'applicationScope', 'param', 'paramValues', 'header', 'headerValues', 'initParam', 'cookie')))
			{
				// we must have at least one nested property
				if ($remainder == '')
				{
					return ref(null);
				}
				elseif ($delimiter == '.')
				{
					preg_match('/(.+?)((\.|\[|$).*)/', $remainder, $matches);
					list(, $property, $remainder) = $matches;
				}
				elseif ($delimiter == '[')
				{
					$scanner =& new Scanner(new StringReader($remainder));
					$start = $scanner->mark();
					$scanner->skipUntil(']', true);
					$end = $scanner->mark();
					$contents = $scanner->getChars($start, $end);
					// case: quoted string
					$property = stripslashes(substr($contents, 2, -2));
					$remainder = substr($remainder, strlen($contents));
				}
			}
			elseif ($source == 'pageContext')
			{
				if ($remainder == '')
				{
					return ref(null);
				}

				$remainder = $delimiter . $remainder;
				$property = $nestedProperty;
			}
			// here we lookup the variable using findAttribute, so our source becomes the property
			else
			{
				$property = $source;
				$source = '';
				$remainder = $delimiter . $remainder;
			}
		}
		// NOTE: invalid EL variable specified
		else
		{
			return ref(null);
		}

		$log->debug('Starting with: ' . $property . ($source ? ' located in ' . $source : ' in any scope'));

		switch ($source)
		{
			case 'pageContext':
				$bean =& $pageContext;
			break;

			case 'pageScope':
				$bean =& $pageContext->getAttribute($property, 'page');
			break;

			case 'requestScope':
				$bean =& $pageContext->getAttribute($property, 'request');
			break;

			case 'sessionScope':
				$bean =& $pageContext->getAttribute($property, 'session');
			break;

			case 'applicationScope':
				$bean =& $pageContext->getAttribute($property, 'application');
			break;

			case 'param':
				$bean =& $pageContext->request->getParameter($property);
			break;

			case 'paramValues':
				$bean =& $pageContext->request->getParameterValues($property);
			break;

			case 'header':
				$bean =& $pageContext->request->getHeader($property);
			break;

			case 'headerValues':
				$bean =& $pageContext->request->getHeaderValues($property);
			break;

			case 'initParam':
				$bean =& $pageContext->context->getInitParameter($property);
			break;
			
			default:
				$bean =& $pageContext->findAttribute($property);
			break;
		}

		$stack =& new NestedBeanStack();
		$stack->push($bean);
		$current =& $stack->getCurrent();
		
		while ($remainder != '')
		{
			$log->debug('Handling remainder: ' . $remainder);
			if ($remainder{0} == '.')
			{
				preg_match('/^\.(.+?)((\.|\[|$).*)/', $remainder, $matches);
				list(, $property, $remainder) = $matches;
				// NOTE: handle special case of mapped property
				if (is_a($current, 'HashMap'))
				{
					$stack->push($current->get($property));
				}
				// NOTE: handle mapped property in native array
				else if (is_array($current))
				{
					if (isset($current[$property]))
					{
						$stack->push($current[$property]);
					}
					else
					{
						$stack->push(ref(null));
					}
				}
				else
				{
					$stack->push(PropertyUtils::getSimpleProperty($current, $property));
				}
			}
			elseif ($remainder{0} == '[')
			{
				$scanner =& new Scanner(new StringReader($remainder));
				$start = $scanner->mark();
				$scanner->skipUntil(']', true);
				$end = $scanner->mark();
				$contents = $scanner->getChars($start, $end);
				// case: quoted string
				if ($contents{0} == '"' || $contents{0} == '\'')
				{
					$property = stripslashes(substr($contents, 2, -2));
				}
				// @fixme: probably an int (or nested variable, not handled!!)
				else
				{
					$property = substr($contents, 1, -1);
				}

				$remainder = substr($remainder, strlen($contents));
				if (is_array($current))
				{
					if (isset($current[$property]))
					{
						$stack->push($current[$property]);
					}
					else
					{
						// @fixme: is this right?
						return ref(null);
					}
				}
				elseif (preg_match('/^[a-z][a-z0-9_]*$/i', $property))
				{
					$stack->push(PropertyUtils::getSimpleProperty($current, $property));
				}
				else
				{
					$stack->push(PropertyUtils::getMappedProperty($current, $property));
				}
			}

			$current =& $stack->getCurrent();
		}

		// } catch (RootException e) {
		if ($e = catch_exception())
		{
			$log->error('An error occured resolving the EL variable "' . $property . '": ' . $e->getMessage() . '.', $e);
			// @fixme heads up!! this is a very big exception catch!!! we need to rethrow this!!!
			// assume that if we catch an error, what we are looking for just doesn't exist
			// NOTE: note we first remove the reference, then blank the variable
			$return =& ref(null);
		}
		// }
		else
		{
			$return =& $stack->compact();
		}
		return $return;
	}
}
?>
