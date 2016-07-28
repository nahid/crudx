<?php

namespace Nahid\Crudx;

/*
*@Author:		Nahid Bin Azhar
*@Author URL:	http://nahid.co
*/

class Crudx
{
    /*
        Database connection creadentials
    */

    protected $host;
    protected $user;
    protected $password;
    protected $database;
    protected $charset;
    protected $collation;
    protected $tablePrefix;

    //end database connection credentials

    public $sql;

    public $_db;
    protected $_data = array();

    protected $_sql = 'SELECT * FROM ';
    protected $_query = null;
    protected $_table = '';
    protected $_result = null;
    protected $_fields = array();
    protected $_select = '';
    protected $_where = '';
    protected $_sort = '';
    protected $_limit = '';
    protected $_join = '';

    protected $_instance = null;

/*constructor for Crud class
     * $host : Local/Remote server
     * $user : Database username, in local server default(root)
     * $pwd : Password for Database system, in local server password default(null)
     * $db : MySQL Database Name
     *
     * @retuen : database selection
     */
    public function __construct(array $config)
    {
        $this->host         = $config['host'] ?: 'localhost';
        $this->user         = $config['user'] ?: 'root';
        $this->password     = $config['password'] ?: '';
        $this->database     = $config['database'] ?: '';
        $this->charset      = $config['charset'] ?: 'utf8';
        $this->collation    = $config['collation'] ?: 'utf8_unicode_ci';
        $this->tablePrefix  = $config['prefix'] ?: '';
        // connection for database server by using username and password

        $this->_db = new \mysqli($this->host, $this->user, $this->password, $this->database);

        if ($this->_db->connect_errno > 0) {
            die('Unable to connect with Database!');
        }

        $this->_db->query('SET CHARACTER SET '.$this->charset);
        $this->_db->query("SET SESSION collation_connection ='".$this->collation."'");
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
    }

    protected function _apInstance()
    {
        if ($this->_instance === null) {
            $this->_instance = $this;
        }

        return $this->_instance;
    }

    protected function makeType($var)
    {
        $numtype = array('integer', 'double', 'boolean');

        if (isset($var)) {
            $type = gettype($var);
            if (in_array($type, $numtype)) {
                return $this->_db->real_escape_string($var);
            } elseif ($type == 'string') {
                return "'".$this->_db->real_escape_string($var)."'";
            }
        }
    }

    protected function makeSqlString(array $value)
    {
        if (is_array($value)) {
            $vals = '';
            foreach ($value as $key => $val) {
                end($value);
                if ($key === key($value)) {
                    $vals .= $this->makeType($val);
                } else {
                    $vals .= $this->makeType($val).', ';
                }
            }

            return $vals;
        }
    }

    public function table($table = '')
    {
        try {
            if ($table == '' or empty($table)) {
                throw new Exception('Unexpected blank table, please try with table name<br/>', 1);
            }
        } catch (Exception $e) {
            echo $e->getMessage(), "\n";
        }

        $this->_table = $this->tablePrefix.$table;
        $this->_sql .= $this->_table;

        return $this->_apInstance();
    }

    public function save(array $data = null)
    {
        if (is_null($data) && count($this->_data) > 0) {
            $data = $this->_data;
        } elseif (is_null($data) && count($this->_data) < 1) {
            return false;
        }

        if ($this->_where == '') {
            $fields = implode(',', array_keys($data));
            $values = $this->makeSqlString($data);

            $this->_sql = 'INSERT INTO '.$this->_table.'('.$fields.') VALUES('.$values.')';
        } else {
            $sqls = '';
            foreach ($data as $key => $val) {
                end($data);
                if ($key === key($data)) {
                    $sqls .= $key.'='.$this->makeType($val);
                } else {
                    $sqls .= $key.'='.$this->makeType($val).', ';
                }
            }

            $this->_sql = 'UPDATE '.$this->_table.' SET '.$sqls.$this->_where;
        }

        $this->_query = $this->_db->query($this->_sql);

        return $this->_query;
    }

    public function insert(array $data = null)
    {
        return $this->save($data);
    }

    public function insertMany(array $data)
    {
        if (empty($data) or $this->_table == '') {
            return false;
        }

        $this->_sql = '';

        foreach ($data as $key => $val) {
            $fields = implode(',', array_keys($val));
            $values = $this->makeSqlString($val);

            end($data);
            if ($key === key($data)) {
                $this->_sql .= 'INSERT INTO '.$this->_table.'('.$fields.') VALUES('.$values.')';
            } else {
                $this->_sql .= 'INSERT INTO '.$this->_table.'('.$fields.') VALUES('.$values.');';
            }
        }

        $this->_query = $this->_db->multi_query($this->_sql);

        return $this->_query;
    }

    public function delete()
    {
        if ($this->_where == '') {
            return false;
        }

        $this->_sql = 'DELETE FROM '.$this->_table.$this->_where;

        $this->_query = $this->_db->query($this->_sql);

        return $this->_query;
    }

    public function where($fields = '', $condition = '', $value = '')
    {
        if ($fields == '' or $condition == '') {
            return false;
        }

        if (func_num_args() == 2) {
            $value = $condition;
            $condition = '=';
        }

        $this->_where .= $this->_where == '' ? ' WHERE '.$fields.$condition.$this->makeType($value) : ' AND '.$fields.$condition.$this->makeType($value);

        $this->_sql .= $this->_where;

        return $this;
    }

