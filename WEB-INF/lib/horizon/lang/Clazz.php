<?php
/* $Id: Clazz.php 370 2006-10-17 05:19:38Z mojavelinux $
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
import('horizon.io.File');
import('horizon.io.FileReader');
import('horizon.util.StringUtils');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.lang
 * @access public
 * @final
 */
class Clazz extends Object
{
	/**
	 * An instance of the underlying class represented by this {@link Clazz}.
	 *
	 * @var object
	 */
	var $instance = null;

	/**
	 * If the intialize flag is set in forName(), an instance can be prepared, but
	 * can only be used once.
	 * @var boolean
	 */
	var $preloaded = false;

	/**
	 * Fully qualified name of this class, including package name
	 *
	 * @var string
	 */
	var $qName = null;

	/**
	 * PHP name of this class, which, as of PHP4, is not equivalent
	 * to the qualified name
	 *
	 * @var string
	 */
	var $shortName = null;

	/**
	 * Construct a new {@link Clazz} instance for the fully qualified
	 * class name.
	 *
	 * @access public
	 */
	function Clazz($qName)
	{
		$this->qName = $qName;
		$dot = strrpos($qName, '.');
		$dot = ($dot !== false) ? $dot + 1 : 0;
		$this->shortName = strtolower(substr($qName, $dot));
		$this->instance = null;
	}

	/**
	 * Returns the Class object associated with the class or interface with the
	 * given string name.  When this method is called, an import will be attempted
	 * on the requested class to determine if it exists, regardless of the initialize
	 * flag.
	 *
	 * @param string $qName Fully qualified name of the class
	 * @param boolean $initialize (optional) Optionally load and initialize this class
	 *
	 * @return Clazz
	 * @throws ClassNotFoundException
	 */
	function &forName($qName, $initialize = true)
	{
		// if there is an outstanding exception, we don't want to falsify it
		$nil =& ref(null);
		if (bubble_exception()) return $nil;

		// try {
			import($qName);
		// } catch (RootException $e) {
		if ($e = catch_exception())
		{
			throw_exception(new ClassNotFoundException('Could not locate ' . $qName, $e));
			return $nil;
		}
		// }

		$clazz =& new Clazz($qName);
		if ($initialize)
		{
			$clazz->instance =& $clazz->newInstance();
			$clazz->preloaded = true;
		}

		return $clazz;
	}

	/**
	 * Get the fully qualified java naming syntax for an object.  This function
	 * works in reverse of {@link import()}, as it tries to figure out what the
	 * package from the included file list.
	 *
	 * <b>IMPLEMENTATION NOTE</b>: Until namespaces in PHP are supported, no
	 * two classes should have the same name from any package or a potential
	 * conflict could occur!  It will not occur if the two classes are never
	 * used in the same script, but PHP cannot distinguish between package
	 * names if the names are identical.  Include paths should also not be
	 * nested, and there is a potential conflict if the end of the include path
	 * crosses with the beginning of a package name.
	 *
	 * @param mixed $obj Either an object or a string name
	 * @return string
	 */
	function getQualifiedName(&$obj)
	{
		static $cache = array();
		static $paths = null;

		// get the normalized class name (normalized by case)
		$className = strtolower(is_object($obj) ? get_class($obj) : $obj);

		if (is_null($paths))
		{
			$paths = get_include_paths();
		}

		if (!array_key_exists($className, $cache))
		{
			// we are optimizing for speed, so native function call here
			foreach (get_included_files() as $file)
			{
				$candidate = basename($file);
				// skip a filename that doesn't start with upper case (non-class)
				if (strtoupper($candidate[0]) != $candidate[0])
				{
					continue;
				}

				// remove the extension
				$candidateClass = strtolower(substr($candidate, 0, strrpos($candidate, '.')));

				if ($candidateClass == $className)
				{
					foreach ($paths as $dir)
					{
						if (strpos($file, $dir) === 0)
						{
							// we want to chop off the directory and extension
							// and then convert directory separators to periods
							$qualifiedClassName = str_replace(
								DIRECTORY_SEPARATOR,
								'.',
								substr($file, strlen($dir) + 1, strrpos($file, '.') - 1 - strlen($dir))
							);

							$cache[$className] = $qualifiedClassName;
							return $cache[$className];
						}
					}

					// QUESTION: at this point, do we stop or press on?
				}
			}
		}
		else
		{
			return $cache[$className];
		}
	}

	/**
	 * Return the PHP4 name of this class, which excludes the package
	 *
	 * @return string
	 */
	function getShortName()
	{
		return $this->shortName;
	}

	/**
	 * Get the fully qualified name of this class, including the package
	 *
	 * @return string
	 */
	function getName()
	{
		return $this->qName;
	}

