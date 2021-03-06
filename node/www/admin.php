<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*- 

/*  
 * $Id: admin.php,v 1.29 2005/09/09 15:02:17 micsik Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

require("init.inc.php");

// may contain long jobs
set_time_limit(300);

$smarty->assign('PAGETITLE',$page->getlocalized('AdminPage'));

$page->forceLogin();

//$page->errorURL = "admin.php";

checkPerm('node', 'change', 'authorize');

// import XBMF
$xbmfFile = sotf_Utils::getParameter('import_xbmf');
if($xbmfFile) {
  $file = $config['xbmfInDir'] . "/$xbmfFile";
  $id = sotf_Programme::importXBMF($file, $config['publishXbmf'], true);
  if($id) {
	 echo "Import succesful: <a target=\"_opener\" href=\"editMeta.php?id=$id\">click here</a>";
	 unlink($file);
  } else {
	 echo "Import failed";
  }
  $page->logRequest();
  exit;
}

// update CVS
if(sotf_Utils::getParameter('updatecvs')) {
  die("doesn't work this way, sorry");
  checkPerm('node', 'change');

  chdir($config['basedir']);
  header("Content-type: text/plain\n");
  system('cvs update');
  //$page->redirect("admin.php");
  $page->logRequest();
  exit;
}

// recompile Smarty templates
if(sotf_Utils::getParameter('retemplate')) {
	checkPerm('node', 'change');
  $smarty->clear_compiled_tpl();
  $page->redirect("admin.php");
  $page->logRequest();
  exit;  
}

// update topic counts
if(sotf_Utils::getParameter('updatetopics')) {
  $vocabularies->updateTopicCounts();
  $page->redirect("admin.php");
  $page->logRequest();
  exit;  
}

// save general data
if(sotf_Utils::getParameter('save_debug')) {
	checkPerm('node', 'change');
  $sotfVars->set('debug', sotf_Utils::getParameter('debug'));
  $sotfVars->set('debug_sql', sotf_Utils::getParameter('debug_sql'));
  $sotfVars->set('debug_smarty', sotf_Utils::getParameter('debug_smarty'));
  $sotfVars->set('smarty_compile_check', sotf_Utils::getParameter('smarty_compile_check'));
  $page->redirect("admin.php");
  $page->logRequest();
  exit;
}

// save network data
if(sotf_Utils::getParameter('save')) {
	checkPerm('node', 'change');
  $desc = sotf_Utils::getParameter('desc');
  $localNode = sotf_Node::getLocalNode();
  $localNode->set('description', $desc);
  $localNode->update();
  $page->redirect("admin.php#network");
  $page->logRequest();
  exit;
}

// sync
if(sotf_Utils::getParameter('sync')) {
	checkPerm('node', 'change');
  // this can be long duty!
  set_time_limit(18000);
  // get neighbour object
  $nid = sotf_Utils::getParameter('nodeid');
  $neighbour = sotf_Neighbour::getById($nid);
  // full sync?
  if(sotf_Utils::getParameter('full'))
	 sotf_NodeObject::newNodeInNetwork($nid);
  // sync
  $neighbour->sync(true);
  if($config['debug'])
	 echo "<h3>Sync completed.</h3>";
  else
	 $page->redirect("admin.php#network");
  $page->logRequest();
}

// delete neighbour
if(sotf_Utils::getParameter('delneighbour')) {
	checkPerm('node', 'change');
  debug("delete neighbour", "!!");
  $nid = sotf_Utils::getParameter('nodeid');
  $neighbour = sotf_Neighbour::getById($nid);
  $neighbour->delete();
  $page->redirect("admin.php#network");
  $page->logRequest();
}

// manage permissions
$delperm = sotf_Utils::getParameter('delperm');
if(sotf_Utils::getParameter('delperm')) {
	checkPerm('node', 'authorize');
  $userid = sotf_Utils::getParameter('userid');
  if(empty($userid) || !is_numeric($userid)) {
    raiseError("Invalid userid: $userid");
  }
  $username = $user->getUsername($userid);
  if(empty($username)) {
    raiseError("Invalid userid: $userid");
  }
  $permissions->delPermission('node', $userid);
  $msg = $page->getlocalizedWithParams("deleted_permissions_for", $username);
  $page->addStatusMsg($msg, false);
  $page->redirect("admin.php");
  $page->logRequest();
  exit;
}

// generate output

$localNode = sotf_Node::getLocalNode();
if(!$localNode) {
	// clear old entry
	$localNode = new sotf_Node();
	$localNode->set('name', $config['nodeName']);
	$localNode->find();
	if($localNode->exists())
		$localNode->delete();
	// create local node entry if does not exist
  $localNode = new sotf_Node();
  $localNode->set('node_id', $config['nodeId']);
  $localNode->set('name', $config['nodeName']);
  $localNode->set('url', $config['rootUrl']);
  $localNode->create();
}

$smarty->assign("LOCAL_NODE", $localNode->getAll());

// nodes
//$nodes = sotf_Node::countAll();
//$smarty->assign('NODES',$nodeData);

// neighbours
//$neighbours = sotf_Neighbour::listAll();
$nodes = sotf_Node::listAll();
while(list(,$node)= each($nodes)) {
  $nodeId = $node->get('node_id');
  if($nodeId == $config['nodeId'])
	 continue;
  $data = $node->getAll();
  $nei = sotf_Neighbour::getById($nodeId);
  if($nei)
    $data['neighbour'] = $nei->getAll();
  $data['pending_objects'] = $db->getOne("select count(*) from sotf_object_status where node_id='$nodeId'");
  $data['pending_forwards'] = $db->getOne("select count(*) from sotf_to_forward where node_id='$nodeId'");
  $neighbors = $data['neighbours'];
  debug("X0", $data['neighbours']);
  $neighbors = str_replace("io","&lt;-&gt;",$neighbors);
  $neighbors = str_replace("i","&lt;-",$neighbors);
  $neighbors = str_replace("o","-&gt;",$neighbors);
  $data['neighbours'] = explode(',', $neighbors);
  debug("XX", $data['neighbours']);
  $nodeData[] = $data;
}
$smarty->assign('NODES',$nodeData);

// user permissions: editors and managers
$smarty->assign('PERMISSIONS', $permissions->listUsersAndPermissionsLocalized('node'));

// arriving xbmf
$dirPath = $config['xbmfInDir'];
$dir = dir($dirPath);
while($entry = $dir->read()) {
	if ($entry != "." && $entry != "..") {
		$currentFile = $dirPath . "/" .$entry;
		//debug("examining", $currentFile);
		if (!is_dir($currentFile)) {
			$XBMF[] = basename($currentFile);
		}
	}
}
$dir->close();
$smarty->assign("XBMF", $XBMF); 

// variables
$smarty->assign("VARS", $sotfVars->getAll());

$page->send();

?>
