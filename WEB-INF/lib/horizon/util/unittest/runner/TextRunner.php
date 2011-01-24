<?php
/* $Id: TextRunner.php 306 2005-07-21 04:14:42Z mojavelinux $
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

import('horizon.util.unittest.TestResult');

/**
 * <p>Special thanks goes to Kent Beck and Erich Gamma for writing JUnit,
 * the inspiration behind this unit test implementation.</p>
 */
class TextRunner extends Object
{
	function main($args)
	{
		if (!isset($args[1]))
		{
			die('Usage: You must supply a classname that implements TestCase or TestSuite to begin the test.' . "\n");
		}

		$clazz =& Clazz::forName($args[1]);

		if ($e = catch_exception()) {
			echo 'Could not execute unit tests.  Error report follows.' . "\n";
			$e->printStackTrace();
			die();
		}

		$test =& $clazz->newInstance();
		$suite =& $test->suite();
		$result =& new TestResult();
		$suite->run($result);

		$tests = $suite->countTestCases();
		$errors = $result->getErrorCount();
		$failures = $result->getFailureCount();
		$testsPassed = $tests - $errors - $failures;
		echo 'Runs: ' . $testsPassed . '/' . $tests . "\t" . 'Errors: ' . $errors . "\t" . 'Failures: ' . $failures . "\n";
		if ($errors)
		{
			echo 'Errors: ' . "\n";
			foreach ($result->getErrors() as $error)
			{
				echo "\t" . $error->toString() . "\n";
			}
		}

		if ($failures)
		{
			echo 'Failures: ' . "\n";
			foreach ($result->getFailures() as $error)
			{
				echo "\t" . $error->toString() . "\n";
			}
		}
	}
}
?>
