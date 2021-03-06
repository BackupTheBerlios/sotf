<?php

/*  
 * $Id: portal_upload.php,v 1.5 2003/06/23 14:16:44 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: M�t� Pataki, Andr�s Micsik
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 * 
 */

require("portal_login.php");

$settings = $portal->loadSettings();	//load saved portal settings

$type = sotf_Utils::getParameter('type');
$data = sotf_Utils::getParameter('data');
$name = sotf_Utils::getParameter('name');
$portal_password = sotf_Utils::getParameter('portal_password');
$submit = sotf_Utils::getParameter('submit');

$d = array();
if ($type == "query") {$d['query'] = $data; $d['name'] = $name;}
else $d = $data;
$result = $portal->uploadData($type, $d, $portal_password);
//if ($result == "OK") $page->redirect($rootdir."/closeAndRefresh.php");		//close window
//else

if (isset($submit)) $smarty->assign("error", $result);						//error message in $result


////SMARTY
//directories and names
$smarty->assign("rootdir", $rootdir);				//root directory (portal/www)
$smarty->assign("php_self", $_SERVER['PHP_SELF']);		//php self for the form submit and hrefs
$smarty->assign("portal_name", $portal_name);			//name of the portal

$smarty->assign("portal", $settings["portal"]);

//upload data
$smarty->assign("type", $type);
$smarty->assign("data", $data);
$smarty->assign("name", $name);

$page->send("portal_upload.htm");
?>