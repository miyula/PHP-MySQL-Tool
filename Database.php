<?php

define('SQL_DEBUG', FALSE); //如果打开，会输出所有执行过的sql
define('SQL_ERROR', FALSE); //如果打开，会输出错误

interface DatabaseInterface{
    public function open($server, $user, $pass, $dbName, $isUTF8);
    public function close();
    public function selectDb($dbName);
    public function query($sql);
    public function freeResult($ret);   // 仅需要在考虑到返回很大的结果集时会占用多少内存时调用。在脚本结束后所有关联的内存都会被自动释放。(w3school)
    public function getLastQuery();
    public function lastErrorNo();
    public function lastError();
    public function tableExists($table);
    
    public function select($table, $var = '*', $conds = '', $options = array());
    public function update($table, $values, $conds = '');
    public function delete($table, $conds);
    public function insert($table, $values);
    
    public function fetchObject($ret);  // 以Ojbect返回结果集
    public function fetchRow($ret);   // 以Array返回结果集
    public function numRows($ret);    // 统计返回结果集条数，如果不需要获取具体内容，建议用selectCount
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
