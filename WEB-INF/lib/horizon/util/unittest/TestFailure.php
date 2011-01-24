<?php
/* $Id: TestFailure.php 306 2005-07-21 04:14:42Z mojavelinux $
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
 * <p>Special thanks goes to Kent Beck and Erich Gamma for writing JUnit,
 * the inspiration behind this unit test implementation.</p>
 */
class TestFailure extends Object
{
	var $failedTest;

	var $thrownException;

	function TestFailure(&$failedTest, &$thrownException)
	{
		$this->failedTest =& $failedTest;
		$this->thrownException =& $thrownException;
	}

	function &getFailedTest()
	{
		return $this->failedTest;
	}

	function &getThrownException()
	{
		return $this->thrownException;
	}

	function isFailure()
	{
		return is_a($this->thrownException, 'AssertionFailedException');
	}

	function toString()
	{
		return $this->failedTest->getName() . ': ' . $this->thrownException->getStackTrace();
	}
}
?>
