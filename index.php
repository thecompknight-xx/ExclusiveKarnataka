<?php
error_reporting(E_ALL);
// using DIRECTORY_SEPARATOR since PATH_SEPARATOR didn't show up until PHP 4.3
ini_set('include_path', 'WEB-INF/lib' . (DIRECTORY_SEPARATOR == '/' ? ':' : ';') . 'WEB-INF/classes');
// umask: 0022 (rw for user); 0002 (rw for user+group); 0000 (rw for all)
umask(0002);
/**
 * Bootstrap our Horizon Object Facade and Class Libraries for PHP to give that "java-like" feel,
 * then create our servlet context and pass the request on the the HttpProcessor.
 */
require_once 'horizon/init.php';

import('stratus.connector.HttpProcessor');
import('stratus.config.ContextConfig');

$config =& new ContextConfig(dirname(__FILE__));
$processor =& new HttpProcessor($config->getContext());
$processor->run();
?>
