<?php // -*- tab-width: 2; indent-tabs-mode: 1; -*- 

/*  
 * $Id: functions.inc.php,v 1.3 2003/02/25 10:09:31 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

function startTiming(){
  global $startTime;
  $microtime = microtime();
  $microsecs = substr($microtime, 2, 8);
  $secs = substr($microtime, 11);
  $startTime = "$secs.$microsecs";
}

function stopTiming(){
  global $startTime, $totalTime;
  
  $microtime = microtime();
  $microsecs = substr($microtime, 2, 8);
  $secs = substr($microtime, 11);
  $endTime = "$secs.$microsecs";
  $totalTime = round(($endTime - $startTime),4);
  return $totalTime;
}

function dump($what, $name='')
{
	echo "<TABLE><TR><TD>";
	echo "<PRE>Dump: $name\n";
	print_r($what);
	echo "</PRE></TD></TR></TABLE>";
}

/** this creates a log entry */
function logError($msg) {
  error_log(getHostName() . ": ERROR: $msg", 0);
}

/** this creates a log entry if $debug is true*/
function debug($name, $msg='', $type='default') {
  global $debug, $debug_type;
  // the $debug_type is set in config.inc.php
  if ($debug) {
    logger($name, $msg, $type);
  }
}

/** this creates a log entry */
function logger($name, $msg='', $type='default') {
  if ($type == 'default') {
    $type = $debug_type;
  }
  if(is_array($msg)) {
    ob_start();
    //var_dump($msg);
    print_r($msg);
    $msg = "\n" . ob_get_contents();
    ob_end_clean();
  }
  error_log(getHostName() . ": $name: $msg", 0);
  if ($type == 'now' && headers_sent() ) {
    echo "<small><pre> Debug: $name: $msg </pre></small><br>\n";
  } 
}

function getHostName()
{
	if(!$host) $host = myGetenv("REMOTE_HOST");
	if(!$host) $host = myGetenv("REMOTE_ADDR");
	return $host;
}

function myGetenv($name) {
	$foo = getenv($name);
	if(!$foo)
		$foo = $_SERVER[$name];
	return $foo;
}

function addError($msg) {
  global $page;
  if(DB::isError($msg)) 
    $msg = "SQL error: " . $msg->getMessage();
  logError($msg);
  $page->errors[] = $page->getlocalized($msg);
}

function raiseError($msg) {
  global $page;
  if(DB::isError($msg)) 
    $msg = "SQL error: " . $msg->getMessage();
  logError($msg);
  $page->errors[] = $page->getlocalized($msg);
  $page->halt();
  exit;
}

function noErrors() {
  return empty($page->errors);
}

/** shortcut for permission check: hasPerm(<objectId>, <permName1>, <permName2>, ...)
will return true if the current user has at least on of the listed permissions for the object.
Also used in smarty templates to check permissions. */
function hasPerm($object) {
  global $permissions;
	$perm_list = func_get_args();
	for ($i = 1; $i <count($perm_list); $i++) {
		debug('checking for permission', $perm_list[$i]);
		if($permissions->hasPermission($object, $perm_list[$i]))
			return true;
	}
	return false;
}

function moveUploadedFile($fieldName, $file) {
  if(!move_uploaded_file($_FILES[$fieldName]['tmp_name'], $file))
		raiseError("Could not move uploaded file from " . $_FILES[$fieldName]['tmp_name'] . " to $file");
	debug("Moved uploaded file", $_FILES[$fieldName]['tmp_name'] . " to $file");
  if(!chmod($file, 0660)) {
		logger("Could not chmod file $file!");
	}
}

?>