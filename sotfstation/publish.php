<?
	/****************
	* SOTF Station Management Tool
	*********************************
	* Author: Kulikov Alexey - alex@pvl.at, alex@ita-studio.com
	*********************************
	* Please bear in mind, that this software was written for _fun_ =) 
	*************************/

	/************************
	* Template Page Using the pre-build page generation framework
	*----------------------------------------
	* Purpose of page goes here
	************************/
	include("init.inc.php");										# include the global framwork
	include("classes/packXML.class.php");				# include data wrapper
	//$myNav->add($SECTION[403],'index.php');		# add entry to Navigation Bar Stack
	//authorize('edit_station');								# check access rights
	
	//may i do this?
	//can I edit this? (this is my authorize!)
	if(($_SESSION['USER']->get("edit_station")==2) or ($_SESSION['USER']->get("auth_id") == $db->getOne("SELECT series.owner FROM programme LEFT JOIN series ON (programme.series_id = series.id) WHERE programme.id = '$_GET[id]'"))){
		$mod_flag = TRUE;
	}else{
		$myError->add($ERR[26]);
	}
	
	if($_GET['action'] == 'publish'){
		//check if all meta data has been entered
		$myProg = $db->getRow("SELECT
															id AS prog_id,
															intime AS prog_intime,
															outtime AS prog_outtime,
															title AS prog_title,
															alt_title AS prog_alt_title,
															keywords AS prog_keywords,
															description AS prog_desc,
															contributors AS prog_contrib,
															created AS prog_datecreated,
															issued AS prog_dateissued,
															topic AS prog_topic,
															genre AS prog_genre,
															lang AS prog_lang,
															rights AS prog_rights,
															active AS prog_active
														FROM programme WHERE id = '$_GET[pid]'",DB_FETCHMODE_ASSOC);
		
		if(!$myError->checkLength($myProg['prog_keywords'],2) 
				or !$myError->checkLength($myProg['prog_desc'],2) 
				or !$myError->checkLength($myProg['prog_datecreated'],2) 
				or !$myError->checkLength($myProg['prog_dateissued'],2) 
				or !$myError->checkLength($myProg['prog_topic'],1) 
				or !$myError->checkLength($myProg['prog_genre'],1) 
				or !$myError->checkLength($myProg['prog_lang'],1) 
				or !$myError->checkLength($myProg['prog_rights'],1))
		{
			if($mod_flag){
				$myError->add($ERR[27]);
			}
		}
		
		//check if programme is active
		if($myProg['prog_active'] != 't'){
			$myError->add($ERR[28]);
		}
		
		//only if no errors occured, then publish
		if($myError->getLength()==0){
			
			//prepare XML data
			$mySeries = $db->getRow("SELECT
																	id AS series_id,
																	owner AS owner,
																	title AS series_title,
																	description AS series_desc
															 FROM series WHERE id = '$_GET[id]'",DB_FETCHMODE_ASSOC);
			
			$myUser = $db->getRow("SELECT
																	name AS user_name,
																	role AS user_role 
														 FROM user_map WHERE auth_id = '$mySeries[owner]'",DB_FETCHMODE_ASSOC);
			
			//pack XML data
			$myPack = new packXML('sotfPublish');
			$myPack->addData(array_merge($mySeries,$myProg,$myUser));
			$myPack->toFile(PROG_DIR . $_GET['pid'] . "/Metadata.xml");
			
			//echo "tar.sh " . PROG_DIR . $_GET['pid'] . " ../" . $_GET['pid'] . ".tgz";
			system("tar.sh " . PROG_DIR . $_GET['pid'] . " ../" . SOTF_STATION_ID . "_" . $_GET['pid'] . ".tgz");
			chmod(PROG_DIR . SOTF_STATION_ID . "_" . $_GET['pid'] . ".tgz",0777);
			system("mv " . PROG_DIR . SOTF_STATION_ID . "_" . $_GET['pid'] . ".tgz " . SYNC_DIR);
			
			//mark programme as published
			$db->query("UPDATE programme SET published = '" . date("Y-m-d H:i:s") . "' WHERE id = '$_GET[pid]'");
			
			
			//close window
			unset($_GET['pid']);
			unset($_GET['action']);
			reset($_GET);
			while(list($key,$val) = each($_GET)){
				$get_stuff[] = $key . "=" . $val;
			}
			$get_stuff = implode("&",$get_stuff);
			
			$smarty->assign(array("window_destroy"=>true,"destination"=>"myseries.php","get_data"=>$get_stuff));
		}
	}else if($_GET['action'] == 'unpublish'){
		if($myError->getLength()==0){
			//remove files
		
			//notify node of changes
		
			//update database
			$db->query("UPDATE programme SET published = NULL WHERE id = '$_GET[pid]'");
		
			//close window
			unset($_GET['pid']);
			unset($_GET['action']);
			reset($_GET);
			while(list($key,$val) = each($_GET)){
				$get_stuff[] = $key . "=" . $val;
			}
			$get_stuff = implode("&",$get_stuff);
		
			$smarty->assign(array("window_destroy"=>true,"destination"=>"myseries.php","get_data"=>$get_stuff));
		}	
	}//end if action
	
	//create help message
	//$myHelp = new helpBox(1);										# this will fetch a help message from the database and output it
																								# in the template (if allowed to do so)																						
	//page output :)
	//pageFinish('noaccess.htm');									# enter the desired template name as a parameter
	pageFinishPopup('publish.htm');								# same as above but in a popop
?>