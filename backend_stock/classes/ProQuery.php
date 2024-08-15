<?php

//require_once(HD . '/classes/proQuery/class-application.php');
//require_once(HD . '/classes/proQuery/setting.php');
//require_once(HD . '/classes/proQuery/class-sql.php');


class Sql
{

  protected static $_dbprefix = null;
  protected static $_db_section = null;
  protected static $_db = null;

  private static function &dbconnect()
  {

    $sdb = &self::$_db;
    if (!(is_null($sdb))) {
      return $sdb;
    }

    // Настройки Базы данных
//    $setting = new Setting();

    if (is_null(self::$_db_section)) {
      echo 'You must set up the DB Section by "setDb(section_name)"';
      exit;
    }

    $db_section = &self::$_db_section;

    self::$_dbprefix = JDotEnv::get('DB_PREFIX', null, $db_section);
    $_host = JDotEnv::get('DB_HOST', null, $db_section);
    $_dbuser = JDotEnv::get('DB_USER', null, $db_section);
    $_dbname = JDotEnv::get('DB_BASE', null, $db_section);
    $_dbpass = JDotEnv::get('DB_PASS', null, $db_section);

    $mysqli = mysqli_connect($_host, $_dbuser, $_dbpass, $_dbname);

    if (mysqli_connect_errno()) {
      echo "Не удалось подключиться к MySQL: " . mysqli_connect_error();
      exit;
    }

    self::$_db = $mysqli;
    return $mysqli;
  }

  /**
   * Set Up DB Section from .env file
   * @param null $db_section_name
   * @return bool
   */
  public static function setDb($db_section_name = null)
  {
    if (is_null($db_section_name)) {
      return false;
    }
    $sdb = &self::$_db;
    if (!(is_null($sdb))) {
      self::$_db = null;
    }
    self::$_db_section = $db_section_name;
    return true;
  }

  /* GET INFO */

  public static function getinfo($q = '', $type = 1, $arr_num = 0, $field_key = 'id')
  {
    $sdb = &self::dbconnect();


    mysqli_query($sdb, 'SET CHARACTER SET utf8');
    mysqli_query($sdb, "SET NAMES utf8");

    $q = preg_replace('/(#__)/u', self::$_dbprefix, $q);
    $result = mysqli_query($sdb, $q);
    /*
     * mysqli_result Object
      (
      [current_field] => 0
      [field_count] => 5
      [lengths] =>
      [num_rows] => 25
      [type] => 0
      )
     * if no result = False
     */

    if (!($result)) {
      return null;
    }

    $cnt = (int)$result->num_rows;

    if (!($cnt)) {
      return null;
    }

    $cnt = ($type == 5) ? 1 : $cnt;

    $array = [];
    for ($i = 0; $i < $cnt; $i++) {
      if ($type == 1) {
        $array[$i] = $result->fetch_object(); // std object all
      } elseif ($type == 2) {
        $array[$i] = $result->fetch_assoc(); // array all
      } elseif ($type == 3) {
        $r = $result->fetch_row(); // array number of array
        $array[$i] = $r[$arr_num];
      } elseif ($type == 4) {
        $r = $result->fetch_object(); // $arr[id] = ...
        $array[$r->$field_key] = $r;
      } elseif ($type == 5) {// Одну запись Result
        $r = $result->fetch_object(); // array all
        $array = $r;
        break;
      }
    }

    $result->free();
    return $array;
  }

  public static function getResult($q = '')
  {
    if (empty($q)) {
      return null;
    }
    $res_arr = self::getinfo($q, 3, 0);
    if ($res_arr == null) {
      return null;
    }
    return $res_arr[0];
  }

  // Первая линия

  public static function getFirst($q = '')
  {
    if (empty($q)) {
      return null;
    }
    $res = self::getinfo($q, 5);
    if ($res == null) {
      return null;
    }
    return $res;
  }

  // Выдать массив одиночных значений

  public static function getArray($q = null, $keynum = 0)
  {
    if (is_null($q)) {
      return NULL;
    }
    $data_arr = self::getinfo($q, 3, $keynum);
    return $data_arr;
  }

  /* редактирование или добавление */

  public static function changeput($q)
  {
    $db = &self::dbconnect();

    mysqli_query($db, 'SET CHARACTER SET utf8');
    mysqli_query($db, "SET NAMES utf8");

    $q = preg_replace('/(#__)/u', self::$_dbprefix, $q);
    if ($result = mysqli_query($db, $q)) {
      $sql_arr = explode(" ", $q);
      $sql_arr = preg_split('/\s/u', $q, -1, 1);

      if (strtoupper($sql_arr[0]) == "INSERT") {
        $res = (int)mysqli_insert_id($db);
//        $result->free();
        return $res;
      } else {
//        $result->free();
        return true;
      }
    } else {
//      $result->free();
      return false;
    }
  }

}


