<?php
/* $Id: File.php 352 2006-05-15 04:27:35Z mojavelinux $
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
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package horizon.io
 * @access public
 *
 * @note The underlying pathname may need to be absolute so that we don't have relative path problems
 */
class File extends Object
{
	var $pathname = null;

	function File($pathname)
	{
		$this->pathname = $pathname;
	}

	function getName()
	{
		return basename($this->pathname);
	}

	function getPath()
	{
		return $this->pathname;
	}

	/**
	 * Returns the pathname string of this abstract pathname's parent, or null
	 * if this pathname does not name a parent directory.
	 *
	 * The parent of an abstract pathname consists of the pathname's prefix, if any,
	 * and each name in the pathname's name sequence except for the last. If the
	 * name sequence is empty then the pathname does not name a parent directory.  
	 *
	 * @return string
	 */
	function getParent()
	{
		return dirname($this->pathname);
	}

	/**
	 * Returns the abstract pathname of this abstract pathname's parent, or
	 * null if this pathname does not name a parent directory.
	 *
	 * The parent of an abstract pathname consists of the pathname's prefix, if
	 * any, and each name in the pathname's name sequence except for the last.
	 * If the name sequence is empty then the pathname does not name a parent
	 * directory. 
	 *
	 * @return File
	 */
	function &getParentFile()
	{
		$file =& new File($this->getParent());
		return $file;
	}

	/**
	 * Tests whether the file denoted by this abstract pathname exists.
	 *
	 * @return boolean
	 */
	function exists()
	{
		return @file_exists($this->pathname);
	}

	/**
	 * Tests whether the application can write the file denoted by this path.
	 *
	 * @return boolean
	 */
	function canWrite()
	{
		return @is_writable($this->pathname);
	}

	/**
	 * Tests whether the application can read the file denoted by this path.
	 *
	 * @return boolean
	 */
	function canRead()
	{
		return @is_readable($this->pathname);
	}

	// TODO: we need to check the last modified time against the time when
	// this object was created an if the file on disk is newer we need to
	// clearstatcache()
	function length()
	{
		if (!is_file($this->pathname))
		{
			return null;
		}

		return filesize($this->pathname);
	}

	/**
	 * Creates the directory named by this abstract pathname.  The pathname
	 * is assumed to represent a directory and will be treated as such.  If
	 * the directory cannot be created because of an invalid request (the parent
	 * directory doesn't exist or a file is in the way), <kbd>false</kbd> will
	 * be returned.  If the the directory cannot be created because of a permissions
	 * error, then a {@link SecurityException} will be thrown.  Otherwise, the
	 * directory will be created successfully and <kbd>true</kbd> will be returned.
	 *
	 * @param mixed $perms (optional) Optional permissions to apply to created
	 * directory
	 *
	 * @return boolean Whether or not the directory had to be created
	 */
	function mkdir($perms = null)
	{
		// refuse to create directory if it exists, a file is in the way, or
		// the parent directory does not exist
		if (@file_exists($this->pathname) || !@is_dir($this->getParent()))
		{
			return false;
		}

		// if this fails, it means we don't have permission to do it, so we silence the
		// error and throw a manual exception
		if (!@mkdir($this->pathname))
		{
			throw_exception(new SecurityException('Permission to create directory denied.'));
			return false;
		}

		if (!is_null($perms))
		{
			// perhaps consider current umask?
			// @fixme no error checking is done here
			chmod($this->pathname, $perms);
		}

		return true;
	}

	/**
	 * Creates the directory named by this abstract pathname, including any
	 * necessary but nonexistent parent directories. Note that if this
	 * operation fails it may have succeeded in creating some of the necessary
	 * parent directories.  Using this function makes the assumption that the
	 * abstract pathname is a directory.
	 *
	 * @param mixed $perms (optional) Optional permissions to apply to each
	 * created directory
	 * @throws IOException if directories cannot be created successfully
	 *
	 * @return boolean Whether or not the directory had to be created
	 */
	function mkdirs($perms = null)
	{
		if (!@file_exists($this->getParent()))
		{
			$parentFile =& $this->getParentFile();
			// NOTE: this will throw an exception if not possible, let it propogate
			$parentFile->mkdirs($perms);
		}
		else if (!@is_dir($this->getParent()))
		{
			return false;
		}

		return $this->mkdir($perms);
	}

	/**
	 * Deletes the file or directory denoted by this abstract pathname.
	 *
	 * Only try to delete the file or directory if it exists.
	 * NOTE: If this pathname denotes a directory, then the directory must be empty
	 * in order to be deleted.
	 *
	 * @return boolean Whether or not the file was deleted.
	 */
	function delete()
	{
		if (!$this->exists())
		{
			return false;
		}

		$success = false;

		// try {

		if ($this->isFile())
		{
			$success = unlink($this->pathname);
		}
		else if ($this->isDirectory())
		{
			$success = rmdir($this->pathname);
		}

		// } catch (RootException $e) {
		if ($e = catch_exception())
		{
			throw_exception(new SecurityException('No permission to delete file.'));
		}
		
		return $success;
	}

	/**
	 * Tests whether the file denoted by this abstract pathname is a directory.
	 *
	 * @return boolean
	 */
	function isDirectory()
	{
		return is_dir($this->pathname);
	}

	/**
	 * Tests whether the file denoted by this abstract pathname is a normal
	 * file.
	 *
	 * @return boolean
	 */
	function isFile()
	{
		return is_file($this->pathname);
	}

	/**
	 * Returns the time that the file denoted by this abstract pathname was
	 * last modified (in milliseconds since the Unix Epoch).  Returns 0 if
	 * file does not exist or cannot be read.
	 *
	 * @return int time the file was last modified.
	 */
	function lastModified()
	{
		if ($this->exists() && $this->canRead())
		{
			return @filemtime($this->pathname);
		}
		
		return 0;
	}

	/**
	 * @note we cannot use list() as a method name since it is reserved, so listPaths() is used instead
	 */
	function listFileNames()
	{
		if (!$this->isDirectory())
		{
			return null;
		}

		$names = array();

		// try {
		$dh = opendir($this->pathname);
		while (($filename = readdir($dh)) !== false)
		{
			if ($filename == '.' || $filename == '..')
			{
				continue;
			}

			$names[] = $filename;
		}

		closedir($dh);
		// } catch (RootException $ex) {
		// }

		return $names;
	}

	/**
	 * Returns the pathname string of this abstract pathname.
	 * The output of this method is equivalent to the return value of
	 * the {@link getPath()} method.
	 *
	 * @return string
	 */
	function toString()
	{
		return $this->getPath();
	}
}
?>
