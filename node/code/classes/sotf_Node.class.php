<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*-

/*
 * $Id: sotf_Node.class.php,v 1.20 2005/07/07 09:56:25 micsik Exp $
 *
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri
 *					at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

class sotf_Node extends sotf_NodeObject {

	var $tablename = 'sotf_nodes';

	function sotf_Node($id='', $data='') {
		$this->sotf_NodeObject($this->tablename, $id, $data);
	}

	/** 
	 * @method static getNodeById
	 */
	function getNodeById($nodeId) {
		global $db;
		$id = $db->getOne("SELECT id FROM sotf_nodes WHERE node_id = '$nodeId'");
		if(DB::isError($id))
			raiseError($id);
		if($id)
			return new sotf_Node($id);
		else
			return NULL;
	}

	/** 
	 * @method static getLocalNode
	 */
	function getLocalNode() {
		global $db, $config;
		return sotf_Node::getNodeById($config['nodeId']);
	}

	/** static */
	function redirectToHomeNode($obj, $script) {
	  global $page;

	  $url = sotf_Node::getHomeNodeRootUrl($obj);
	  $oldParams = substr(strstr(myGetenv("REQUEST_URI"), '.php'), 4);
	  $url = $url . "/$script" . $oldParams;
	  $page->redirect($url);
	  exit;
	}

	/** static */
	function getHomeNodeRootUrl($obj) {
	  if($obj->isLocal()) {
		 global $config;
		 return $config['rootUrl'];
	  } else {
		 $node = sotf_Node::getNodeById($obj->getNodeId());
		 if(!$node) {
			raiseError("Could not find home node for programme: " . $obj->id);
		 }
		 return $node->get('url');
	  }
	}

	/** returns a list of all such objects: can be slow!!
	 * @method static listAll
	 */
	function listAll() {
		global $db;
		$sql = "SELECT * FROM sotf_nodes ORDER BY name";
		$res = $db->getAll($sql);
		if(DB::isError($res))
			raiseError($res);
		$slist = array();
		foreach($res as $st) {
			$slist[] = new sotf_Node($st['id'], $st);
		}
		return $slist;
	}

	/** 
	 * @method static countAll
	 */
	function countAll() {
		global $db;
		return $db->getOne("SELECT count(*) FROM sotf_nodes");
	}

  /**************************************************
	*
	*					  MESSAGE FORWARD SUPPORT
	*
	**************************************************/

	var $objectsPerRPCRequest = 50;
	
	function forwardObjects() {
	  global $db, $config;
	  
	  global $page;
	  if(!$console && $this->getBool('use_for_outgoing')) {
		 debug("node $this->id is not used for outgoing sync");
		 return;
	  }
	  debug("FORWARDING TO ", $this->get("node_id"));

	  $rpc = new rpc_Utils;
	  if($config['debug'])
		 $rpc->debug = true;
	  $timestamp = $db->getTimestampTz();
	  $remoteId = $this->get('node_id');
	  $url = $this->get('url');
	  // remove trailing '/'
	  while(substr($url, -1) == '/')
		 $url = substr($url, 0, -1);
	  // calculate chunking
	  $thisChunk = 1;
	  // do XML-RPC conversation
	  $objectsSent = 0;
	  $more = sotf_NodeObject::countForwardObjects($remoteId);
	  if(!$more)
		 debug("No new objects to send");
	  while($more) {
		 $db->begin(true);
		 $modifiedObjects = sotf_NodeObject::getForwardObjects($remoteId, $this->objectsPerRPCRequest);
		 $more = sotf_NodeObject::countForwardObjects($remoteId);
		 $chunkInfo = array('this_chunk' => $thisChunk,
								  'from_node' => $config['nodeId'],
								  'objects_remaining' => $more
								 );
		 debug("chunk info", $chunkInfo);
		 debug("number of sent objects", count($modifiedObjects));
		 $objectsSent = $objectsSent + count($modifiedObjects);
		 $objs = array($chunkInfo, $modifiedObjects);
		 $response = $rpc->call($url . "/xmlrpcServer.php/forward/$thisChunk", 'sotf.forward', $objs);
		 // error handling
		 if(is_null($response)) {
			$db->rollback();
			return;
		 }
		 $db->commit();
		 $replyInfo = $response[0];
		 debug("replyInfo", $replyInfo);
		 $thisChunk++;
	  }

	  debug("total number of objects sent",$objectsSent );
	  //$this->log($console, "number of updated objects: " .count($updatedObjects));
	}

  function forwardResponse($chunkInfo, $objects) {
	 global $db;

	 $remoteId = $this->get('node_id');
	 // save modified objects
	 $db->begin(true);
	 $updatedObjects = sotf_NodeObject::saveForwardObjects($objects);
	 // if db error: don't commit!
	 $db->commit();
	 debug("number of processed forward objects", $updatedObjects);
	 $replyInfo = array('received' => count($objects),
							  'updated' => $updatedObjects);

	 if($chunkInfo['objects_remaining'] == 0) {
		// last chunk, do something useful!!
		//temporarily taken out sotf_Object::doUpdates();
	 }
	 return array($replyInfo);
  }

}

?>
