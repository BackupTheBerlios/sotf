<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*- 

/*  
 * $Id: help.php,v 1.3 2003/06/25 14:57:55 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

require("config.inc.php");

$action = $_GET['action'];
$lang = $_GET['lang'];

header("Location: " . $config['localPrefix'] . "/help/index.$lang.html#$action");

?>