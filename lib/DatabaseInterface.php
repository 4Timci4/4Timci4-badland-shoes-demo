<?php


interface DatabaseInterface
{

    public function select($table, $conditions = [], $columns = '*', $options = []);


    public function insert($table, $data, $options = []);


    public function update($table, $data, $conditions, $options = []);


    public function delete($table, $conditions, $options = []);


    public function executeRawSql($sql, $params = []);


    public function selectWithJoins($table, $joins = [], $conditions = [], $columns = '*', $options = []);


    public function paginate($table, $conditions = [], $page = 1, $limit = 10, $options = []);


    public function count($table, $conditions = []);


    public function clearCache($key = null);


    public function beginTransaction();


    public function commit();


    public function rollback();


    public function isConnected();


    public function getLastError();
}
