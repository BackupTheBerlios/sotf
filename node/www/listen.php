<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*- 

/*  
 * $Id: listen.php,v 1.17 2003/09/16 12:00:06 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

require("init.inc.php");

if(sotf_Utils::getParameter('reconnect')) {
  $playlist = new sotf_Playlist();
  $playlist->sendMyRemotePlaylist();
  $page->logRequest();
  exit;
}

if(sotf_Utils::getParameter('stop')) {
  $playlist = new sotf_Playlist();
  $playlist->stopMyStream();
  $page->redirect(myGetenv('HTTP_REFERER'));
  exit;
}

$id = sotf_Utils::getParameter('id');
$fileid = sotf_Utils::getParameter('fileid');
$jingle = sotf_Utils::getParameter('jingle');

if(empty($id)) {
  raiseError("Missing parameters!");
}

$playlist = new sotf_Playlist();

if($jingle) {
  $obj = $repository->getObject($id);
  if(!$obj)
	 raiseError("no_such_object");
  if(!$obj->isLocal()) {
	 // have to send user to home node of this programme
	 sotf_Node::redirectToHomeNode($obj, 'listen.php');
	 exit;
  }
  $playlist->addJingle($obj);
} else {
  // add normal programme 
  $prg = new sotf_Programme($id);
  
  if(!$prg->isLocal()) {
	 // have to send user to home node of this programme
	 sotf_Node::redirectToHomeNode($prg, 'listen.php');
	 exit;
  }
  
  $playlist->addProg($prg, $fileid);
}
  
$playlist->startStreaming();

// must start stream before! otherwise we don't know stream url
$playlist->sendRemotePlaylist();

$page->logRequest();

?>