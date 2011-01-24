<?php
/* $Id: PhaseCompilationContext.php 370 2006-10-17 05:19:38Z mojavelinux $
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

import('phase.compiler.PhaseCompiler');
import('phase.PhaseException');

/**
 * @package phase
 * @author Dan Allen
 */
class PhaseCompilationContext extends Object
{
	var $phaseUri = null;

	var $options = null;

	var $phaseWrapper = null;

	/**
	 * @var ServletContext
	 */
	var $context = null;

	var $phaseCompiler = null;

	/**
	 * @param string $phaseUri
	 * @param 
	 * @param ServletContext $context
	 * @param PhaseServletWrapper $phaseWrapper
	 */
	function PhaseCompilationContext($phaseUri, &$options, &$context, &$phaseWrapper)
	{
		$this->phaseUri = $phaseUri;
		$this->options =& $options;
		$this->phaseWrapper =& $phaseWrapper;
		$this->context =& $context;
	}

	function createOutdir()
	{
		// NOTE: stored under "javax.servlet.context.tempdir" which is the same as the work directory
		$scratchDir =& $this->options->getScratchDir();
		$outUri = $scratchDir->getPath();
		if (substr($outUri, -1) == '/')
		{
			$outUri = $outUri . substr($this->phaseUri, 1);
		}
		else
		{
			$outUri = $outUri . $this->phaseUri;
		}

		$outUri = substr($outUri, 0, strrpos($outUri, '/'));

		$outDirFile = new File($outUri);
		if (!$outDirFile->exists())
		{
			// try {
			// NOTE: the permissions will inherit from the umask() setting
			$outDirFile->mkdirs();
			// } catch (IOException $e) {
			if ($e = catch_exception('IOException'))
			{
				// if the directory cannot be created, there is nothing we can do at this point, so tell the user
				throw_exception(new PhaseException('Work directory must exist and be writable: ' . $outUri));
				return;
			}
			// }
		}

		$this->setOutputDir($outUri . DIRECTORY_SEPARATOR);		
	}

	function setOutputDir($dir)
	{
		$this->outputDir = $dir;
	}

	function getOutputDir()
	{
		return $this->outputDir;
	}

	function load()
	{
		return $this->getCompiledFileName();
	}

	function compile()
	{
		// make sure our target directory exists
		$this->createOutdir();	
		$this->createCompiler();
		if ($this->phaseCompiler->isOutDated())
		{
			$this->phaseCompiler->compile();
			$this->phaseWrapper->setReload(true);
		}
	}

	function &createCompiler()
	{
		if (is_null($this->phaseCompiler))
		{
			$this->phaseCompiler =& new PhaseCompiler($this, $this->phaseWrapper);
		}
		
		return $this->phaseCompiler;	
	}

	/**
	 * Regardless of the extension used for phase files, the resulting file is
	 * a regular php file.
	 */
	function getCompiledFileName()
	{
		$filename = basename($this->phaseUri);
		return $this->outputDir . substr($filename, 0, strrpos($filename, '.')) . '.php';
	}

	function getPhaseFile()
	{
		return $this->phaseUri;
	}

	function getResourcePaths($res)
	{
		return $this->context->getResourcePaths($res);
	}

	function &getResourceAsStream($res)
	{
		$s =& $this->context->getResourceAsStream($res);
		return $s;
	}

	function getRealPath($path)
	{
		return $this->context->getRealPath($path);
	}

	function &getServletContext()
	{
		return $this->context;
	}
}
?>
