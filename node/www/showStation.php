<?php
require("init.inc.php");

$hitsPerPage = $sotfVars->get("hitsPerPage", 30);

$stationid = sotf_Utils::getParameter('stationid');
$start = sotf_Utils::getParameter('start');

if(!$stationid)
     raiseError("No station selected!");

$st = & new sotf_Station($stationid);

$page->errorURL = "showStation.php?stationid=$stationid";
$page->setTitle($st->get('name'));

// delete series
$delseries = sotf_Utils::getParameter('delseries');
if($delseries) {
  $seriesid = sotf_Utils::getParameter('seriesid');
  $series = new sotf_Series($seriesid);
  $series->delete();
  $page->redirect("showStation.php?stationid=$stationid#series");
  exit;
}

// delete prog
$delprog = sotf_Utils::getParameter('delprog');
$prgid = sotf_Utils::getParameter('prgid');
if($delprog) {
  $prg = new sotf_Programme($prgid);
  $prg->delete();
  $page->redirect("showStation.php?stationid=$stationid#progs");
  exit;
}

// generate output

$smarty->assign('STATION_ID',$stationid);
$smarty->assign('STATION',$st->get('name'));
$smarty->assign('STATION_DATA',$st->getAll());
if($st->isLocal()) {
  $smarty->assign('IS_LOCAL',1);
}
$smarty->assign('ROLES', $st->getRoles());

if ($st->getIcon()) {
  $smarty->assign('ICON','1');
  $st->cacheIcon();
}

if ($entered)
     $smarty->assign('ENTERED',$entered);
if (!$start)
     $start = 0;

$seriesList = $st->listSeries();
if(!empty($seriesList)) {
  while(list(,$series) = each($seriesList)) {
    $sd = $series->getAll();
    $sd['count'] = $series->numProgrammes();
    $series->cacheIcon();
    $seriesData[] = $sd;
  }

  $smarty->assign('SERIES', $seriesData);
}

$numProgs = $st->numProgrammes();
$limit = $page->splitList($numProgs, $_SERVER["REQUEST_URI"]);
$progs = $st->listProgrammes($limit["from"] , $limit["maxresults"]);

if($progs) {

  while(list(,$prog) = each($progs)) {
    $prog->cacheIcon();
    $pd = $prog->getAll();
    $progList[] = $pd;
  }
  $smarty->assign('PROGS',$progList);
}

$page->send();

?>