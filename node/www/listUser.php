<?php  // -*- tab-width: 3; indent-tabs-mode: 1; -*- 

/*  
 * $Id: listUser.php,v 1.2 2003/03/05 09:11:40 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: András Micsik, Máté Pataki, Tamás Déri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

require("init.inc.php");

$users = sotf_User::listUsers();
$smarty->assign('USERS',$users);
$smarty->display("listUsers.htm");
?>
