<?php 
/**************************************
 seesaw associates | http://seesaw.net

 client: 		mysql
 file: 			class.mysql.php
 description: 	handles mysql paging

 Copyright (C) 2008 Matt Kenefick(.com)
**************************************/

class DB{
	var $host;
	var $user_name;
	var $password;
	var $db_name;
	
	var $link_id;
	var $result;
	var $col;
	var $query;
	var $fields;
	var $records;
	var $setting;
    var $res;
	
	var $debug = false;
	var $query_count = 0;
	var $debug_file = "debug.sql";
	
	function settings($key,$value){
		$this->setting[$key] = $value;
	}

	function init($_host, $_user, $_password, $_db_name, $_charset){
		$this->host = $_host;
		$this->user_name = $_user;
		$this->password = $_password;
		$this->db_name = $_db_name;
		$this->fields = array();
        
        $this->link_id = new PDO('mysql:host='.$_host.';dbname='.$_db_name.';charset='.$_charset, $_user, $_password) or die("Your website is not properly installed.");
        $this->link_id->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		/*
		$this->link_id = @mysql_connect($_host, $_user, $_password) or die("Your website is not properly installed.");
		@mysql_select_db($_db_name, $this->link_id);
        mysql_query("SET NAMES $_charset", $this->link_id);
        */
	}
	
	function assign($field, $value){
        $this->fields[':'.$field] = $value;
	}
	function prepare($sql){
        if($this->debug) {
            list($usec, $sec) = explode(" ", microtime());
            $time_start = ((float)$usec + (float)$sec);
        }
		$this->result = $this->link_id->prepare($sql);
        $this->result->execute($this->fields);
        if($this->debug){
            list($usec, $sec) = explode(" ",microtime());
            $time_end  =  ((float)$usec + (float)$sec);
            $time = $time_end - $time_start;
            $this->saveErrors($time, $_query);
        }
	}
	
	function reset(){
		$this->fields = array();
	}
	function insert($table){
		$f = $fp = $v = "";
		reset($this->fields);
		foreach($this->fields as $field=>$value){
		    if ($field) {
                $f .= ($f != "" ? ", " : "") . str_replace(':', '', $field);
                $fp .= ($fp != "" ? ", " : "") . $field;
            }
			//$v.= ($v!=""?", ":"").$value;
		}
		$sql = "INSERT INTO ".$table." (".$f.") VALUES (".$fp.")";
        $this->prepare($sql);
 		return $this->result;
	}
	
	function update($table, $where){
        $f = $fp = $v = "";
		reset($this->fields);
		foreach($this->fields as $field=>$value){
            if ($field) {
                $f .= ($f != "" ? ", " : "") . str_replace(':', '', $field) . " = '" . $value . "'";
            }
		}
		$sql = "UPDATE ".$table." SET ".$f." ".$where;
        $this->prepare($sql);
        return $this->result;
	}
	
	function timestampFormat($unixNumber){
		return date('Y-m-d H:i:s',$unixNumber);
		///      xxxx-xx-xx xx-xx-xx
	}
	
	function query($_query){
        if($this->debug) {
            list($usec, $sec) = explode(" ", microtime());
            $time_start = ((float)$usec + (float)$sec);
        }
		$this->query = $_query;
        if($this->debug){
		  $this->result = $this->link_id->query($_query) or die( $_query."<p>.print_r($this->link_id->errorInfo()).</p>" );
        } else {
            $this->result = $this->link_id->query($_query, $this->link_id) or die( 'Ошибка в обращении к базе MySQL!' );
        }
        if($this->debug){
            list($usec, $sec) = explode(" ",microtime());
            $time_end  =  ((float)$usec + (float)$sec);
            $time = $time_end - $time_start;
			$this->saveErrors($time, $_query);
		}
		
		return $this->result;
	}

	function saveErrors($time, $_query) {
        $this->query_count ++;
        $f = fopen($this->debug_file, "a");
        $sss = "# ".$this->query_count."\n ".$time." sec \n\n".$_query
            ."\n#-------------------------------------------------------------------------\n\n";
        fputs($f, $sss, strlen($sss));
        fclose($f);
    }
	
	function get_records(){
		$this->records = array();
        $this->records = $this->result->fetchAll(PDO::FETCH_ASSOC);
		return $this->records;
	}
    
    function get_result() {
        $this->res = 0;
        $this->res = $this->result->fetchColumn();
        return $this->res;
    }

	function num_rows(){
		return (int)$this->result->rowCount();
	}

	function movenext(){
		$this->col = $this->result->fetch(PDO::FETCH_ASSOC);
		return $this->col;
	}

	function insert_id(){
		return $this->link_id->lastInsertId();
	}

    function list_tables() {
        $arrtable = array();
        $this->result = $this->link_id->query("show tables");
        while ($row = $this->result->fetch(PDO::FETCH_ASSOC)) {
            $arrtable[] = $row[0];
        }
        return $arrtable;
    }
}
?>