//class ProQuery extends Application
class ProQuery
{

  public static $query_arr = null;
  var $_data = [];

  function __construct()
  {
    $this->_data = $this->init();
  }

// ***** STATIC METHODS ***** //

  /** FIND STATIC *** */
  public static function find()
  {
    $query = &self::$query_arr;
    $query = new ProQuery();
    return $query;
  }

  public function init()
  {
    $init_arr = (object)[
      'select' => [],
      'tablename' => '',
      'where' => [],
      'join' => [],
      'group_by' => null,
      'offset' => null,
      'limit' => null,
      'order_by' => null,
      'query' => null,
      'results' => null,
    ];
    return $init_arr;
  }

  /**
   * Set Up DB Section from .env file
   * @param null $db_section_name
   * @return ProQuery|null
   */
  public function setDb($db_section_name = null)
  {
    if (is_null($db_section_name)) {
      return null;
    }
    $base_name = Sql::setDb($db_section_name);
    return ($base_name) ? $this : null;
  }

  /*     * ** SQL QUERY ****** */

  /**
   * ----- SELECT
   * @param type $select_data
   * @return $this
   */
  public function select($select_data = '*')
  {
    $data = &$this->_data;
    if (is_string($select_data)) {
      $sel_elements = preg_split('/(,\s?)/s', $select_data, -1, 1);
      foreach ($sel_elements as $el) {
        $el = trim($el);
        $data->select[] = $el;
      }
    } elseif (is_array($select_data)) {// if array
      $data->select = array_merge($data->select, $select_data);
    }
    return $this;
  }

  /**
   * --- AND SELECT
   * @param type $select_data
   * @return type
   */
  public function andSelect($select_data = '*')
  {
    $this->select($select_data);
    //    return ($this->select($select_data));
    return $this;
  }

  /**
   * ---- FROM
   * @param type $value
   * @return $this
   */
  public function from($value = '')
  {
    $data = &$this->_data;
    if (is_string($value)) {
      $tablename = preg_replace('/\s+/i', '', $value);
      $data->tablename = preg_replace('/(\{\{|\}\})/i', '`', $tablename);
    } elseif (is_array($value)) {
      $key_arr = array_keys($value);
      $alias = $key_arr[0];
      $tablename = preg_replace('/\s+/i', '', $value[$alias]);
      $tablename = preg_replace('/(\{\{|\}\})/i', '`', $tablename);
      $data->tablename = $tablename . ' AS ' . $alias;
    }
    return $this;
  }

  /**
   * Left Join function
   * @param type $tablename
   * @param type $comparison
   * @return boolean|$this
   */
  public function leftJoin($tablename = '', $comparison = '')
  {
    //        if ((empty($tablename)) || (empty($comparison))) {
    //            return false;
    //        }
    $data = &$this->_data;
    $join_arr = &$data->join;

    if (is_string($tablename)) {

      $tablename = preg_replace('/(\{\{|\}\})/i', '`', $tablename);
    } elseif (is_array($tablename)) {

      $key_arr = array_keys($tablename);
      $alias = $key_arr[0];
      $tablename = preg_replace('/\s+/i', '', $tablename[$alias]);
      $tablename = preg_replace('/(\{\{|\}\})/i', '`', $tablename);
      $tablename = $tablename . ' AS ' . $alias;
    }

    $join_sql = 'LEFT JOIN ' . $tablename . ' ON (' . $comparison . ')';
    $join_arr[] = $join_sql;
    return $this;

  }

  /**
   * andLeftJoin
   * @param type $tablename
   * @param type $comparison
   * @return type
   */
  public function andLeftJoin($tablename = '', $comparison = '')
  {
    return $this->leftJoin($tablename, $comparison);
  }

  /*     * *** where ***** */

  /**
   * ---- WHERE
   * @param type $condition
   * @return $this
   */
  public function where($condition = null)
  {
    $data = &$this->_data;
    //
    // if string
    if (is_string($condition)) {
      $data->where[] = '(' .  (string)trim($condition) . ')';
    //
    // if array
    } elseif (is_array($condition) && (!empty($condition))) {

      foreach ($condition as $key => $value) {
    // value is array
        if (is_array($value)) {
          $val_arr = [];
          foreach ($value as $el) {
            $el = (is_string($el)) ? '"' . $el . '"' : $el;
            $val_arr[] = $el;
          }
          $data->where[] = '(`' . $key . '` IN (' . implode(', ', $val_arr) . '))';
        } elseif (is_string($value) || is_int($value) || is_float($value)) {
          $el = (is_string($value)) ? '"' . $value . '"' : $value;
          $data->where[] = '(`' . $key . '` = ' . $el . ')';
        } elseif (is_object($value)) {
          $data->where[] = '(`' . $key . '` IN (' . $value->buildQuery() . '))';
        } elseif (is_null($value)){
          $data->where[] = '(`' . $key . '` IS NULL)';
        }
      }
    }
    return $this;
  }

