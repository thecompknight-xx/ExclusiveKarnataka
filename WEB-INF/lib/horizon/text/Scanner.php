<?php
/* $Id: Scanner.php 188 2005-04-07 04:52:31Z mojavelinux $
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

/**
 * @package horizon.text
 * @author Dan Allen
 */
class Scanner
{
	/**
	 * Holds the string which is being processed
	 * @var string
	 * @access protected
	 */
	var $stream = null;

	/**
	 * Keeps track of the internal pointer on the stream
	 * @var int
	 * @access protected
	 */
	var $cursor = null;

	/**
	 * NOTE: the constructor closes the input stream when done
	 */
	function Scanner(&$inputStream)
	{
		// read the entire contents of the stream and close it
		$inputStream->read($this->stream);	
		$inputStream->close();
		$this->cursor = 0;
	}

	/**
	 * Determine if more input exists in our stream so that we don't
	 * read off the end of the internal stream.
	 *
	 * @return boolean
	 */
	function hasMoreInput()
	{
		return $this->cursor < strlen($this->stream);
	}

	/**
	 * Read the next char under the cursor in the internal stream and
	 * advance the internal cursor by one character.
	 *
	 * @return string
	 */
	function nextChar()
	{
		if (!$this->hasMoreInput())
		{
			return false;
		}

		return $this->stream[$this->cursor++];
	}

	/**
	 * Determine if the string specified aligns with the stream starting from
	 * the cursor's current location up to the length of that specified string.
	 * If the two strings align, the cursor position is placed after the search
	 * string match, otherwise the stream is unchanged.
	 *
	 * @param string $string The string to match
	 * @param boolean $ignoreCase (optional) Whether to ignore case when matching
	 *
	 * @access public
	 * @return boolean
	 */
	function matches($string, $ignoreCase = false)
	{
		$strlen = strlen($string);
		$substr = $this->peekChar($strlen);
		if ($ignoreCase)
		{
			$string = strtolower($string);
			$substr = strtolower($substr);
		}

		if ($substr === $string)
		{
			$this->advance($strlen);
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Advance in the current stream until a non-space character
	 * is found.  Return the number of characters skipped in the process.
	 *
	 * @return int
	 */
	function skipSpaces()
	{
		$i = 0;
		while ($this->isSpace())
		{
			$i++;
			$this->nextChar();
		}

		return $i;
	}

	/**
	 * Skip until the given string is matched in the stream.  Position
	 * the cursor just after the match if found and return the position
	 * just before the match.  If the ignore escape flag is off,
	 * characters prefixed with a \ will not be considered to be a match (such
	 * as in a quoted string ).
	 *
	 * NOTE: the string we are looking for can be multiple characters, but
	 * the literal boundaries must be only a single char (i.e. the quotes)
	 *
	 * @param string $string String to be matched
	 * @param boolean $ignoreEsc (optional) Whether to ignore escaped matches
	 * @param array $enclosures (optional) Strings which can act as enclosures, i.e. quotes
	 *
	 * @return int
	 */
	function skipUntil($string, $ignoreEsc = false, $enclosures = array('\'', '"'))
	{
		$curr = '';
		$prev = '';

		while ($this->hasMoreInput())
		{
			$curr = $this->peekChar();

			if ($string{0} == $curr && ($ignoreEsc || $prev != '\\'))
			{
				if ($this->matches($string))
				{
					return $this->cursor - strlen($string);
				}
			}
			else if (!empty($enclosures) && in_array($curr, $enclosures))
			{
				$this->skipUntil($this->nextChar());
				$prev = '';
				continue;
			}

			$prev = $curr;
			$this->nextChar();
		}

		return null;
	}

	/**
	 * Determine if the next character to be read is a space character (empty
	 * string does not count as a valid space characters and false will be
	 * returned). Do not advance in the process of determining this
	 * characteristic.
	 *
	 * @return boolean
	 */
	function isSpace()
	{
		$ch = $this->peekChar();
		return $ch != '' && $ch <= ' ';
	}

	/**
	 * Peek the next character to be read in the internal stream.  If
	 * the cursor is already at the end of the stream, false is returned
	 *
	 * @return string (or boolean false if EOS)
	 */
	function peekChar($cnt = 1)
	{
		return substr($this->stream, $this->cursor, $cnt);
	}

	/**
	 * Pull out a section from the stream and return it.
	 *
	 * @return string
	 */
	function getChars($start, $stop)
	{
		return substr($this->stream, $start, $stop - $start);
	}

	/**
	 * Advance the cursor the set number of characters.  If the
	 * number of characters specified pushes us off the end of the
	 * stream, place the cursor just after the end of the stream
	 *
	 * @param int $length Number of characters to advance
	 *
	 * @return void
	 * @access public
	 */
	function advance($n)
	{
		$this->cursor = min($this->cursor + $n, strlen($this->stream));
	}

	/**
	 * Get the current position of the cursor in the stream.
	 *
	 * @return int
	 */
	function mark()
	{
		return $this->cursor;
	}

	/**
	 * Reset the cursor to the marked position in the stream.
	 *
	 * @return void
	 */
	function reset($mark = 0)
	{
		$this->cursor = $mark;
	}

	/**
	 * Push the cursor to the end of the stream so that processing stops.
	 *
	 * @return void
	 */
	function end()
	{
		$this->cursor = strlen($this->stream);
	}
}
?>
