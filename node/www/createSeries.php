<?php
// -*- tab-width: 3; indent-tabs-mode: 1; -*-
// $Id: createSeries.php,v 1.2 2002/12/10 17:36:13 andras Exp $

require("init.inc.php");

$page->popup = true;
$page->forceLogin();

$stationId = sotf_Utils::getParameter('stationid');
$seriesTitle = sotf_Utils::getParameter('title');

if(!hasPerm($stationId, "create")) {
  raiseError("You have no permission to create new series!");
}

if($seriesTitle) {
  // create a new series
  $series = new sotf_Series();
  $series->set('title', $seriesTitle);
  $series->set('station_id', $stationId);
  $series->set('entry_date', date('Y-m-d'));
  $status = $series->create();
  if(!$status) {
    $page->addStatusMsg('series_create_failed');
  } else {
    $permissions->addPermission($series->id, $user->id, 'admin');
    $page->redirect("editSeries.php?seriesid=" . $series->id);
    exit;
  }
}

// general data
$smarty->assign("TITLE", $seriesTitle);

$page->sendPopup();

?>
