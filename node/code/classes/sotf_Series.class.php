<?php
// -*- tab-width: 3; indent-tabs-mode: 1; -*-
// $Id: sotf_Series.class.php,v 1.3 2002/11/08 16:42:56 andras Exp $

/**
* Models a series
*
* @author Andras Micsik SZTAKI DSD micsik@sztaki.hu
*/
class sotf_Series extends sotf_RepBase {		

  var $roles;

  var $access;

   /**
     * Constructor: loads the object from database if ids are given
     *
     * @param string tablename name of SQL table to store
     * @param string node node id
     * @param string id id within node
   */
  function sotf_Series($nodeId='', $id=''){
    parent::constructor('sotf_series', $nodeId, $id);
    // load roles
    $r = $this->db->getAll("SELECT node_id, id FROM sotf_series_roles WHERE series_id='$id' AND node_id='$nodeId'");
    while (list (, $val) = each ($r)) {
      $this->roles[$val['node_id'] . '_' . $val['id']] = new sotf_Role('sotf_series_roles', $val['node_id'], $val['id']);
    }
    // load access rights
  }

  /** get number of published programmes */
  function numProgrammes($onlyPublished = true) {
    $node = $this->node;
    $id = $this->id;
    $station = $this->data['station'];
    $sql = "SELECT COUNT(*) FROM sotf_programmes WHERE node_id = '$node' AND series_id='$id'";
    if($onlyPublished)
      $sql .= " AND published='t'";
    $count = $this->db->getOne($sql);
    if (DB::isError($count))
      raiseError($count->getMessage());
    else
      return $count;
  }

  function listProgrammes($start, $num, $onlyPublished = true) {
    $node = $this->node;
    $id = $this->id;
    $sql = "SELECT node_id, id FROM sotf_programmes WHERE node_id = '$node' AND series_id='$id'";
    if($onlyPublished)
      $sql .= " AND published='t' ";
    $sql .= " ORDER BY entry_date DESC,track ASC";
    if ($num) {
      if ($num < 0)
        $num = 0;
      $sql .= " LIMIT $num OFFSET $start";
    }
    $res = $this->db->getAll($sql);
    if(DB::isError($res))
      raiseError($res->getMessage());
    foreach($res as $item) {
      $list[] = new sotf_Programme($item['node_id'], $item['id']);
    }
    return $list;
  }


}	

?>
