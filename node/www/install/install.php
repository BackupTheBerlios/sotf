<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*-

/*
 * $Id: install.php,v 1.30 2005/06/24 18:43:24 wreutz Exp $
 *
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

  //die("By default the install script is disabled. This is for your security. Please comment out this statement at the beginning of the file");

ini_set("max_execution_time", "90");
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
//header ("Expires: Mon, 26 Jul 2010 05:00:00 GMT");
//TODO:debug infok kikapcsolasa!!!
// TODO: cache dir letrehozasa!!
// TODO chekc imagemagick installed

ini_set("error_log", "../../logs/log");
ini_set("log_errors", true);
error_reporting (E_ALL ^ E_NOTICE);

function dbug($msg) {
  error_log("INSTALL: $msg",0);
}

dbug("-----INSTALL---------------------------------------------");
dbug("install.php started");

function PrintTitle($number)		//'header' af all tests
{
	set_time_limit(120);		//extends the time limit for the next test
	global $install_test_name, $install_color;
	print('<TABLE width="100%"><TR><TD BGCOLOR="'.$install_color[$number].'">');		//begin new table row, color is set here
	print('<DIV ALIGN="center"><B>'.$install_test_name[$number].'<BR /><BR /></B></DIV>');		//prints the name of the test
}

function PrintButton($number)		//'footer' of all tests
{
	global $install_test_result, $install_color;
	print('<BR /><DIV ALIGN="center">'.$install_test_result[$number].'</DIV>');		//prints the result string of the tests
	print('<INPUT type="hidden" name="test_result[]" value="'.htmlentities($install_test_result[$number]).'">');	//stores the result string in a hidden field (no need to rerun the test)
	print('<INPUT type="hidden" name="color[]" value="'.$install_color[$number].'">');		//stores the color value	-||-
	print('<BR /><DIV ALIGN="center"><INPUT type="submit" name="run_test" value="Run test '.$number.'"></DIV>');	//prints a run test button user can run this test again
	print('</TD></TR></TABLE>');		//end new table row
	flush();			//writes out the row (if no buffer the user can see it)
}

function RunTest($number, $testname, $required = -1)		//returns if or not to run the test again
{
	global $install_run_test, $install_test_result, $install_test_name, $install_green, $install_color;
	$install_test_name[$number] = $testname;
	if ($required == -1)		//if no test is required for this one
		$retval = (($install_run_test == "Run test ".$number) OR ($install_test_result[$number] == NULL));		//if button pressed or no results (not run yet)
	else				//if a pervious test needs to be OK
		$retval = ( ($install_run_test == "Run test ".$number) OR (($install_test_result[$number] == NULL) AND ($install_color[$required] == $install_green)) );	//same but the other test must be OK
   //dbug("RunTest $number: $retval");
	return $retval;
}

function GetPerm($filename, $option)		//tries to write or read files and directories
{
	if ($option == "read")			////read a file
	{
		if (file_exists($filename))
		{
			if (!$fp = fopen($filename, 'r'))
			{
				return("read ERROR");		//file exists but can not be read
			}
			fclose($fp);
			return("OK readable");			//file is readable
		}
		else		//file does not exists
		{
		return("ERROR file does not exists");
		}
	}
	elseif ($option == "write")		////write a non existing file (directory write test)
	{
		if (file_exists($filename)) return("ERROR file in use");	//file exists ERROR
		if (!$fp = fopen($filename, 'a'))			//create file
		{
			return("creating ERROR");			//file can not be created
		}
		if (!fwrite($fp, "alma"))
		{
			return("write ERROR");				//file can not be written
		}
		fclose($fp);
		unlink($filename);					//delete file
		return("OK writeable");					//file is writeable
	}
	elseif ($option == "dir")			////directory read test
	{
		if (file_exists($filename))
		{
			if (!$dir = opendir($filename))	//read files in dir
			{
				return("read ERROR");
			}
			closedir($dir);				//close dir handler
			return("OK readable");
		}
		else
		{
		return("ERROR directory does not exists");
		}
	}
	elseif ($option == "append")			////write to an existing or not existing file
	{
		if (!$fp = fopen($filename, 'a'))	//open file
		{
			return("creating ERROR");	//could not be opened
		}
		if (!fwrite($fp, "\ninstall scipt try\n"))	//write string
		{
			return("write ERROR");			//write error
		}
		fclose($fp);					//clode file
		return("OK writeable");
	}
	else return "ERROR ".$option." unknown in GetPerm";	////bad option parameter
}

function addParent($name, $topic_name = "", $lang = "en", $treeId=2, $description='')
{
	$x = new sotf_NodeObject("sotf_topic_tree_defs");
	$x->set('supertopic', 0);
	$x->set('name', $name);
	$x->set('tree_id', $treeId);
	$x->create();
	$id = $x->getID();
	if ($topic_name != "")
	{
		$y = new sotf_NodeObject("sotf_topics");
		$y->set('topic_id', $id);
		$y->set('language', $lang);
		$y->set('topic_name', $topic_name);
		$y->set('description', $description);
		$y->create();
		//print($id);
	}
	return $id;
}

function addChild($parent, $name, $topic_name = "", $lang = "en", $treeId=2)
{
	$x = new sotf_NodeObject("sotf_topic_tree_defs");
	$x->set('supertopic', $parent);
	$x->set('name', $name);
	$x->set('tree_id', $treeId);
	$x->create();
	$id = $x->getID();
	if ($topic_name != "")
	{
		$y = new sotf_NodeObject("sotf_topics");
		$y->set('topic_id', $id);
		$y->set('language', $lang);
		$y->set('topic_name', $topic_name);
		$y->create();
		//print($id);
	}
	return $x->getID();
}


/*
Initial parameters
*/
$install_maxtests = 8;					//number of tests
$install_red  =	 "FF5555";				//red for ERROR
$install_green = "00FF00";				//green for OK
$install_blue =	 "0000FF";				//blue for not tested
$install_test_name = "";				//array for the name of the tests


