<?php
if(!defined('HVZ')) die(-1);
//database functions for hvz

class Database {
	private $link;
	
	public function __construct($db, $name, $pass, $server = 'localhost') {
		$this->link = mysql_connect($server, $name, $pass);
		if($this->link == false) {
			throw new Exception('DBIC: Invalid database credentials');
		}
		if(!mysql_select_db($db, $this->link)) {
			throw new Exception("DBNF: Database '$db' not found");
		}
	}
	
	public function __destruct() {
		mysql_close($this->link);
	}
	
	/**
	 * select wrapper with error handling
	 * $table - table name (escaped)
	 * $what - array of fields to select or true to select all (fields escaped, default true)
	 * $conds - array of conditions for WHERE (WHERE `key`='value', default array() = no WHERE clause)
	 * $limit - number of rows to return or 0 for no limit (default 0)
	 * returns result
	 */
	public function select($table, $what = true, $conds = array(), $limit = 0) {
		if(is_array($what)) {
			$what = '`' . implode('`,`', $what) . '`';
		} elseif($what === true || $what == '*') {
			$what = '*';
		} else {
			$what = "`$what`";
		}
		$q = "SELECT $what FROM `$table`";
		if($conds != array()) {
			$q .= " WHERE";
			foreach($conds as $k => $v) {
				$v = mysql_real_escape_string($v, $this->link);
				$q .= " AND `$k`='$v'";
			}
			$q = str_replace("WHERE AND", "WHERE", $q);
		}
		if($limit > 0) {
			$q .= " LIMIT $limit";
		}
		$res = $this->query($q);
		if($res === false) {
			throw new Exception("DB" . mysql_errno() . ": " . mysql_error());
		} else {
			return $res;
		}
	}
	
	/**
	 * update wrapper with error handling
	 * $table - table name (escaped)
	 * $what - array of fields to update (field name => value)
	 * $conds - array of conditions for WHERE
	 * $limit - number of rows to update
	 * returns number of rows modified
	 */
	public function update($table, $what, $conds = array(), $limit = 0) {
		$w = '';
		if(is_array($what)) {
			foreach($what as $k => $v) {
				$w .= '`' . $k . '`=\'' . mysql_real_escape_string($v, $this->link) . '\',';
			}
		} else {
			$w = "`$what`";
		}
		$what = rtrim($w, ',');
		$q = "UPDATE `$table` SET $what";
		if($conds != array()) {
			$q .= " WHERE";
			foreach($conds as $k => $v) {
				$v = mysql_real_escape_string($v, $this->link);
				$q .= " AND `$k`='$v'";
			}
			$q = str_replace("WHERE AND", "WHERE", $q);
		}
		if($limit > 0) {
			$q .= " LIMIT $limit";
		}
		$n = $this->query($q);
		if($n === false) {
			throw new Exception("DB" . mysql_errno() . ": " . mysql_error());
		} else {
			return mysql_affected_rows($this->link);
		}
	}
	
	//basic query wrapper, no error handling
	public function query($query) {
		$res = mysql_query($query, $this->link);
		if(!is_resource($res)) {
			return $res;
		} else {
			return new DbResult($res);
		}
	}
}

class DbResult {
	private $res;
	private $freed = false;
	
	public function __construct($res) {
		$this->res = $res;
	}
	
	public function __destruct() {
		$this->freeResult();
	}
	
	public function freeResult() {
		if(!$this->freed) {
			mysql_free_result($this->res);
			$this->freed = true;
		}
	}
	
	public function fetchRow() {
		return mysql_fetch_object($this->res);
	}
	
	public function numRows() {
		return mysql_num_rows($this->res);
	}
	
	public function getRawResult() {
		return $this->res;
	}
}
