<?php

/*  -*- tab-width: 3; indent-tabs-mode: 1; -*-
 * $Id: sotf_Vars.class.php,v 1.3 2003/01/30 15:30:34 andras Exp $
 * Created for the StreamOnTheFly project (IST-2001-32226)
 * Authors: Andr�s Micsik, M�t� Pataki, Tam�s D�ri 
 *          at MTA SZTAKI DSD, http://dsd.sztaki.hu
 */

class sotf_Vars {

  var $db;

  var $table;

  var $isInitialized = false;

  var $vars;

  function sotf_Vars($db, $table_name) {
    $this->db = $db;
    $this->table = $table_name;
    $this->initialize();
  }

  function initialize() {
    $res = $this->db->getAll("SELECT name, value FROM $this->table");
    if(DB::isError($res))
      raiseError($res);
    while(list(,$value) = each($res)) {
      $this->vars[$value['name']] = $value['value'];
    }
  }

  function getAll() {
    return $this->vars;
  }

  function get($variable_name, $defaultValue) {
    if(isset($this->vars[$variable_name]))
      return $this->vars[$variable_name];
    else
      return $defaultValue;
  }

  function set($name,$val) {
    $name = sotf_Utils::magicQuotes($name);
    $val = sotf_Utils::magicQuotes($val);
    if(isset($this->vars[$name]))
      $update = 1;
    $this->vars[$name] = $val;
    if($update)
      $result = $this->db->query("UPDATE $this->table SET value='$val' WHERE name='$name'");
    else
      $result = $this->db->query("INSERT INTO $this->table (name,value) VALUES('$name', '$val')");
    if(DB::isError($result)) 
      raiseError($result);
    debug("setvar", "$name=$val");
  }

}
