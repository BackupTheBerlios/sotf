<?php

require("init.inc.php");

/*  -*- tab-width: 3; indent-tabs-mode: 1; -*-
 * $Id: topics.php,v 1.3 2003/02/04 15:00:34 andras Exp $
 *
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: András Micsik, Máté Pataki, Tamás Déri
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 *          Koulikov Alexey - alex@pvl.at
 */

$smarty->assign("openTree", sotf_Utils::getParameter("open"));
$smarty->display("topics.htm");

?>