/*
$install_user = $_POST["user"];		//username for DB
$install_pass = $_POST["pass"];		//password for DB
$install_host = $_POST["host"];		//host for DB
$install_port = $_POST["port"];		//port for DB

$install_sadm_user = $_POST["sadm_user"];		//username for DB
$install_sadm_pass = $_POST["sadm_pass"];		//password for DB
$install_sadm_host = $_POST["sadm_host"];		//host for DB
$install_sadm_port = $_POST["sadm_port"];		//port for DB
$install_sadm_db_name = $_POST["sadm_db_name"];	//DB name

$install_node_user = $_POST["node_user"];		//username for DB
$install_node_pass = $_POST["node_pass"];		//password for DB
$install_node_host = $_POST["node_host"];		//host for DB
$install_node_port = $_POST["node_port"];		//port for DB
$install_node_db_name = $_POST["node_db_name"];	//DB name
*/

$install_run_test = $_POST["run_test"];	//Run test X buttons
$install_run_all = $_POST["RUN_ALL"];		//Run all button
$install_reload = $_POST["reload"];		//Reload config.inc.php button
$install_createdb = $_POST["createdb"];	//Create sadm db button
$install_writeback_sadm = $_POST["writeback_sadm"];	//write sadm connection params to config.inc.php
$install_writeback_node = $_POST["writeback_node"];	//write node connection params to config.inc.php

$install_delete_topic = $_POST["delete_topic"];	//delete topic tree
$install_create_topic = $_POST["create_topic"];	//create topic tree

// test 8: node admin
$admin_name = $_POST["admin_name"];
$admin_pass = $_POST["admin_pass"];


$install_color = $_POST["color"];		//color values for the cells
$install_test_result = $_POST["test_result"];	//result strings of the tests


if ($install_user === NULL)		//set default parameter if first time here
{
	$install_user = 'username';
	$install_pass = 'password';
	$install_host = 'host';
	$install_port = '5432';

	for ($i=0; $i <= $install_maxtests; $i++)
	{
		$install_color[$i]=$install_blue;	//set blue color as default
		$install_test_result[$i] = NULL;	//no test results (forces tests to run)
	}
}

if ($install_run_all != NULL)		//if run_all button pressed
{
	for ($i=0; $i <= $install_maxtests; $i++)
	{
		$install_color[$i]=$install_blue;	//set blue color as default
		$install_test_result[$i] = NULL;	//no test results (forces tests to run)
	}
}


if (($install_color[$id] = $install_green) AND ($nodeDbHost == NULL))			//if test 2 passed and not already included
	include("../config.inc.php");

//include "install_tests.php";

?>

<HTML>
<HEAD>
<TITLE>Install</TITLE>
</HEAD>
<BODY>
<FORM method="post" action="install.php">
<DIV ALIGN="center"><H2>Install</H2></DIV>

<INPUT type="hidden" name="test_result[]" value="<?php print($install_test_result[0]) ?>">
<INPUT type="hidden" name="color[]" value="FFFFFF">
<DIV ALIGN="center">
<!-- <TABLE BORDER=1 CELLPADDING=5 CELLSPACING=0 WIDTH="60%" BGCOLOR="<?php print($install_color[0]) ?>">
-->