  /**
   * Where Like
   * @param type $key // Ключевое поле для сравнения
   * @param type $condition // Массив или строка с чем сравниваем
   * @param type $prefix // по умолчанию пробел перед словом
   * @param string $type // Версия OR или AND
   * @return $this
   */
  public function whereLike($key, $condition, $prefix = ' ', $type = 'OR')
  {
    $data = &$this->_data;
    if (!(is_array($condition))) {
      $data->where[] = '(' . $key . ' LIKE "%' . $prefix . $condition . '%")';
    } else {
      $arr_where = [];
      foreach ($condition as $word) {
        $arr_where[] = '(' . $key . ' LIKE "%' . $prefix . $word . '%")';
      }
      $type = ' ' . strtoupper($type) . ' ';
      $data->where[] = '(' . implode($type, $arr_where) . ')';
    }
    return $this;
  }

// Добавляем Where

  /**
   * AND WHERE
   * @param type $condition
   * @return type
   */
  public function andWhere($condition = null)
  {
    $this->where($condition);
    return $this;
  }

  /**
   * Group By
   * @param type $group_by
   * @return $this
   */
  public function groupBy($group_by = '')
  {
    $data = &$this->_data;
    $data->group_by = $group_by;
    return $this;
  }

  /**
   * ----- OFFSET
   * @param type $value
   * @return $this
   */
  public function offset($value = 0)
  {
    $data = &$this->_data;
    $data->offset = (int)$value;
    return $this;
  }

  /**
   * ---- LIMIT
   * @param type $value
   * @return $this
   */
  public function limit($value = null)
  {
    $data = &$this->_data;
    $data->limit = (int)$value;
    return $this;
  }

  /**
   * ----- ORDER BY
   * @param type $value
   * @return $this
   */
  public function orderBy($value = null)
  {
    $data = &$this->_data;

    $value_arr = preg_split('|[\s]|iu', trim($value), -1, 1);
    $no_quoted = ['ASC', 'DESC', 'asc', 'desc'];
    // quote fields
    foreach ($value_arr as $ind => $v) {
      if (in_array($v, $no_quoted)) {
        continue;
      }

      $v_arr = preg_split('|\.|', $v, -1, 1);
      $v = implode('`.`', $v_arr);


      $value_arr[$ind] = '`' . $v . '`';
    }

    $data->order_by = trim(implode(' ', $value_arr));
    return $this;
  }

  /*     * ************************ */
  /*     * ************************ */
  /*     * ** МЕТОДЫ ИЗВЛЕЧЕНИЯ *** */
  /*     * ************************ */
  /*     * ************************ */

  /*     * ***** ALL ********* */

  /**
   * ----- ALL
   * @param type $group_by
   * @return type
   */
  public function all($group_by = null, $type = 1)
  {
    if (!is_null($group_by)) {
      $this->groupBy($group_by);
    }
    $query = $this->buildQuery();
    $data = &$this->_data;
    if (!is_null($data->group_by)) {
      $data->results = Sql::getinfo($query, $type, null, $group_by);
    } else {
      $data->results = Sql::getinfo($query, $type);
    }
    return $data->results;
  }

  /*     * **** ONE ****** */

  /**
   * ONE
   * @return type
   */
  public function one()
  {
    $query = $this->buildQuery(['limit' => 1]);
    $data = &$this->_data;
    $data->results = Sql::getFirst($query);
    return $data->results;
  }

  public function getResult()
  {
    $query = $this->buildQuery(['limit' => 1]);
    $data = &$this->_data;
    $data->results = Sql::getResult($query);
    return $data->results;
  }

