<?php
/* $Id: ELEvaluator.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.io.StringReader');
import('horizon.text.Scanner');
import('horizon.util.StringUtils');
import('horizon.util.logging.Logger');
import('phase.support.ELExpression');
import('phase.support.ELExpressionString');
import('phase.support.VariableResolver');

/**
 * Class <b>ELEvaluator</b> is the manager for evaluating EL
 * expression strings.
 *
 * An expression string is a string that may contain expressions of
 * the form ${...}.  Multiple expressions may appear in the same expression
 * string.  In such a case, the expression string's value is computed by
 * concatenating the string values of those evaluated expressions and any
 * intervening non-expression text, then converting the resulting string to the
 * expected type.
 *
 * In the special case where the expression string is a single
 * expression, the value of the expression string is determined by
 * evaluating the expression, without any intervening conversion to a
 * string.
 *
 * @package phase.support
 * @author Dan Allen <dan.allen@mojavelinux.com>
 */
class ELEvaluator
{
	/**
	 * @static
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('phase.support.ELEvaluator');
		return $logger;
	}

	/**
	 * @static
	 */
	function &evaluate($name, $expressionString, $expectedType = 'string', &$pageContext)
	{
		$log =& ELEvaluator::getLog();
		$log->debug('Evaluating EL expression: ' . $expressionString);

		if (is_null($expressionString))
		{
			// @todo throw an ELException...why? why not just return null??
			return ref(null);
		}

		$parsedValue =& ELEvaluator::parseExpressionString($expressionString);

		// the expressionString is actually a simple string value
		if (is_string($parsedValue))
		{
			return $parsedValue;
		}
		// the expressionString is either an ELExpression or ELExpressionString
		else
		{
			// the EL expression is converted to a PHP expression and evaluated
			$evalCode = $parsedValue->evaluate();
			$log->debug('Expression interpretation: ' . $evalCode);
			// TODO: cannot return by reference here!!! currently disabled!
			$result = eval('return ' . $evalCode . ';');
			$log->debug('Expression result: ' . $result);
			return $result;
		}
	}

	/**
	 * Gets the parsed version of the expression string so that it
	 * can be evaluated in parts.
	 *
	 * @param string $expressionString
	 *
	 * @return array
	 */
	function &parseExpressionString($expressionString)
	{
		if (strlen($expressionString) == 0)
		{
			return '';
		}

		$reader =& new StringReader($expressionString);
		$scanner =& new Scanner($reader);
		$elements = array();
		$buffer = '';
		
		while ($scanner->hasMoreInput())
		{
			if ($scanner->matches('${'))
			{
				// if we have buffered intermediary string, push
				// it on to the elements array
				if (strlen($buffer) > 0)
				{
					$elements[] = $buffer;
					$buffer = '';
				}

				$start = $scanner->mark();
				// skip until the next }, not considering } characters
				// which fall within string literals
				$end = $scanner->skipUntil('}', true);

				if ($end > 0)
				{
					$elements[] =& new ELExpression($scanner->getChars($start, $end));
				}
				else
				{
					if (is_null($end))
					{
						// @todo throw an ELException, or perhaps recover?
					}
				}
			}

			$buffer .= $scanner->nextChar();
		}

		if (strlen($buffer) > 0)
		{
			$elements[] = $buffer;
		}

		if (count($elements) == 1)
		{
			return $elements[0];
		}
		else
		{
			$expr =& new ELExpressionString($elements);
			return $expr;
		}
	}
}
?>
