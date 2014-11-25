<?php 

/**
 *  The EntryPoint For the Program 
 *
 *
 */

 /* Set Memory Limit Fif the Particular FIle is Large and it consume too much Memory For Processing */

ini_set("memory_limit", -1);

set_time_limit(0);

/* include LogReader to Read the Log File and Applied Necessary Operation on it. */
include_once('LogReader.php');


/* If Access through Browser used Default Log File For Processing */
$filePath = 'sample.log';
$separator = "<br>";

/* If the execution through Command Line then use Command Line Argument in which supplied the Path For Sample Log file */

if ( php_sapi_name() === 'cli' ) {
	
	// Change the separator for CLI
	$separator = PHP_EOL;
	// If the path to the log file is provided through CLI
	if ( isset( $argv[1] ) ) {
		$filePath = $argv[1];
	}
}


/* After Set The Log File Path passed as an Argument to LogReader Class For Processing     */
$reader = new LogReader( $filePath );

/* Applied Necessary Functions on it and Extract Appropriate Result   and Assign to Variables  */

$pendingMsgData = $reader->getUrlData('count_pending_messages', 'get', true);
$getMsgData = $reader->getUrlData('get_messages', 'get', true);
$friendProgData = $reader->getUrlData('get_friends_progress', 'get', true);
$friendScData = $reader->getUrlData('get_friends_score', 'get', true);
$usersGetData = $reader->getUrlData('users', 'get', true);
$usersPostData = $reader->getUrlData('users', 'post', true);

/* Display Output */
if ( php_sapi_name() !== 'cli' ) {
    echo "<h1>Pocket Playlab Challenge</h1>";
    echo "<h2>Output : </h2>";
}
echo $pendingMsgData . $separator . $separator;
echo $getMsgData . $separator . $separator;
echo $friendProgData . $separator . $separator;
echo $friendScData . $separator . $separator;
echo $usersGetData . $separator . $separator;
echo $usersPostData . $separator . $separator;