    public function orWhere($fields = '', $condition = '', $value = '')
    {
        if ($fields == '' or $condition == '') {
            return false;
        }

        if (func_num_args() == 2) {
            $value = $condition;
            $condition = '=';
        }

        $this->_where .= $this->_where == '' ? ' WHERE '.$fields.$condition.$this->makeType($value) : ' OR '.$fields.$condition.$this->makeType($value);

        $this->_sql .= $this->_where;

        return $this;
    }

    public function whereBetween($field, array $data)
    {
        if ($field == '' or $data == '') {
            return false;
        }

        $this->_where .= $this->_where == '' ? ' WHERE '.$field.' BETWEEN '.$this->makeType($data[0]).' AND '.$this->makeType($data[1]) : ' AND '.$field.' BETWEEN '.$this->makeType($data[0]).' AND '.$this->makeType($data[1]);
        $this->_sql .= $this->_where;

        return $this;
    }

    public function orBetween($field, array $data)
    {
        if ($field == '' or $data == '') {
            return false;
        }

        $this->_where .= $this->_where == '' ? ' WHERE '.$field.' BETWEEN '.$this->makeType($data[0]).' AND '.$this->makeType($data[1]) : ' OR '.$field.' BETWEEN '.$this->makeType($data[0]).' AND '.$this->makeType($data[1]);
        $this->_sql .= $this->_where;

        return $this;
    }

    public function all()
    {
        $result = array();

        if ($this->_where == '') {
            $this->_sql = 'SELECT * FROM '.$this->_table.$this->_join.$this->_sort.$this->_limit;
        } else {
            $this->_sql = 'SELECT * FROM '.$this->_table.$this->_join.$this->_where.$this->_sort.$this->_limit;
        }

        $this->_query = $this->_db->query($this->_sql);
        $x = array();
        while ($res = $this->_query->fetch_object()) {
            $result[] = $res;
        }
        $this->_result = $result;

        return $this->_apInstance();
    }

    public function get(array $fields)
    {
        $result = array();
        $this->_fields = implode(',', $fields);
        if ($this->_where == '') {
            $this->_sql = 'SELECT '.$this->_fields.' FROM '.$this->_table.$this->_join.$this->_sort;
        } else {
            $this->_sql = 'SELECT '.$this->_fields.' FROM '.$this->_table.$this->_join.$this->_where.$this->_sort;
        }

        $this->_query = $this->_db->query($this->_sql);
        while ($res = $this->_query->fetch_object()) {
            $result[] = $res;
        }

        $this->_result = $result;

        return $this->_apInstance();
    }

    public function first(array $fields)
    {
        $result = array();
        $this->_fields = implode(',', $fields);
        if ($this->_where == '') {
            $this->_sql = 'SELECT '.$this->_fields.' FROM '.$this->_table.$this->_join.$this->_sort;
        } else {
            $this->_sql = 'SELECT '.$this->_fields.' FROM '.$this->_table.$this->_join.$this->_where.$this->_sort;
        }

        $this->_query = $this->_db->query($this->_sql);
        while ($res = $this->_query->fetch_object()) {
            $result[] = $res;
        }

        $this->_result = reset($result);

        return $this->_apInstance();
    }

    public function sortAs($field = '', $order = 'ASC')
    {
        if ($field == '') {
            return false;
        }

        $this->_sort = ' ORDER BY '.$field;
        $this->_sort .= ' '.strtoupper($order);

        return $this->_apInstance();
    }

    public function limit($range = null, $offset = 0)
    {
        if (is_null($range)) {
            return false;
        }

        $this->_limit .= ' LIMIT '.$range.' OFFSET '.$offset;

        return $this->_apInstance();
    }

    public function count($result = false)
    {
        if ($result == true) {
            return count($this->_result);
        }

        if ($this->_query === null) {
            $this->_query = $this->_db->query($this->_sql);
        }

        return $this->_query->num_rows;
    }

    public function result()
    {
        if (is_null($this->_result)) {
            return false;
        }

        return $this->_result;
    }

    public function getId()
    {
        if (!$this->_query) {
            return false;
        }

        return $this->_query->insert_id;
    }

    public function getTables()
    {
        $this->_sql = 'SHOW TABLES';

        $this->_query = $this->_db->query($this->_sql);
        while ($res = $this->_query->fetch_row()) {
            $this->_result[] = $res[0];
        }

        return $this->_apInstance();
    }

    public function getFields()
    {
        $result = array();

        if ($this->_table == '') {
            return false;
        }

        $this->_sql = 'SHOW COLUMNS IN '.$this->_table;
        $this->_query = $this->_db->query($this->_sql);
        while ($res = $this->_query->fetch_object()) {
            $result[] = $res;
        }

        $this->_result = $result;

        return $this->_apInstance();
    }

    public function getQueryString()
    {
        if ($this->_sql == '') {
            return false;
        }

        return $this->_sql;
    }

    protected function destroyMemory()
    {
        foreach ($this->_apInstance() as $property => $value) {
            unset($this->$property);
        }
    }

    public function join($table, $firstTableColumn, $condition, $secondTableColumn = null)
    {
        if (is_null($secondTableColumn)) {
            $secondTableColumn = $condition;
            $condition = '=';
        }

        $tableFirst = $this->tablePrefix.$firstTableColumn;
        $tableSecond = $this->tablePrefix.$secondTableColumn;

        $this->_join .= ' INNER JOIN '.$this->tablePrefix.$table.' ON '.$tableFirst.$condition.$tableSecond.' ';

        return $this->_apInstance();
    }

    public function __destruct()
    {
        $this->destroyMemory();
    }
}