  /**
   * ----- ПОСТРОИТЕЛЬ SQL
   * @param type $params_arr
   * @return type
   */
  function buildQuery($params_arr = [])
  {
    $data = &$this->_data;
    $query = [];
// Select
    $q_arr = [];
    if (!empty($data->select) && (!isset($params_arr['select']))) {
      foreach ($data->select as $el) {
        $el = trim($el);
        $q_arr[] = $el;
      }
      $query[] = implode(" ", ['SELECT', implode(', ', $q_arr)]);
    } elseif (isset($params_arr['select'])) {
      $query[] = implode(" ", ['SELECT', $params_arr['select']]);
    }

// FROM tablename
    if (!empty($data->tablename)) {
      $query[] = implode(" ", ['FROM', $data->tablename]);
    }

// LEFT JOIN
    if (!empty($data->join)) {
      $query[] = implode(" ", $data->join);
    }
// Where

    if (!empty($data->where)) {
      $query[] = 'WHERE(' . implode(' AND ', $data->where) . ')';
    }


    if (!is_null($data->group_by)) {
      $query[] = implode(" ", ['GROUP BY', $data->group_by]);
    }

    if (!is_null($data->order_by)) {
      $query[] = 'ORDER BY ' . $data->order_by;
    }

    if (!is_null($data->limit)) {
      $query[] = (isset($params_arr['limit']) && (((int)$params_arr['limit']) > 0)) ? 'LIMIT ' . (int)$params_arr['limit'] : 'LIMIT ' . (int)$data->limit;
    }

    if (!is_null($data->offset)) {
      $query[] = 'OFFSET ' . (int)$data->offset;
    }

    $data->query = implode(" ", $query);

    return $data->query;
  }

  /* STORE */

  /**
   *
   * @param type $value
   * @return type
   */
  public function table($value = '')
  {
    $this->from($value);
    return $this;
  }

  public function save($method = null)
  {
// Делим на Update и Add
// Если есть Where значит Update
    $data = &$this->_data;

    if (!empty($data->where) || (strtolower($method) == 'update')) {
      return $this->update();
    } else {
      return $this->add();
    }
  }

  public function update()
  {
    $data = &$this->_data;
    $fields = $this->getFields();
    $arr = [];
    $arr[] = 'UPDATE';
    $arr[] = $data->tablename;
    $arr[] = 'SET';
    $val_arr = [];
    foreach ($fields as $key => $value) {
      if (is_null($value) || (mb_strtolower($value, 'UTF-8') == 'null')) {
        $val_arr[] = '`' . $key . '` = NULL';
      } else {
        $val_arr[] = (is_string($value)) ? '`' . $key . '` = ' . '"' . addslashes($value) . '"' : '`' . $key . '` = ' . $value;
      }
    }
    $arr[] = implode(', ', $val_arr);

    $arr[] = 'WHERE (' . implode(' AND ', $data->where) . ')';
    $data->query = implode(" ", $arr);
    return Sql::changeput($data->query);
  }

  public function add()
  {
    $data = &$this->_data;
    $fields = $this->getFields();

    $arr = [];
    $arr[] = 'INSERT INTO';
    $arr[] = $data->tablename;
    $arr[] = '(`' . implode('`,`', array_keys($fields)) . '`)';
    $arr[] = 'VALUES';
    $val_arr = [];
    foreach ($fields as $value) {
      if (is_null($value) || (mb_strtolower($value, 'UTF-8') == 'null')) {
        $val_arr[] = 'NULL';
      } else {
        $val_arr[] = (is_string($value)) ? '"' . addslashes($value) . '"' : $value;
      }
    }
    $arr[] = '(' . implode(',', $val_arr) . ')';
    $data->query = implode(" ", $arr);

    return Sql::changeput($data->query);
  }

  public function getFields()
  {
    $fields = (array)$this;
    unset($fields['_data']);
    return $fields;
  }

  /*     * ***** DELETE ***** */

  public function delete()
  {
    $data = &$this->_data;
    $arr = [];
    $arr[] = 'DELETE FROM';
    $arr[] = $data->tablename;
    $arr[] = 'WHERE (' . implode(' AND ', $data->where) . ')';
    $query = implode(" ", $arr);
    return Sql::changeput($query);
  }

  /* ******** TRANSACTION ******* */

  public function startTransaction()
  {
    return Sql::changeput('START TRANSACTION');
  }

  public function commit()
  {
    return Sql::changeput('COMMIT');
  }

  public function rollback()
  {
    return Sql::changeput('ROLLBACK');
  }
  /* ******** TRANSACTION END ******* */

  /* ******** SERVICE FUNCTIONS ******* */

  public function getData($group_by = null, $type = 1){
    if (!is_null($group_by)) {
      $this->groupBy($group_by);
    }
    $this->buildQuery();
    $data = &$this->_data;
    return $data;
  }

  public function getQuery($group_by = null, $type = 1){
    if (!is_null($group_by)) {
      $this->groupBy($group_by);
    }
    $this->buildQuery();
    $query = &$this->_data->query;
    return $query;

  }



}
