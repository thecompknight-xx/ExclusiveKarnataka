<?php
/* $Id: ELExpression.php 188 2005-04-07 04:52:31Z mojavelinux $
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

import('horizon.util.StringUtils');
import('horizon.util.logging.Logger');

/**
 * An ELExpression is contained within ${...} brackets.  Evaluation
 * is done by wrapping variables with a ResolveVariable lookup and
 * allowing the other characters to pass through to create a PHP
 * compatible expression.
 *
 * @package phase.support
 * @author Dan Allen <dan.allen@mojavelinux.com>
 *
 * TODO: find a neutral way to determine equality, since primitives should
 * be able to compare easier CompareUtils?
 * TODO: determine when we are expecting a variable vs. an operator (after a
 *       variable we better get an operator)
 * TODO: use ConvertUtils to convert to expected type
 */
class ELExpression
{
	var $expression;

	var $operators = array(
		'eq' => '===',
		'ne' => '!==',
		'lt' => '<',
		'gt' => '>',
		'le' => '<=',
		'ge' => '>=',
		'mod' => '%',
		'div' => '/',
		'not' => '!',
		'and' => '&&',
		'or' => '||',
	);

	/**
	 * @static
	 */
	function &getLog()
	{
		return Logger::getLogger('phase.support.ELExpression');
	}

	function ELExpression($expression)
	{
		$this->expression = $expression;
	}

	function &evaluate()
	{
		$reader =& new StringReader($this->expression);
		$scanner =& new Scanner($reader);
		$openBracket = false;

		$buffer = '';
		while ($scanner->hasMoreInput())
		{
			$ch = $scanner->nextChar();
			$chOrd = ord($ch);
			// [A-Za-z_]
			if (($chOrd >= 66 && $chOrd <= 90) || ($chOrd >= 97 && $chOrd <= 122) || $chOrd == 95)
			{
				$var = $ch;
				while($scanner->hasMoreInput())
				{
					// @todo use ord here to spead things up
					if (!preg_match('/[a-zA-Z_0-9\.\[\]\'"]/', $scanner->peekChar()))
					{
						break;
					}

					$var .= $scanner->nextChar();
				}

				// @todo this would be better as a switch to allow fall through

				// if we find empty, there cannot be anything else in the expression besides
				// '!' (or 'not'), 'empty' and the variable
				if ($var == 'empty')
				{
					$buffer .= 'is_empty(';
					$scanner->skipSpaces();
					$openBracket = true;
				}
				// if the word is 'null' and it is butted against a sensible break, interpret as null value
				elseif ($var == 'null' && ($scanner->peekChar() == ' ' || $scanner->peekChar() == ')'))
				{
					$buffer .= 'null';
				}
				// @fixme not allowing for 'not' to preceed an open bracket
				elseif (isset($this->operators[$var]) && $scanner->peekChar() == ' ')
				{
					$buffer .= $this->operators[$var];
				}
				else
				{
					$buffer .= 'VariableResolver::resolveVariable(' . StringUtils::quote($var) . ', $pageContext)';
					if ($openBracket)
					{
						$buffer .= ')';
						$openBracket = false;
					}
				}
			}
			else if ($ch == '\'' || $ch == '\"')
			{
				$start = $scanner->mark();
				$end = $scanner->skipUntil($ch);
				$buffer .= $ch . $scanner->getChars($start, $end) . $ch;
			}
			else
			{
				$buffer .= $ch;
			}
		}

		return $buffer;
	}
}
?>
