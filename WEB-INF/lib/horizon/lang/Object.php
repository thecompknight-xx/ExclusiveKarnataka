<?php
/* $Id: Object.php 318 2005-07-31 13:24:48Z mojavelinux $
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
 * Object is the root of the class hierarchy.
 *
 * Every class has Object as a superclass. All objects inherently implement the
 * methods of this class.
 *
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.lang
 */
class Object
{
	// simulate clone() in a compatible way
	function makeCopy()
	{
		return $this;
	}

	function equals(&$o)
	{
		return $this === $o;
	}

	function &getClass()
	{
		return Clazz::forName(Clazz::getQualifiedName($this), false);
	}

	/**
	 * Returns the class name of this object.  This is a convenience
	 * method which wraps getClass().getName()
	 *
	 * @return string
	 */
	function getClassName()
	{
		return Clazz::getQualifiedName($this);
	}

	/**
	 * For whatever its worth, this method will return the PHP provided
	 * class name for an object.
	 */
	function getPhpClassName()
	{
		return get_class($this);
	}

	function hashCode()
	{
		return md5(serialize($this));
	}

	/**
	 * Give a string representation of the object.
	 *
	 * @return string
	 */
	function toString()
	{
		ob_start();
		var_dump($this);
		return ob_get_clean();
	}

	/**
	 * Determine if the current object is an instance of
	 * the specified class.
	 *
	 * @param className case-insensitive class name
	 *
	 * @return boolean Whether the current object is an instance of the class name
	 */
	function instanceOfClass($className)
	{
		return is_a($this, $className);
	}

	/**
	 * Now the write/readObject methods allow a channel for the object to be
	 * serialized.  It does not however mean that it will get written.  If the
	 * __sleep() method returns an empty array the object will be a default
	 * instance when it comes back up.  Different from java however, it is necessary
	 * for the parent object to disable serializing child references or else what
	 * will happen is the object will still be in place, but it will be a new instance
	 * called with an empty constructor.
	 */
	function writeObject(&$objectWriter)
	{
		if (!$objectWriter->ready())
		{
			return null;
		}

		$output = '';
		$serializedObject = serialize(&$this);
		// include only necessary classes to rebuild serialized object to minimize
		// the size of the serialized file and the time it takes to read it back in
		foreach (get_included_paths(1) as $path)
		{
			$classname = strtolower(substr(basename($path), 0, strrpos(basename($path), '.')));
			// NOTE: PHP5 uses case sensitive names, where PHP <= 4 uses lowercase!
			if (preg_match(';O:[0-9]+:"' . $classname . '":[0-9]+:{;i', $serializedObject))
			{
				$output .= 'require_once \'' . str_replace('\'', '\\\'', $path) . '\'; ' . "\n";
			}
		}

		$output .= 'return unserialize(\'' . str_replace('\'', '\\\'', $serializedObject) . '\');';
		$objectWriter->write($output);
	}

	/**
	 * TODO: perhaps readObject and writeObject should be moved to a class
	 * Serializer or something like that (Java uses ObjectOutputStream and
	 * ObjectInputStream)
	 */
	function &readObject(&$objectReader)
	{
		$serializedObject = '';
		$objectReader->read($serializedObject);
		$object = @eval($serializedObject);
		return $object;
	}
}
?>
