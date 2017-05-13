<?php

define('SQL_DEBUG', FALSE); //如果打开，会输出所有执行过的sql
define('SQL_ERROR', FALSE); //如果打开，会输出错误

interface DatabaseInterface{
    public function open($server, $user, $pass, $dbName, $isUTF8);
    public function close();
    public function selectDb($dbName);
    public function query($sql);
    public function freeResult($ret);
    public function getLastQuery();
    public function lastErrorNo();
    public function lastError();
    public function tableExists($table);
    
    public function select($table, $var = '*', $conds = '', $options = array());
    public function update($table, $values, $conds = '');
    public function delete($table, $conds);
    public function insert($table, $values);
    
    public function fetchObject($ret);
    public function fetchRow($ret);
    public function numRows($ret);
    public function insertId();
    public function affectRows();
    
    public function selectCount($table, $conds = '');
    public function selectRows($table, $var = '*', $conds = '', $options = array());
    public function selectRow($table, $var = '*', $conds = '', $options = array());
}

if(function_exists('mysqli_connect')){
    require_once DOCUMENT.'/includes/DatabaseMysqli.php';
}else{
    require_once DOCUMENT.'/includes/DatabaseMysql.php';
}

/**
 * 返回数据库连接
 * @param type $db
 */
function connectDB($db){
    if($db==null || empty($db['host']) || empty($db['user']) || empty($db['name'])){
        die('No database config');
    }    
    return new Database($db['host'],$db['user'], $db['password'], $db['name']);
}
