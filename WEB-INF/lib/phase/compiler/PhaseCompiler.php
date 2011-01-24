<?php
/* $Id: PhaseCompiler.php 370 2006-10-17 05:19:38Z mojavelinux $
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
import('horizon.io.FileWriter');
import('phase.compiler.PhaseParser');
import('phase.compiler.PhaseReader');
import('horizon.util.logging.Logger');

/**
 * @author Dan Allen <dan.allen@mojavelinux.com>
 * @package phase.runtime
 * @access public
 */
class PhaseCompiler extends Object
{
	var $psw = null;

	var $ctxt = null;

	/**
	 * Get the logger for this class.
	 *
	 * @access private
	 * @return Logger
	 */
	function &getLog()
	{
		$logger =& Logger::getLogger('phase.compiler.PhaseCompiler');
		return $logger;
	}

	/**
	 * @param PhaseCompilationContext $ctxt
	 * @param PhaseServletWrapper $psw
	 */
	function PhaseCompiler(&$ctxt, &$psw)
	{
		$this->psw =& $psw;
		$this->ctxt =& $ctxt;
	}

	function compile()
	{
		$log =& $this->getLog();
		if ($log->isLoggable('DEBUG')) {
			$log->debug('Running phase compiler on source ' . $this->ctxt->getPhaseFile());
		}

		// NOTE: the PhaseReader will close the input stream when it is done with it
		$is =& $this->ctxt->getResourceAsStream($this->ctxt->getPhaseFile());
		// make sure the file could be opened for reading
		if (bubble_exception()) { return; }
		$reader =& new PhaseReader($is);
		$parsedPage = PhaseParser::parse($reader, $this->ctxt);
		
		if ($log->isLoggable('DEBUG')) {
			$log->debug('Writing compiled source ' . $this->ctxt->getCompiledFileName() . ' with ' . strlen($parsedPage) . ' bytes');
		}
		// try {
		$os =& new FileWriter(new File($this->ctxt->getCompiledFileName()));
		$os->write($parsedPage);
		$os->close();
		// } catch (IOException $e) {
		if ($e = catch_exception('IOException'))
		{
			throw_exception(new PhaseException('Could not compile PSP page: ' . $this->ctxt->getPhaseFile(), $e));
		}
		// }
	}

	function isOutDated()
	{
		$phaseFile = $this->ctxt->getRealPath($this->ctxt->getPhaseFile());
		$compiledFile = $this->ctxt->getCompiledFileName();
		if (!file_exists($compiledFile))
		{
			return true;
		}
		elseif (filemtime($phaseFile) > filemtime($compiledFile))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>
