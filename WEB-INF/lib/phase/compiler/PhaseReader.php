<?php
/* $Id: PhaseReader.php 324 2006-03-11 05:42:59Z mojavelinux $
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

/**
 * @package phase.compiler
 * @author Dan Allen
 *
 * TODO: perhaps try to keep line number and file for debugging, error purposes
 * TODO: try to make any method more efficient that is possible
 */
class PhaseReader extends Scanner
{
	function PhaseReader(&$inputStream)
	{
		parent::Scanner($inputStream);
	}

	/**
	 * Gets the content until the next potential taglib element.  We just search
	 * for &lt; since it is this char that begins every taglib
	 *
	 * @return string
	 */
	function nextContent()
	{
		$content = $this->peekChar();
		while (++$this->cursor < strlen($this->stream) && ($ch = $this->peekChar()) != '<')
		{
			$content .= $ch;
		}

		return $content;
	}

	/**
	 * @return boolean
	 */
	function matchesEndTag($tagname)
	{
		$mark = $this->mark();
		if (!$this->matches('</' . $tagname))
		{
			return false;
		}

		$this->skipSpaces();
		if ($this->nextChar() == '>')
		{
			return true;
		}

		$this->reset($mark);
		return false;
	}

	/**
	 * @return int
	 */
	function skipUntilEndTag($tagname)
	{
		$ret = $this->skipUntil('</' . $tagname);
		if (!is_null($ret))
		{
			$this->skipSpaces();
			if ($this->nextChar() != '>')
			{
				$ret = null;
			}
		}

		return $ret;
	}

	/**
	 * Get the next token available based on the values specified as delimiters
	 *
	 * @return string
	 */
	function parseToken($quoted)
	{
		$buffer = '';
		$this->skipSpaces();
		$ch = $this->peekChar();
		if ($quoted)
		{
			if ($ch == '\'' || $ch == '"')
			{
				$start = $this->mark();
				$this->skipUntil($ch);
				$end = $this->mark();
				// @todo throw exception if end not found
				$buffer .= $this->getChars($start, $end);
			}
		}
		else
		{
			if (!$this->isDelimiter())
			{
				// read value until delimiter is found
				do
				{
					$ch = $this->nextChar();
					// take care of quoting
					if ($ch == '\\')
					{
						$nextChar = $this->peekChar();
						if ($nextChar == '"' || $nextChar == '\'' || $nextChar == '>' || $nextChar == '%')
						{
							$ch = $this->nextChar();
						}
					}

					// HACK: PHP has some NASTY reference bugs and sometimes just touching the
					// variable brings it back into orbit...so we just do that here...
					// TODO: might be able to remove this, bug was discovered to be in the Zend Optimizer
					ref($this->cursor . ', ' . $ch);
					$buffer .= $ch;
				}
				while (!$this->isDelimiter());
			}
		}

		return $buffer;
	}

	/**
	 * Determine if the next character is a delimiter without advancing the cursor.
	 *
	 * @return boolean
	 */
	function isDelimiter()
	{
		if (!$this->isSpace())
		{
			$ch = $this->peekChar();	
			// look for a single char work delimiter
			if ($ch == '=' || $ch == '>' || $ch == '"' || $ch == '\'' || $ch == '/')
			{
				return true;
			}
			// look for end of comment tag
			if ($ch == '-')
			{
				$mark = $this->mark();
				if (($ch = $this->nextChar()) == '>' || ($ch == '-' && $this->nextChar() == '>'))
				{
					$this->reset($mark);
					return true;
				}
				else
				{
					$this->reset($mark);
					return false;
				}
			}
			
			return false;
		}
		else
		{
			return true;
		}
	}
}
?>
