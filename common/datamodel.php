<?php

/**
* Class to encapsulate data access.
*
**/

class DataModel {
  private $xml = array ("armor" => "armor.xml");
  private $db = array (
    "race" => "SELECT * FROM `race`",
    "subrace" => "SELECT * FROM `race`",
    "forcesensitive" => "SELECT `has_force` FROM `forcerestrict`",
    "prereq" => "SELECT `prereq_id`, `specialization`, `group_id`, `spec_type` FROM `prereq` ",
    "racebonus" => "SELECT a.`num_choices`, c.`name`, a.`group_id`, b.`skill_id`, c.`spec`, c.`specreq`, c.`descr`, b.`specialization`, b.`level`, b.`prereq_exempt`, b.`auto_grant`, c.`type` FROM `bonus_meta` a, `bonus_skill` b, `skill` c ",
    "skill"=> "SELECT * FROM `skill`"
  );

  private $linkedConstraints = array(
    "racebonus" => "AND a.`race_id` = b.`race_id` AND a.`group_id` = b.`group_id` AND b.`skill_id` = c.`skill_id`"
  );
  private $sourceType;
  private $source;
  private $constraints = array();
  private $sortFields = array();
  private $params = array();
  private $rowLimit = "";
  private $fullSQL = "";

  /**
  * @param string $source Name of data type to be retrieved. Will either match a database table name or correspond to an XML file.
  *
  **/

  function __construct ($source) {
    # echo "source=$source" . PHP_EOL;
     #echo var_export($this->xml, true) . PHP_EOL;
    $this->source = $source;
    if (array_key_exists($source, $this->xml)) {
      $this->sourceType = "xml";
      # echo "SET sourceType xml" . PHP_EOL;
    } else {
      $this->sourceType = "db";
    }
  }

  public function getSQLStmt() {
    // For debugging purposes only.
    return $this->fullSQL;
  }

  private function setFieldName($field, $alias) {
    if ($alias == "") {
      return "`$field`";
    } else {
      return "$alias.`$field`";
    }
  }

  public function addConstraint($field, $value, $isText = TRUE, $operator = "=", $alias = "") {
    $field = $this->setFieldName($field, $alias);
    if ($isText) {
      array_push($this->constraints, " AND $field $operator '$value'");
    } else {
      array_push($this->constraints, " AND $field $operator $value");
    }
  }

  public function addParams($field, $operator = "=", $alias = "") {
    $field = $this->setFieldName($field, $alias);
    array_push($this->params, " AND $field $operator ?");
  }

  public function addSort($field, $alias = "") {
    $field = $this->setFieldName($field, $alias);
    array_push($this->sortFields, $field);
  }

  public function setLimit($limit) {
    if (is_int($limit) and ($limit > 0)) {
      $this->rowLimit = " LIMIT $limit";
    }
  }

  public function getData($params) {
    $results = array();

    if ($this->sourceType == "xml") {
      # echo "Calling getDataByXml";
      $results = $this->getDataByXml($params);
    } else if ($this->sourceType == "db") {
      $results = $this->getDataBySql($params);
    }
    return $results;
  }

  private function getDataByXml($params) {
    $resultSet = null;
    try {
      /*
      $simple = file_get_contents($this->xml[$this->source]);
      $p = xml_parser_create();
      xml_parse_into_struct($p, $simple, $vals, $index);
      xml_parser_free($p);
      echo "Index array\n";
      print_r($index);
      echo "\nVals array\n";
      print_r($vals);
      */
      /*
      $xmlDoc = new DOMDocument();
      $xmlDoc->load($this->xml[$this->source]);

      $rootEl = $xmlDoc->documentElement;
      foreach ($rootEl->childNodes As $item) {
        echo "Key: " . $item->nodeName . " = " . $item->nodeValue . PHP_EOL;
      }
      */
      # echo var_export($xmlHandle, true);
    } catch (Exception $ex) {
      echo "Couldn't open file.";
    }
    return $resultSet;
  }

  private function getSQL() {
    $sqlStmt = $this->db[$this->source];
    $sqlStmt .= " WHERE 1 = 1 ";
    if (array_key_exists($this->source, $this->linkedConstraints)) {
      $sqlStmt .= $this->linkedConstraints[$this->source];
    }
    foreach ($this->constraints as $whereClause) {
      $sqlStmt .= $whereClause;
    }
    foreach ($this->params as $whereClause) {
      $sqlStmt .= $whereClause;
    }
    if (sizeof($this->sortFields) > 0) {
      $sqlStmt .= " ORDER BY " . join(",", $this->sortFields);
    }
    $sqlStmt .= $this->rowLimit;
    $sqlStmt .= ";";
    # echo var_export($sqlStmt, true) . SEP;
    return $sqlStmt;
  }


  private function getDataBySql($params){
    $resultSet = null;
    $conn = dbconn::getConnectionBuild()->getConnection();
    $sqlStmt = $this->getSQL();
    $this->fullSQL = $sqlStmt;
    $resultSet = $conn->prepare($sqlStmt);
    $resultSet->execute($params);
    $row = $resultSet->fetchAll(PDO::FETCH_ASSOC);
    return $row;
  }
}

?>
