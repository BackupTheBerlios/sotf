<?
	/****************
	* SOTF Station Management Tool
	*********************************
	* Author: Kulikov Alexey - alex@pvl.at, alex@ita-studio.com
	*********************************
	* Please bear in mind, that this software was written for _fun_ =) 
	*************************/

	/************************
	* Configuration File :: 07.11.2002
	*----------------------------------------
	* Feel free to edit all the setting in this file, but please
	* take care, since the application depends on these few lines
	* of code ;)
	* 
	* An important note: to spare resources and the avoid the need
	* of declaration of global variables constants are used in order
	* to represent global values. Constants are defined as follown
	* 
	*    define(sting name, mixed value[, int case sensitive]);
	************************/
	
	//Database Access Parameters, read http://pear.php.net for docs
	define('DB_TYPE','pgsql');																# Type of Database :: accepted (string)
	define('DB_HOST','localhost');														# Where the Database is located :: accepted (string)
	define('DB_NAME','sotf_station');													# Database Name :: accepted (string)
	define('DB_USER','Dolce');																	# Database User :: accepted (string)
	define('DB_PASS','1234');																	# Database Access Password :: accepted (string)
	
	/*
	* Now, there are TWO ways to get around the the user authentication database, in case it runs on
	* your local server, then the ideal way will be to build a direct connection to it (SADM), otherwise
	* a wise thing will be to specify an XML RPC verification routine.
	* 
	* Below are the settings for both
	*/
	define('DIRECTSADM_ACCESS',TRUE);													# specifies whether SADM runs on a local DB server: accepted (bool)
	
	//if the above is set to true, then the following
	//have a meaning
	define('SDB_TYPE','pgsql');																# Type of Database :: accepted (string)
	define('SDB_HOST','localhost');									# Where the Database is located :: accepted (string)
	define('SDB_NAME','temp');																# Database Name :: accepted (string)
	define('SDB_USER','Dolce');																# Database User :: accepted (string)
	define('SDB_PASS','1234');																# Database Access Password :: accepted (string)
	
	//if SADM is accessed via XMLRPC then the
	//following has a meaning
	define('SADM_HOST','localhost');													# location of SADM
	define('SADM_SERVER','/work/sadm3/server.php');						# location of the XMLRPS SADM Server
	define('SADM_PORT',80);																		# SADM XMLRPC Access Port
	
	define('SADM_GROUP',1);																	# This is the default member id, to which all the station users will belong to
	
	//SOTF META DATA CONFIG
	define('SOTF_PUB','Radio Tienes');												# publisher of the resorces
	define('SOTF_PUB_ACR','RT');															# publisher of the resorces acronym
	define('SOTF_PUB_MAIL','radio@tienes.com');								# publisher of the resorces e-mail
	define('SOTF_PUB_ADR','Vienna is a big city');						# publisher of the resorces e-mail
	define('SOTF_PUB_URI','http://www.pvl.at/');							# link to the homepage
	define('SOTF_PUB_LOGO','http://www.pvl.at/logo.gif');			# link to logo
	define('SOTF_PUB_ROLE','Editor');													# publisher role scheme="EBU v1"
	
	define('SOTF_NODE','AA1');																# id of the node
	define('SOTF_NODE_URI','http://www.hello.com');						# uri of the node
	define('SOTF_STATION_ID','1121');													# id of THIS station on the node netwORK
	
	
	/*
	* The critical settings are now configured, below you will find other data to play with that
	* controls the way your station looks and behaves
	*/
	//Mail Settings
	define('MAILBOT','you@domain.at');													# from this address all notifications will be sent
	
	//Names
	define('STATION_NAME','Da Station');											# Name of this station
	define('HOME_NAME','Home');																# Name of the homepage
	
	//Paths
	define('TPL_DIR','templates');														# Templates are found in this directory (relative) :: accepted (string)
	define('SRC_ROOT','http://localhost/work/sotfstation/');	# The program is located under this URL (absolute) :: accepted (string)
	define('SYNC_DIR','sync/');																# xmbf export gateway
	define('PROG_DIR','progs/');															# raw mp3 and file storage before publishing
	define('UA_DIR','uncutaudio/');														# where recorder day feeds reside
	
	define('PEAR_PATH','');																		# Path to Pear, by default it is empty, if PHP has been compiled correctly :: accepted (string)
	
	define('SMARTY_PATH','../../smarty');														# Path to the template parser, by default it is included with this distro
																														# and you don't have to edit this entry, but you may wish to use the external
																														# library. (relative) :: accepted (string)
	
	define('XMLRPC_PATH','xmlrpc');														# Path to the XMLRPC routines, by default these are included with this distro
																														# and you don't have to change this entry, but you may with to use an external
																														# library. (relative) :: accepted (string)
	
	//Other Settings
	define('SPLITOFFSET', 30);																# mp3 Split offset in seconds
	define('ALLOW_GZIP',TRUE);																# Either allow or dissallow output compression :: accepted (bool)
	define('ALLOW_LOGS',TRUE);																# Either allow or dissallow to log user's action :: accepted (bool)
	define('NOW',time());																			# Current Timestamp :: accepted (int)
?>