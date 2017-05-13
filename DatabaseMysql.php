<?php
/**
 * 数据库操作同一类 (MySQL)
 *
 * @author zengshuyan
 */

class Database implements DatabaseInterface {

    private $_mLastQuery = '';
    private $_server = '';
    private $_user = '';
    private $_pass = '';
    private $_tablePrefix = '';
    protected $dbName = '';
    protected $conn = null;

    function __construct($server, $user, $pass, $dbName, $isUTF8 = true, $tablePrefix = '') {

        $this->_tablePrefix = $tablePrefix;
        if ($server) {
            $this->open($server, $user, $pass, $dbName, $isUTF8);
        }
    }

    function open($server, $user, $pass, $dbName, $isUTF8) {

        if (!function_exists('mysql_connect')) {
            die('MySQL functions missing, have you compiled PHP with the --with-mysql opotion?');
        }

        $this->close();
        $this->_server = $server;
        $this->_user = $user;
        $this->_pass = $pass;
        $this->dbName = $dbName;
        $this->conn = mysql_connect($server, $user, $pass);

        if ($this->conn !== false) {
            mysql_select_db($dbName, $this->conn);
        } else {
            die('DB connect error:'.$this->lastError());
        }
        
        if($isUTF8){
            $this->query ('SET NAMES utf8');
        }
    }

    function close() {

        if ($this->conn) {
            mysql_close($this->conn);
            $this->conn = null;
        }
    }

    function query($sql) {
        $this->_mLastQuery = $sql;
        if(SQL_DEBUG) echo $this->getLastQuery ().'<br/>';
        return mysql_query($sql, $this->conn);
        
    }
    
    function getLastQuery(){
        return $this->_mLastQuery;
    }
    
    function freeResult($ret){
        @mysql_free_result($ret);
    }
    
    function fetchObject($ret){
        
        return $ret ?  mysql_fetch_object($ret) : false;
        
    }
    
    function fetchRow($ret, $type = MYSQL_ASSOC){
        
        return $ret ? mysql_fetch_array($ret, $type) : false;

    }
    
    function numRows($ret){
        
        return $ret ? mysql_num_rows($ret) : 0;
        
    }
    
    function insertId(){
        return mysql_insert_id ( $this->conn );
    }
    
    function affectRows(){
        return mysql_affected_rows($this->conn);
    }
    
    function selectDb($dbName){
        $this->dbName = $dbName;
        return mysql_select_db($this->dbName, $this->conn);
    }
    
    function lastErrorNo(){
        return $this->conn ? mysql_errno($this->conn) : mysql_errno();
    }

    function lastError() {
        if ($this->conn) {
            $error = mysql_error($this->conn);
        }
        if (!$error) {
            $error = mysql_error();
        }
        if($error){
            $error.="({$this->_server})";
        }
        return $error;
    }
    
    function makeConds($conds){
        
        if(is_array($conds)&&!empty($conds)){
            $condStr = ' 1';
            foreach($conds as $key => $cond){
                
                if(is_numeric($key)){
                    $condStr.= ' AND '.$cond;
                }else{
                    $condStr.= ' AND '.$this->makeParamt($key, $cond);
                }
                
            }
            return $condStr;
        }else{
            return $conds;
        }
        
    }
    
    function makeParamt($key, $value){
        $value = mysql_real_escape_string($value);
        return "`{$key}`='{$value}'";
    }
    
    function makeOptions($options){
        
        $optStr = '';
        
        if(isset($options ['GROUP BY'])){
            $optStr.=' GROUP BY '.$options ['GROUP BY'];
        }
        
        if (isset ( $options ['ORDER BY'] )) {
            $optStr.=' ORDER BY '.$options ['ORDER BY'];
        }
        
        if (isset ( $options ['LIMIT'] )) {
            $optStr.=' LIMIT '.$options ['LIMIT'];
        }
        
        return $optStr;
        
    }
    
    function makeVars($var){
        if(is_array($var)){
            return implode ( ',', $vars );
        }
        return $var;
    }
    
    function select($table, $var = '*', $conds = '', $options = array()){
        
        $varStr = $this->makeVars($var);
        
        $sql = "SELECT {$varStr} FROM `{$table}`";
        
        $condsStr = $this->makeConds($conds);
        if(!empty($condsStr)){
            $sql.=' WHERE '.$condsStr;
        }
        
        $sql.= $this->makeOptions($options);
        
        return $this->query($sql);
        
    }
    
    function update($table, $values, $conds = ''){
        
        $updateStr = '';
        foreach($values as $key => $v){
            if(!empty($updateStr)){
                $updateStr.=', ';
            }
            $updateStr.=$this->makeParamt($key, $v);
        }
        
        $condsStr = $this->makeConds($conds);
        
        $sql = "UPDATE `{$table}` SET {$updateStr} WHERE {$condsStr}";

        return $this->query($sql);
        
    }
    
    function delete($table, $conds){
        
        if(empty($conds)){
            return false;
        }
        
        $condsStr = $this->makeConds($conds);
        
        $sql = "DELETE FROM `{$table}` WHERE {$condsStr}";
        
        return $this->query($sql);
        
    }
    
    function insert($table, $values){
        
        $sql = "INSERT INTO `{$table}` (%s) VALUES %s";
        
        $columns = '';
        $valuesStr = '';
        
        if(isset($values[0]) && is_array($values[0])){
            $columns = $this->getInsertColumns($values[0]);
            
            foreach($values as $val){
                if(!empty($valuesStr)){
                    $valuesStr.=',';
                }
                $valuesStr.= $this->getInsertValue($val);
            }
            
        }else{
            $columns = $this->getInsertColumns($values);
            $valuesStr = $this->getInsertValue($values);
        }
        return $this->query(sprintf($sql, $columns, $valuesStr)); 
        
    }
    
    function getInsertColumns($value){
        
        $columns = '';
        foreach($value as $key => $v){
            if(!empty($columns)){
                $columns.=',';
            }
            $columns.= "`{$key}`";
        }
        return $columns;
    }
    
    function getInsertValue($value){
        
        $values = '';
        foreach($value as $key => $v){
            if(!empty($values)){
                $values.=',';
            }
            $v = mysql_real_escape_string($v);
            $values.= "'{$v}'";
        }
        
        return "({$values})";
    }
    
    function tableExists($table){
        $sql = sprintf('SHOW TABLES LIKE \'%s\'', $table);
        $ret = $this->query($sql);
        $rowNums = $this->numRows($ret);
        return $rowNums > 0;
    }
    
    function selectCount($table, $conds = ''){
        $var = 'COUNT(1) AS num';
        $ret = $this->select($table, $var, $conds);
        if($ret && $row = $this->fetchRow($ret)){
            return $row['num'];
        }
        return 0;
    }
    
    function selectRows($table, $var = '*', $conds = '', $options = array()){
        $rows = array();
        $ret = $this->select($table, $var, $conds, $options);
        while($row = $this->fetchRow($ret)){
            $rows[] = $row;
        }
        return $rows;
    }
    
    function selectRow($table, $var = '*', $conds = '', $options = array()){
        $options[] = 'LIMIT 1';
        $ret = $this->select($table, $var, $conds, $options);
        
        return $ret ? $this->fetchRow($ret) : false;
    }

}