	/**
	 * Returns the {@link Clazz} representing the superclass of the entity
	 * represented by the underlying class.
	 *
	 * @return Clazz
	 */
	function &getSuperclass()
	{
		$parent = Clazz::getQualifiedName(ref(get_parent_class($this->shortName)));
		if (is_null($parent))
		{
			return ref(null);
		}

		$instance =& new Clazz($parent);
		return $instance;
	}

	/**
	 * Create a new instance of this class with the default constructor if
	 * one has not already been created
	 *
	 * @return object
	 */
	function &newInstance()
	{
		if ($this->preloaded && !is_null($this->instance))
		{
			$this->preloaded = false;
			return $this->instance;
		}

		$clazz = $this->shortName;
		$instance =& new $clazz();
		return $instance;
	}

	/**
	 * Return an array of all public methods for this class (but not
	 * any of the inherited methods).
	 *
	 * @return array String array of all public methods
	 * TODO: cache the result in an instance variable!!
	 */
	function getDeclaredMethods()
	{
		$allMethods = get_class_methods($this->shortName);

		// only get the methods in this particular class
		if (($parent = get_parent_class($this->shortName)) != null)
		{
			$allMethods = array_diff($allMethods, get_class_methods($parent));
		}

		$publicMethods = array();	
		foreach ($allMethods as $method)
		{
			if ($method{0} != '_' && $method != $this->shortName)
			{
				$publicMethods[] = $method;
			}
		}

		return $publicMethods;
	}

	/**
	 * NOTE: technically the rules for searching should be as follows:
	 *  - if begins with a '/' search with no include paths (assume root directory of system)
	 *  - if begins without a '/' add package name (replacing . with /) and search includes
	 *  - bootstrap loader will not append package name and find in include path
	 *
	 *  - do not use \ in the path name to be loaded since this is not a real path but a naming schema
	 *    either / or . is acceptable, but it must begin with / to be considered absolute
	 *
	 * @param string $name
	 *
	 * @return Reader
	 */
	function &getResourceAsStream($name)
	{
		$file = null;

		// hack to get around lack of static-agnostic method call
		if (is_a($this, 'Clazz'))
		{
			$file =& $this->getResource($name);
		}
		else
		{
			$file =& Clazz::getResource($name);
		}

		if (is_null($file))
		{
			return ref(null);
		}

		$reader = null;

		if ($file->isFile())
		{
			$reader =& new FileReader($file);
		}
		elseif ($file->isDirectory())
		{
			die('directory reader not implemented');
			//$reader =& new DirectoryReader($file);
		}

		return $reader;
	}

	/**
	 * @return {@link File} The abstract represenation of this path
	 */
	function &getResource($name)
	{
		$file = null;

		// if this is absolute check if file exists and use it, no include path
		// NOTE: we look for the unix root and the windows drive roots
		// NOTE: could use realpath() in this case
		if ($name[0] == '/' || preg_match('/^[a-zA-Z]:(\\\|\/)/', $name))
		{
			// silently discard if file cannot be found
			if (@file_exists($name))
			{
				$file = $name;
			}
		}
		else
		{
			// if this is a valid instance of a class, then prepend the package
			// name as a directory tree
			if (is_a($this, 'Clazz'))
			{
				$name = str_replace('.', DIRECTORY_SEPARATOR, $this->getPackage()) . DIRECTORY_SEPARATOR . $name;
			}

			$paths = get_include_paths();
			foreach ($paths as $path)
			{
				if (@file_exists($path . DIRECTORY_SEPARATOR . $name))
				{
					$file = $path . DIRECTORY_SEPARATOR . $name;
					break;
				}
			}
		}

		$result =& new File($file);
		return $result;
	}

	/**
	 * Gets the package for this class.
	 *
	 * @return string
	 * @access public
	 */
	function getPackage()
	{
		$period = strrpos($this->qName, '.');
		if ($period === false)
		{
			return null;
		}

		return substr($this->qName, 0, $period);
	}

	/**
	 * Determine if the parent class provided (either string or class object) is
	 * an ancestor of our class.  This method is similar to is_a() except that
	 * it acts on the local class object and it can take either a string or an object
	 * as an argument.
	 *
	 * @param String|Clazz $parent The parent from which we are looking to inherit
	 */
	function isAssignableFrom($parent)
	{
		if (is_object($parent))
		{
			$parentName = strtolower($parent->getShortName());
		}
		else
		{
			$parentName = strtolower(StringUtils::substringAfterLast($parent, '.'));
		}

		$childName = $this->getShortName();

		while ($childName != $parentName)
		{
			// NOTE: PHP5 returns case sensitive names for the parent class,
			// while PHP <= 4 does not
			$childName = strtolower(get_parent_class($childName));
			if (!$childName) return false;
		}

		return true;
	}
}
?>
