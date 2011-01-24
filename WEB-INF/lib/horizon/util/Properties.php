<?php
/* $Id: Properties.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('horizon.collections.HashMap');

/**
 * The <b>Properties</b> class represents a persistent set of key-value properties.
 * 
 * This class can either be build procedurally or loaded from an IO stream.
 * Each key and its corresponding value in the property list is a string.
 *
 * A property list can contain another property list as its {@link defaults};
 * this second property list is searched if the property key is not found in
 * the original property list.
 *
 * Since {@link Properties} extends {@link HashMap}, there are no references
 * in the returned methods.  In the java implementation this is done using an
 * Enumeration, but we can just kill all the & signs for the same effect here.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @see java.util.Properties
 * @package horizon.util
 */
class Properties extends HashMap
{
	/**
	 * @var Properties
	 * @access protected
	 */
	var $defaults;

	/**
	 * @var int
	 * @access private
	 */
	var $_buffer = 4096;

	/**
	 * Creates an empty property list with no default values.
	 */
	function Properties()
	{
		// sanity check for buffer
		if ($this->_buffer < 256)
		{
			$this->_buffer = 256;
		}

		$this->defaults = null;
		parent::HashMap();
	}

	/**
	 * Since PHP can't have overloaded constructors to pass values optionally by
	 * reference (grrrrrrrrr) I added a new function which will set the defaults
	 * after the instance of properties had been created
	 *
	 * @param Properties $properties The default properties to be used
	 * @return void
	 */
	function setDefaults(&$properties)
	{
		$this->defaults =& $properties;
	}

	/**
	 * Searches for the property with the specified key in this property list.
	 * If the key is not found in this property list, the default property
	 * list, and its defaults, recursively, are then checked. The method
	 * returns null if the property is not found.
	 *
	 * NOTE: we don't return by reference since a change to the result should
	 * not then change the internal value, they are no longer linked in this way
	 *
	 * @param string $key
	 * @param string $defaultValue
	 * @return string
	 */
	function getProperty($key, $defaultValue = null)
	{
		if (!$this->containsKey($key))
		{
			if (!is_null($this->defaults))
			{
				return $this->defaults->getProperty($key, $defaultValue);
			}
			else
			{
				return (string) $defaultValue;
			}
		}

		$value = $this->get($key);
		return (string) $value;
	}

	/**
	 * Sets the value for the specified property.  Enforces use of strings for
	 * property keys and values.
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	function setProperty($key, $value)
	{
		$this->put($key, (string) $value);
	}
	
	/**
	 * Returns an array of all the keys in this property list, including
	 * distinct keys in the default property list if a key of the same name has
	 * not already been found from the main properties list.
	 *
	 */
	function propertyNames()
	{
		if (is_null($this->defaults))
		{
			return $this->keys();
		}
		else
		{
			return array_merge($this->keys(), $this->defaults->propertyNames());
		}
	}

	/**
	 * Reads a property list (key and element pairs) from the input stream.
	 *
	 * Every property occupies one line of the input stream. Each line is
	 * terminated by a line terminator (\n or \r or \r\n). Lines from the input
	 * stream are processed until end of file is reached on the input stream.
	 *
	 * A line that contains only whitespace or whose first non-whitespace
	 * character is an ASCII # or ! is ignored (thus, # or ! indicate comment
	 * lines).
	 *
	 * Every line other than a blank line or a comment line describes one
	 * property to be added to the table (except that if a line ends with \,
	 * then the following line, if it exists, is treated as a continuation
	 * line, as described below). The key consists of all the characters in the
	 * line starting with the first non-whitespace character and up to, but not
	 * including, the first ASCII =, :, or whitespace character. All of the key
	 * termination characters may be included in the key by preceding them with
	 * a \. Any whitespace after the key is skipped; if the first
	 * non-whitespace character after the key is = or :, then it is ignored and
	 * any whitespace characters after it are also skipped. All remaining
	 * characters on the line become part of the associated element string.
	 * Within the element string, the ASCII escape sequences \t, \n, \r, \\,
	 * \", \', \ (a backslash and a space), and \uxxxx are recognized and
	 * converted to single characters. Moreover, if the last character on the
	 * line is \, then the next line is treated as a continuation of the
	 * current line; the \ and line terminator are simply discarded, and any
	 * leading whitespace characters on the continuation line are also
	 * discarded and are not part of the element string.
	 *
	 * @param Reader $input An object that implementes the reader interface
	 * @return void
	 * @throws IOException
	 */
	function load(&$input)
	{
		$data = '';
		$input->read($data);

		// first replace pesky CF+LF endlines with LF endlines
		$data = str_replace("\r\n", "\n", $data);

		// connect lines broken with a \
		$data = preg_replace(';\\\ *(\r|\n) *;', '', $data);
		$lines = preg_split(';(\r|\n);', $data);

		foreach ($lines as $line)
		{
			$key = '';
			$value = '';
			$index = 0;
			$len = strlen($line);
			$whitespaceDelimiter = false;
			while (true)
			{
				if ($index == $len)
				{
					break;
				}
			
				$char = $line[$index];
			
				if ($key == '' && ($char == ' ' || $char == "\t"))
				{
					; // ignore character
				}
				elseif ($key == '' && ($char == '#' || $char == '!'))
				{
					// skip this line, it is a comment
					break;
				}
				else
				{
					// check for escaped delimiter in key
					if ($char == '=' || $char == ':' || $char == ' ' || $char == "\t")
					{
						if ($index > 0)
						{
							if ($line[$index - 1] != '\\')
							{
								if ($char == ' ' || $char == "\t")
								{
									$whitespaceDelimiter = true;
								}

								break;
							}
			
							$key = substr($key, 0, -1);
						}
					}
			
					$key .= $char;
				}
			
				$index++;
			}
			
			if ($key != '')
			{
				if ($index + 1 < $len)
				{
					$value = str_replace(array('\n', '\r', '\t', '\\\\', '\"', '\\\'', '\ '), array("\n", "\r", "\t", '\\', '"', '\'', ' '), ltrim(substr($line, $index + 1)));
				}
				
				if ($whitespaceDelimiter && $value[0] == ':' || $value[0] == '=')
				{
					$value = ltrim(substr($value, 1));
				}

				$this->put($key, $value);
			}
		}
	}

	/**
	 * @throws IOException
	 */
	function store(&$output, $header = null)
	{
		die('Method <b>store</b> not implemented in class <b>Properties</b>');
	}
}
?>
