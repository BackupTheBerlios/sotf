<?
	/****************
	* SOTF Station Management Tool
	*********************************
	* Author: Kulikov Alexey - alex@pvl.at, alex@ita-studio.com
	*********************************
	* Please bear in mind, that this software was written for _fun_ =) 
	*************************/

	/************************
	* Inside - the first page that the anyone sees after login
	*----------------------------------------
	* This page will display the whole station schedule including the
	* past and the future
	************************/
	include("init.inc.php");							# include the global framwork
	include("classes/calendar.class.php");				# calendar handler
	include("classes/weekview.class.php");				# current day calendar handler
	$myNav->add($SECTION[INSIDE],'index.php');			# add entry to Navigation Bar Stack
	authorize('edit_station',false);					# check access rights
	
	//fix missing date (if any)
	if(!$_GET['date']){
		$_GET['date'] = date("d-m-Y");
	}
	
	//#################################### THIS WEEK'S SHOWS #####################################
	//get this week's programms ;)
	//process limits
	$myDate = explode("-",$_GET['date']);
	
	$myDay = new weekview($myDate);						# create the new week representer
	$start = $myDay->getStart();
	$end = $myDay->getEnd();
	
	//run the query
	$shows = $db->getAll("SELECT 
										series.owner AS series_owner, 
										series.title AS series_title,
										programme.id AS prog_id,
										programme.title AS prog_title,
										EXTRACT(EPOCH FROM programme.intime) AS intime,
										EXTRACT(EPOCH FROM programme.outtime) AS outtime,
										programme.special AS special,
										user_map.name AS owner_name FROM
										series 
										LEFT JOIN programme ON (series.id = programme.series_id)
										LEFT JOIN user_map ON (series.owner = user_map.auth_id)
										WHERE (programme.intime > '$start' AND programme.intime < '$end')
										OR (programme.outtime > '$start' AND programme.outtime < '$end')
										ORDER BY intime",DB_FETCHMODE_ASSOC);
	
	//process the resultset									 
	while(list($key,$val) = each($shows)){
		if(empty($val['prog_title'])){									# if no programme title has been specifid, take the series title
			$val['prog_title'] = $val['series_title'];
		}
		
		//add calendar blocks
		$myDay->add($val);
	}
	
	//output to smarty
	$smarty->assign('day',$myDay->out());
	//#################################### END TODAY'S SHOWS ######################################
															
	//page output :)	
	pageFinish('weekview.htm');											# enter the desired template name as a parameter
?>