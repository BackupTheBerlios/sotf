<?php // -*- tab-width: 3; indent-tabs-mode: 1; -*-

/*
 * $Id: sotf_Node.class.php,v 1.11 2003/05/26 13:11:09 andras Exp $
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

}

?>