<?php
	$id = 1;	//////////////////////////Test 1
	if (RunTest($id, "Server configuration"))
	{
		if ($_SERVER["PHP_SELF"] == NULL)
		{
			$install_test_result[$id] = "PHPSELF can not be read.";
			$install_color[$id] = $install_red;
		}
		else
		{
			$install_test_result[$id] = "Current location: ".$_SERVER["PHP_SELF"];
			$install_test_result[$id] .= "<BR />Server software: ".$_SERVER["SERVER_SOFTWARE"];
			$install_test_result[$id] .= "<BR />Server protocol: ".$_SERVER["SERVER_PROTOCOL"];
			$install_test_result[$id] .= "<BR />Remote address: ".$_SERVER["REMOTE_ADDR"];
			$install_test_result[$id] .= "<BR />Extensions loaded: ".implode(", ", array_values(get_loaded_extensions()));

			$install_test_result[$id] .= "<BR />Web server - PHP interface: ".php_sapi_name();
			$install_test_result[$id] .= "<BR />PHP version: ".phpversion();

//			print_r();
//			print(php_sapi_name());

			$install_color[$id] = $install_green;
		}
	}
	for ($i=0;$i<256;$i++) echo " ";

	PrintTitle($id);
	PrintButton($id);


	$id = 2;	//////////////////////////Test 2
	if (RunTest($id, "'config.inc.php' file include test") or ($install_reload != NULL))
	{
		$error = false;
		$install_test_result[$id] = "config.inc.php: ";
	
		if (!file_exists("../config.inc.php"))
		{
			$install_test_result[$id] .= "file not exists.";
			$error = true;
		}
		elseif (GetPerm("../config.inc.php", "read") != "OK readable")
		{
			$install_test_result[$id] .= "reading error";
			$error = true;
		}
	
		if ($error)
		{
			$install_color[$id] = $install_red;
		}
		else
		{
			include("../config.inc.php");
			if ($config['nodeDbHost'] != NULL)		//if include successfull
			{
					//set default parameter if first time successfull or reload button pressed
				if (($install_color[$id] != $install_green) or ($install_reload != NULL))
				{
					$install_user = $config['nodeDbUser'];			//username for DB
					$install_pass = $config['nodeDbPasswd'];			//password for DB
					$install_host = $config['nodeDbHost'];			//host for DB
					$install_port = $config['nodeDbPort'];			//port for DB
					
					if($config['selfUserDb']) {
					  $install_sadm_user = $config['nodeDbUser'];		//username for DB
					  $install_sadm_pass = $config['nodeDbPasswd'];		//password for DB
					  $install_sadm_host = $config['nodeDbHost'];		//host for DB
					  $install_sadm_port = $config['nodeDbPort'];		//port for DB
					  $install_sadm_db_name = $config['nodeDbName'];		//DB name
					} else {
					  $install_sadm_user = $config['userDbUser'];		//username for DB
					  $install_sadm_pass = $config['userDbPasswd'];		//password for DB
					  $install_sadm_host = $config['userDbHost'];		//host for DB
					  $install_sadm_port = $config['userDbPort'];		//port for DB
					  $install_sadm_db_name = $config['userDbName'];		//DB name
					}
					
					$install_node_user = $config['nodeDbUser'];		//username for DB
					$install_node_pass = $config['nodeDbPasswd'];		//password for DB
					$install_node_host = $config['nodeDbHost'];		//host for DB
					$install_node_port = $config['nodeDbPort'];		//port for DB
					$install_node_db_name = $config['nodeDbName'];		//DB name
				}
				$install_color[$id] = $install_green;
				$install_test_result[$id] .= "OK";
			}
			else					//include error
			{
				$install_color[$id] = $install_red;
				$install_test_result[$id] .= "include error";
			}
		}
	}
	PrintTitle($id);
	print('<DIV ALIGN="center"><BR /><INPUT type="submit" name="reload" value="Reload config.inc.php"></DIV>');
	PrintButton($id);


	$id = 3;	//////////////////////////Test 3
	if (RunTest($id, "Directory and file permissions", 2))
	{
	  dbug("TEST 3");
		$install_test_result[$id] = "";

		//log file
		$install_test_result[$id] .= "logFile (".$config['logFile'].") ".GetPerm($config['logFile'], "append")."<BR />";

		//directories with write permission
		$install_test_result[$id] .= "repositoryDir (".$config['repositoryDir'].") ".GetPerm($config['repositoryDir']."/pmppmp.pmp", "write")."<BR />";
		$install_test_result[$id] .= "userDirs (".$config['userDirs'].") ".GetPerm($config['userDirs']."/pmppmp.pmp", "write")."<BR />";

		$install_test_result[$id] .= "logs (".$config['basedir']."/logs) ".GetPerm($config['basedir']."/logs/pmppmp.pmp", "write")."<BR />";
		$install_test_result[$id] .= "templates_c (".$config['basedir']."/code/templates_c) ".GetPerm($config['basedir']."/code/templates_c/pmppmp.pmp", "write")."<BR />";
		$install_test_result[$id] .= "tmp (../tmp) ".GetPerm("../tmp/pmppmp.pmp", "write")."<BR />";
//		$install_test_result[$id] .= " (".$config['basedir'].") ".GetPerm($config['basedir']."/pmppmp.pmp", "write")."<BR />";

		//other directories
		$install_test_result[$id] .= "basedir (".$config['basedir'].") ".GetPerm($config['basedir'], "dir")."<BR />";
		$install_test_result[$id] .= "getid3dir (".$config['getid3dir'].") ".GetPerm($config['getid3dir'], "dir")."<BR />";
		$install_test_result[$id] .= "musicDir (".$config['musicDir'].") ".GetPerm($config['musicDir'], "dir")."<BR />";

		$install_test_result[$id] .= "classes (".$config['basedir']."/code) ".GetPerm($config['basedir']."/code", "dir")."<BR />";
		$install_test_result[$id] .= "classes (".$config['basedir']."/code/classes) ".GetPerm($config['basedir']."/code/classes", "dir")."<BR />";

		//files that are required by init.inc.php
		$install_test_result[$id] .= "peardir (".$config['peardir']."/DB.php) ".GetPerm($config['peardir']."/DB.php", "read")."<BR />";					//require($peardir . '/DB.php');
		$install_test_result[$id] .= "smartydir (".$config['smartydir']."/Smarty.class.php) ".GetPerm($config['smartydir']."/Smarty.class.php", "read")."<BR />";		//require($smartydir . '/Smarty.class.php');
		$install_test_result[$id] .= "smartydir (".$config['smartydir']."/Config_File.class.php) ".GetPerm($config['smartydir']."/Config_File.class.php", "read")."<BR />";	//require($smartydir . '/Config_File.class.php');

		$install_test_result[$id] .= "classdir (".$config['classdir']."/db_Wrap.class.php) ".GetPerm($config['classdir']."/db_Wrap.class.php", "read")."<BR />";		//require($config['classdir'] . '/db_Wrap.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_Utils.class.php) ".GetPerm($config['classdir']."/sotf_Utils.class.php", "read")."<BR />";		//require($config['classdir'] . '/sotf_Utils.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_User.class.php) ".GetPerm($config['classdir']."/sotf_User.class.php", "read")."<BR />";		//require($config['classdir'] . '/sotf_User.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_Page.class.php) ".GetPerm($config['classdir']."/sotf_Page.class.php", "read")."<BR />";		//require($config['classdir'] . '/sotf_Page.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_Permission.class.php) ".GetPerm($config['classdir']."/sotf_Permission.class.php", "read")."<BR />";	//require($config['classdir'] . '/sotf_Permission.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_Vars.class.php) ".GetPerm($config['classdir']."/sotf_Vars.class.php", "read")."<BR />";		//require($config['classdir'] . '/sotf_Vars.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_Repository.class.php) ".GetPerm($config['classdir']."/sotf_Repository.class.php", "read")."<BR />";	//require($config['classdir'] . '/sotf_Repository.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_FileList.class.php) ".GetPerm($config['classdir']."/sotf_FileList.class.php", "read")."<BR />";	//require($config['classdir'] . '/sotf_FileList.class.php');
		$install_test_result[$id] .= "classdir (".$config['classdir']."/sotf_AudioCheck.class.php) ".GetPerm($config['classdir']."/sotf_AudioCheck.class.php", "read")."<BR />";	//require($config['classdir'] . '/sotf_AudioCheck.class.php'); 

//		$install_test_result[$id] .= "rosszdir (C:/temp/temp) ".GetPerm("C:/temp/temp/pmppmp.pmp", "read")."<BR />";
//		$install_test_result[$id] .= " () ".GetPerm(."/pmppmp.pmp")."<BR />";
	
		if (strpos($install_test_result[$id], "ERROR"))
		{
			$install_color[$id] = $install_red;
		}
		else
		{
			$install_color[$id] = $install_green;
		}
	}
	PrintTitle($id);
	PrintButton($id);


	$id = 4;	//////////////////////////Test 4
	if (RunTest($id, "PostgreSQL connection")) {
	  dbug("TEST 4: pgsql connection");
	  dbug("pg_connect: host=$install_host port=$install_port user=$install_user dbname=template1 password=$install_pass");
	  $conn = pg_connect("host=$install_host port=$install_port user=$install_user dbname=template1 password=$install_pass");
		if (!$conn) {
		  dbug("Connection failed");
		  $install_test_result[$id] = "Connecting to PostGreSQL failed";
		  $install_color[$id] = $install_red;
		} else {
		  dbug("Connection succeeded");
		  $install_test_result[$id] = "OK";
		  $install_color[$id] = $install_green;
		}
		pg_close($conn);
	}
   PrintTitle($id);
	print('
	<DIV ALIGN="center">
	Username: <INPUT type="text" name="user" value="'.$install_user.'"><BR />
	Password: <INPUT type="password" name="pass" value="'.$install_pass.'"><BR />
	Hostname: <INPUT type="text" name="host" value="'.$install_host.'"> Port: <INPUT type="text" name="port" value="'.$install_port.'" SIZE=5><BR />
	</DIV>');
	PrintButton($id);



//////////////////////////Test 5

	$id = 5;	
	if (RunTest($id, "DB connection to 'node'", 4) && !$install_createdb)		//Test 4 should be OK to run this test
	{
	  dbug("TEST 5: node database");
	  dbug("pg_connect: host=$install_node_host port=$install_node_port dbname=$install_node_db_name user=$install_node_user password=$install_node_pass");

	  $conn = pg_connect("host=$install_node_host port=$install_node_port dbname=$install_node_db_name user=$install_node_user password=$install_node_pass");
	  if (!$conn) {
		 dbug("Connect failed");
		 $install_test_result[$id] = "Database '".$install_node_db_name."' not found";
		 $install_color[$id] = $install_red;
	  } else {
		 dbug("Connect success");
		 $install_test_result[$id] = "OK";
		 $install_color[$id] = $install_green;
		 pg_close($conn);
	  }
	}
if ($install_createdb)			//if create node db button pressed
  {
	 dbug("pg_connect: host=$install_node_host port=$install_node_port user=$install_node_user dbname=template1 password=$install_node_pass");
	 $conn = pg_connect("host=$install_node_host port=$install_node_port user=$install_node_user dbname=template1 password=$install_node_pass");	//connect
	 if (!$conn)
		{
		  dbug("Connect failed");
		  $install_test_result[$id] = "Could not connect.";
		  $install_color[$id] = $install_red;
		}
		else
		{
			$sql = "CREATE DATABASE ".$install_node_db_name . " WITH ENCODING='unicode' ";		//create new db
			$result = pg_exec($conn, $sql);
			if (!$result)
			{
			  dbug("Create database failed");
			  $install_test_result[$id] = "Could not create db.";
			  $install_color[$id] = $install_red;
			}
			else
			{
			  dbug("Database created");
			  pg_close($conn);		//close old connection
			  dbug("pg_connect: host=$install_host port=$install_port user=$install_user dbname=$install_node_db_name password=$install_pass");
			  $conn = pg_connect("host=$install_host port=$install_port user=$install_user dbname=$install_node_db_name password=$install_pass");	//Connect to the new DB
				if (!$conn)
				{
				  dbug("Connect to new db failed");
				  $install_test_result[$id] = "Could not connect to the new db.";
				  $install_color[$id] = $install_red;
				}
				else
				{
				  dbug("Connected to new db");
				  //Read SQL commands from db.sql and execute them
				  $fd = fopen ($config['basedir'] ."/code/share/db.sql", "r");
				  if (!$fd) {
					 dbug("Sql file (". $config['basedir'] ."/code/share/db.sql) not found.");
					 $install_test_result[$id] = "Sql file (". $config['basedir'] ."/code/share/db.sql) not found.";
					 $install_color[$id] = $install_red;
				  } else {
					 $buffer = "";
					 //read the whole file
					 while (!feof ($fd)) {
						$line = fgets($fd, 1024);	//read a line with max lengts 1024 byte
						$pos = strpos($line, "--");	//find comment position
						if ($pos === false) $buffer .= $line;	//if no in line add it to the buffer
						else $buffer .= substr($line, 0, $pos);		//if any comment remove it first
					 }
					 fclose ($fd);

					 $buffer = rtrim($buffer);				//delete spaces from end
					 // insert correct path into ftp_auth view needed by proftpd
					 $buffer = str_replace("__PATH_TO_USER_DIRS__", $config['userDirs'], $buffer);
					 $sql = explode(";", $buffer);				//divide into single commands
					 
					 $max = count($sql);					//count commands
					 $install_color[$id] = $install_green;
					 $install_test_result[$id] = "";				//delete pervious results
					 //execute all commands if not empty
					 for($i=0; $i<$max; $i++) if ($sql[$i] != '') {
						$result = pg_exec($conn, $sql[$i]);
						//$result = $db->query();
						if (!$result) {
						  $install_test_result[$id] .= "<BR><DIV ALIGN='center'>-------".pg_last_error($conn)."-------<BR>".$sql[$i]."<BR></DIV>";
						  $install_color[$id] = $install_red;
						}
					 }
					 if ($install_color[$id] == $install_green) $install_test_result[$id] = "OK";
				  }
				}
			}
		}
	}
//$install_writeback_node
	PrintTitle($id);
	print('
	Username: <INPUT type="text" name="node_user" value="'.$install_node_user.'"><BR />
	Password: <INPUT type="password" name="node_pass" value="'.$install_node_pass.'"><BR />
	Hostname: <INPUT type="text" name="node_host" value="'.$install_node_host.'"> Port: <INPUT type="text" name="node_port" value="'.$install_node_port.'" SIZE=5><BR />
	Database name: <INPUT type="text" name="node_db_name" value="'.$install_node_db_name.'"><BR />');
   if ( ($install_color[$id] == $install_green) AND ( ($install_node_user != $nodeDbUser) OR ($install_node_pass != $nodeDbPasswd)
			OR ($install_node_host != $nodeDbHost) OR ($install_node_port != $nodeDbPort) OR ($install_node_db_name != $nodeDbName) ) ) {
	  //print('<DIV ALIGN="center"><BR /><INPUT type="submit" name="writeback_node" value="Write new values to config.inc.php" disabled=true></DIV>');
	}
	if ($install_color[$id] == $install_red) print('<DIV ALIGN="center"><BR /><INPUT type="submit" name="createdb" value="Create NODE db"></DIV>');
	PrintButton($id);

//////////////////////////Test 6
	$id = 6;	
	if (RunTest($id, "DB connection to User Database", 5))		//////////////////////////Test 5 should be OK to run this test
	  {
		 dbug("TEST 6: user database");
		 if($config['selfUserDb']) {
			// we use the node database for user management
			$install_test_result[$id] = "OK";
			$install_color[$id] = $install_green;
		 } elseif($config['userDbType'] == 'mysql') {
			// we have a mysql user database
			dbug("mysql_connect $install_sadm_host:$install_sadm_port, $install_sadm_user, $install_sadm_pass");
			$conn = mysql_connect("$install_sadm_host:$install_sadm_port", $install_sadm_user, $install_sadm_pass);
			if(!$conn) {
			  dbug("Could not connect");
			  $install_test_result[$id] = "Could not connect.";
			  $install_color[$id] = $install_red;
			} else {
			  if(!mysql_select_db($install_sadm_db_name)) {
				 dbug("Database '$install_sadm_db_name' not found.");
				 $install_test_result[$id] = "Database '$install_sadm_db_name' not found.";
				 $install_color[$id] = $install_red;
			  } else {
				 $install_test_result[$id] = "OK";
				 $install_color[$id] = $install_green;
			  }
			  mysql_close();
			}
		 } elseif($config['userDbType'] == 'pgsql') {
			// we have a postgres user database
			dbug("connect: host=$install_sadm_host port=$install_sadm_port dbname=$install_sadm_db_name user=$install_sadm_user password=$install_sadm_pass");
			$conn = pg_connect("host=$install_sadm_host port=$install_sadm_port dbname=$install_sadm_db_name user=$install_sadm_user password=$install_sadm_pass");
			if (!$conn) {
			  dbug("Connection failed");
			  dbug("pg_connect host=$install_sadm_host port=$install_sadm_port dbname=template1 user=$install_sadm_user password=$install_sadm_pass");
			  $conn = pg_connect("host=$install_sadm_host port=$install_sadm_port dbname=template1 user=$install_sadm_user password=$install_sadm_pass");
			  if (!$conn) {
				 dbug("Could not connect");
				 $install_test_result[$id] = "Could not connect.";
				 $install_color[$id] = $install_red;
			  } else {
				 dbug("Database sadm not found");
				 $install_test_result[$id] = "Database '$install_sadm_db_name' not found.";
				 $install_color[$id] = $install_red;
				 pg_close($conn);
			  }
			} else {
			  $install_test_result[$id] = "OK";
			  $install_color[$id] = $install_green;
			  pg_close($conn);
			}
		 } else {
			$install_test_result[$id] = "Cannot handle database type:" . $config['userDbType'] . "Please ask support from developers" ;
			$install_color[$id] = $install_red;
		 }
		 if($install_test_result[$id] == "OK") {
			// initialize user DB
			if(!include_once($config['classdir'] . '/' . $config['userDbClass'] . '.class.php')) {
			  dbug("include user db handler class failed");
			  $install_test_result[$id] = "Check \$config['userDbClass']: class cannot be found.";
			  $install_color[$id] = $install_red;
			} else {
			  $install_test_result[$id] = "OK";
			  $install_color[$id] = $install_green;
			}
		 }
	}
//$install_writeback_sadm
	PrintTitle($id);
	print('
   Connector class: node/code/classes/' . $config['userDbClass'] . '.class.php <BR />
	Type: ' . $config['userDbType'] . '<BR />
	Username: <INPUT type="text" name="sadm_user" value="'.$install_sadm_user.'"><BR />
	Password: <INPUT type="password" name="sadm_pass" value="'.$install_sadm_pass.'"><BR />
	Hostname: <INPUT type="text" name="sadm_host" value="'.$install_sadm_host.'"> Port: <INPUT type="text" name="sadm_port" value="'.$install_sadm_port.'" SIZE=5><BR />
	Database name: <INPUT type="text" name="sadm_db_name" value="'.$install_sadm_db_name.'"><BR />');
	if ( ($install_color[$id] == $install_green) AND ( ($install_sadm_user != $userDbUser) OR ($install_sadm_pass != $userDbPasswd)
		   OR ($install_sadm_host != $userDbHost) OR ($install_sadm_port != $userDbPort) OR ($install_sadm_db_name != $userDbName) ) ) {
	  print('<DIV ALIGN="center"><BR /><INPUT type="submit" name="writeback_sadm" value="Write new values to config.inc.php" disabled=true></DIV>');
	}
	PrintButton($id);


////////////////////////// Test 7
	$id = 7;
	if (RunTest($id, "Vocabularies", 6) OR isset($install_create_topic) OR isset($install_delete_topic))
	{
	  dbug("TEST 7");

		if (isset($install_create_topic))
		{
			require_once("../init.inc.php");

			$db->begin(true);

			// create roles
			
			$db->query("DELETE FROM sotf_node_objects WHERE id LIKE '___rn%'");
			$db->query("DELETE FROM sotf_node_objects WHERE id LIKE '___ro%'");
			$db->query("DELETE FROM sotf_roles");
			$db->query("DELETE FROM sotf_role_names");
			$db->query("SELECT setval('sotf_roles_seq', 1, false)");
			$db->query("SELECT setval('sotf_role_names_seq', 1, false)");
			
			// create default roles: THE ORDER IS IMPORTANT!
			$vocabularies->importRoles(file($config['basedir']."/code/share/roles_eng.txt"), 'eng');
			$vocabularies->importRoles(file($config['basedir']."/code/share/roles_ger.txt"), 'ger');
			$vocabularies->importRoles(file($config['basedir']."/code/share/roles_hun.txt"), 'hun');
			$vocabularies->importRoles(file($config['basedir']."/code/share/roles_fra.txt"), 'fra');

			$db->query("UPDATE sotf_roles SET creator='t' WHERE role_id='2' OR role_id='5' OR role_id='8' OR role_id='9' OR role_id='12' OR role_id='16' OR role_id='22' OR role_id='24'");
			
			$sotfVars->set('roles_langs', 'eng,ger,hun,fra');
			
			// create genres
			
			$db->query("DELETE FROM sotf_node_objects WHERE id LIKE '___ge%'");
			$db->query("DELETE FROM sotf_genres");
			$db->query("SELECT setval('sotf_genres_seq', 1, false)");
			
			// create default genres: THE ORDER IS IMPORTANT!
			$vocabularies->importGenres(file($config['basedir']."/code/share/genres_eng.txt"), 'eng');
			$vocabularies->importGenres(file($config['basedir']."/code/share/genres_ger.txt"), 'ger');
			$vocabularies->importGenres(file($config['basedir']."/code/share/genres_hun.txt"), 'hun');
			$vocabularies->importGenres(file($config['basedir']."/code/share/genres_fra.txt"), 'fra');

			$sotfVars->set('genres_langs', 'eng,ger,hun,fra');

			// delete topics 

			$result = $db->query("DELETE FROM sotf_topic_trees");
			$result = $db->query("DELETE FROM sotf_topic_tree_defs");
			$result = $db->query("DELETE FROM sotf_topics");
			$result = $db->query("DELETE FROM sotf_node_objects WHERE id LIKE '___tt%'");
			$result = $db->query("DELETE FROM sotf_node_objects WHERE id LIKE '___td%'");
			$result = $db->query("DELETE FROM sotf_node_objects WHERE id LIKE '___to%'");
			$result = $db->query("SELECT setval('sotf_topics_seq', 1, false)");
			$result = $db->query("SELECT setval('sotf_topic_trees_seq', 1, false)");
			$result = $db->query("SELECT setval('sotf_topic_tree_defs_seq', 1, false)");

			/*
			$sql = "DELETE FROM sotf_topic_tree_defs";
			$result = pg_exec($conn, $sql);
			$sql = "DELETE FROM sotf_topic_trees";
			$result = pg_exec($conn, $sql);
			$sql = "DELETE FROM sotf_topics";
			$result = pg_exec($conn, $sql);
			*/

			// create default topic trees: THE ORDER IS IMPORTANT!
			$vocabularies->importTopicTree(file($config['basedir']."/code/share/topictree_sotf_eng.txt"), 'eng');
			$vocabularies->importTopicTree(file($config['basedir']."/code/share/topictree_sotf_ger.txt"), 'ger');
			$vocabularies->importTopicTree(file($config['basedir']."/code/share/topictree_sotf_hun.txt"), 'hun');
			$vocabularies->importTopicTree(file($config['basedir']."/code/share/topictree_sotf_fra.txt"), 'fra');
			$vocabularies->importTopicTree(file($config['basedir']."/code/share/topictree_soma_eng.txt"), 'eng');

			// not really  needed any more, just here for safety
			//$result = $db->query("SELECT setval('sotf_topics_seq', ". $config['nodeId'] . "000, false)");
			//$result = $db->query("SELECT setval('sotf_topic_trees_seq', ". $config['nodeId'] . "000, false)");
			//$result = $db->query("SELECT setval('sotf_topic_tree_defs_seq', ". $config['nodeId'] . "000, false)");

			$db->commit();

			$vocabularies->updateTopicCounts();

		}
		if (isset($install_delete_topic))
		  {
      
			 // delete topics 

			 $conn = pg_connect("host=$install_node_host port=$install_node_port dbname=$install_node_db_name user=$install_node_user password=$install_node_pass");
			 $result = pg_exec($conn, "DELETE FROM sotf_node_objects WHERE id LIKE '___tt%'");
			 $result = pg_exec($conn, "DELETE FROM sotf_node_objects WHERE id LIKE '___td%'");
			 $result = pg_exec($conn, "DELETE FROM sotf_node_objects WHERE id LIKE '___to%'");
			 $result = pg_exec($conn, "SELECT setval('sotf_topics_seq', 1, false)");
			 $result = pg_exec($conn, "SELECT setval('sotf_topic_trees_seq', 1, false)");
			 $result = pg_exec($conn, "SELECT setval('sotf_topic_tree_defs_seq', 1, false)");
			 /*
			$sql = "DELETE FROM sotf_topic_tree_defs";
			$result = pg_exec($conn, $sql);
			$sql = "DELETE FROM sotf_topic_trees";
			$result = pg_exec($conn, $sql);
			$sql = "DELETE FROM sotf_topics";
			$result = pg_exec($conn, $sql);
			 */

			 // delete roles

			 $result = pg_exec($conn, "DELETE FROM sotf_node_objects WHERE id LIKE '___rn%'");
			 $result = pg_exec($conn, "DELETE FROM sotf_node_objects WHERE id LIKE '___ro%'");
			 $result = pg_exec($conn, "DELETE FROM sotf_roles");
			 $result = pg_exec($conn, "DELETE FROM sotf_role_names");
			 $result = pg_exec($conn, "SELECT setval('sotf_roles_seq', 1, false)");
			 $result = pg_exec($conn, "SELECT setval('sotf_role_names_seq', 1, false)");

			 // delete genres
      
			 $result = pg_exec($conn, "DELETE FROM sotf_node_objects WHERE id LIKE '___ge%'");
			 $result = pg_exec($conn, "DELETE FROM sotf_genres");
			 $result = pg_exec($conn, "SELECT setval('sotf_genres_seq', 1, false)");

			 //pg_close($conn);		//close old connection

		  }
		$conn = pg_connect("host=$install_node_host port=$install_node_port dbname=$install_node_db_name user=$install_node_user password=$install_node_pass");
		$sql = "SELECT COUNT(*) as rows FROM sotf_topic_tree_defs";
		$result = pg_exec($conn, $sql);
		$count = pg_fetch_array ($result);
		pg_close($conn);		//close old connection
		if ($count["rows"] == 0)
		  {
			 $install_test_result[$id] = "Topic tree is empty";
			 $install_color[$id] = $install_red;
		  }
		else
		  {
			 $install_test_result[$id] = "OK";
			 $install_color[$id] = $install_green;
		  }
	}


PrintTitle($id);
print('<DIV ALIGN="center"><BR />
	<INPUT type="submit" name="delete_topic" value="Delete vocabularies">
	<INPUT type="submit" name="create_topic" value="Create vocabularies (topic tree, genres, roles)">
	</DIV>');
PrintButton($id);

////////////////////////// Test 8 

$id = 8;	
if (RunTest($id, "Node administrator", 7)) // OR isset($install_node_admin))
{
  dbug("TEST 8");
  require_once("../init.inc.php");

  // this will most probably cause an error if the connector class is configured properly!
  $res = sotf_User::findUsers("a");
  $res = NULL;

  $adminId = $db->getOne("SELECT user_id FROM sotf_user_permissions WHERE object_id='node' AND permission_id='1'");

  if(!$adminId && $admin_name && $admin_pass) {
	 if(!sotf_User::getUserid($admin_name)) {
		// let's register the new admin user
		$error = sotf_User::register($admin_pass, $admin_name, '', 'en', '');
	 }
	 if(!$error)
		$error = sotf_User::login($admin_name, $admin_pass);
	 if($error) {
		  $install_test_result[$id] = "Invalid username or password";
		  $install_color[$id] = $install_red;
		  //trigger_error("Invalid username or password");
	 } else {
		$aid = $user->id;
		$count = $db->getOne("SELECT count(*) FROM sotf_user_permissions WHERE user_id='$aid' AND object_id='node' AND permission_id='1'");
		if($count==0) {
		  $db->query("INSERT INTO sotf_user_permissions (object_id, user_id, permission_id) VALUES('node',$aid,1)");
		}
		$count = $db->getOne("SELECT count(*) FROM sotf_user_prefs WHERE id='$aid' OR username='$admin_name'");
		if($count==0) {
		  $db->query("INSERT INTO sotf_user_prefs (id,username) VALUES($aid, '$admin_name')");
		}
	 }
  }

  // check for correct node admin
  $adminId = $db->getOne("SELECT user_id FROM sotf_user_permissions WHERE object_id='node' AND permission_id='1'");
  if($adminId)
	 $adminName = sotf_User::getUsername($adminId);
	 //$adminName = $userdb->getOne("SELECT username FROM authenticate WHERE auth_id='$adminId'");

  if (!$adminName) {
	 $install_test_result[$id] = "Please select node administrator";
	 $install_color[$id] = $install_red;
  } else {
	 $install_test_result[$id] = "OK";
	 $install_color[$id] = $install_green;
	 if(!$admin_name) $admin_name = $adminName;
  }
}

PrintTitle($id);
print('<div align="center">Please type in the username and password for the node administrator.</div');
//print("<p>Node administrator is: $adminName</p>");
print('<br /><br /><DIV ALIGN="center">
	Username: <INPUT type="text" name="admin_name" value="' . $admin_name .'"><BR />
	Password: <INPUT type="password" name="admin_pass" value="' .'"><BR /><BR />
	</DIV>');

	PrintButton($id);

/*
	$id = ;	//////////////////////////Test 
	if (RunTest($id, ""))
	{
	
		if ()
		{
			$install_test_result[$id] = "";
			$install_color[$id] = $install_red;
		}
		else
		{
			$install_test_result[$id] = "OK";
			$install_color[$id] = $install_green;
		}
	}
	PrintTitle($id);
	PrintButton($id);
*/


?>

<!-- </TABLE> -->
</DIV>
<?php
	$install_erroronpage = false;			//test if any errors on page
	for ($i=1; $i <= $install_maxtests; $i++)	//run through all tests (nr.0 is no test)
	{
		if ($install_color[$i] != $install_green) $install_erroronpage = true;	//if not green set error true
	}
	if ($install_erroronpage) {
    print('<DIV ALIGN="center"><BR /><BIG>Please run again the \'red marked\' tests!</BIG><BR /></DIV>');	//if no error write 'ALL OK'
	} elseif(($install_node_user != $config['nodeDbUser']) OR ($install_node_pass != $config['nodeDbPasswd'])
				OR ($install_node_host != $config['nodeDbHost']) OR ($install_node_port != $config['nodeDbPort'])
				OR ($install_node_db_name != $config['nodeDbName']) 
				OR (!$config['selfUserDb'] 
					 AND (($install_sadm_user != $config['userDbUser'])
							OR ($install_sadm_pass != $config['userDbPasswd']) OR ($install_sadm_host != $config['userDbHost'])
							OR ($install_sadm_port != $config['userDbPort']) OR ($install_sadm_db_name != $config['userDbName'])))
          ) {print('<DIV ALIGN="center"><BR /><BIG>The database settings here do not match with the setting in config.inc.php, please update it.</BIG><BR /></DIV>');	//if no error write 'ALL OK'
	} else {
	  print('<DIV ALIGN="center"><BR /><BIG>ALL OK, you are now ready to use the <A HREF="../index.php">system</A>. Log in using the admin login.</BIG><BR /></DIV>');	//if no error write 'ALL OK'
  }

?>
<DIV ALIGN="center">
<BR /><INPUT type="submit" name="RUN_ALL" value="RUN ALL">
</DIV>
</FORM>
</BODY>
</HTML>
