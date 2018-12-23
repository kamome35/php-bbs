<?php

define('DB_QUERY_INSERT', 1);
define('DB_QUERY_UPDATE', 2);
define('DB_QUERY_DELETE', 3);
define('DB_QUERY_SELECT', 4);
define('DB_OK', true);
define('DB_NG', false);
define('DB_NO_ERROR', false);

class DB_mysql
{
    /**
     * MySQL リンク ID
     *
     * @var resource
     */
    var $_connection = null;
    
    /**
     * 事前エラーメッセージ
     *
     * @var string
     */
    var $_errorMessage = "";
    
    /**
     * トランザクション回数
     *
     * @var integer
     * @access private
     */
    var $_transactionCount = 0;
    
    /**
     * オートコミット
     *
     * @var integer
     * @access private
     */
    var $_autoCommit = false;
    
    /**
     * サーバへの接続をオープンする
     *
     * @param [ string $server = ini_get("mysql.default_host")  [,
     *          string $username = ini_get("mysql.default_user")  [,
     *          string $password = ini_get("mysql.default_password")  [,
     *          bool $new_link = false  [,
     *          int $client_flags = 0  ]]]]]
     *
     * @return 成功した場合に MySQL リンク ID を、失敗した場合に FALSE を返します。
     */
    function connect()
    {
        $args = func_get_args();
        
        if (!$this->_connection = @call_user_func_array('mysql_connect', $args)) {
            $this->_errorMessage = "サーバ接続オープンに失敗しました。";
            return DB_NG;
        }
        
        return $this->_connection;
    }
    
    /**
     * データベースを選択する
     *
     * @param string $database_name
     *
     * @return 成功した場合に TRUE を、失敗した場合に FALSE を返します。
     */
    function select_db($database_name)
    {
        if (!mysql_select_db($database_name, $this->_connection)) {
            $this->_errorMessage = "データベース選択に失敗しました。";
            return DB_NG;
        }
        
        return DB_OK;
    }
    
    /**
     * クライアントの文字セットを設定する。
     *
     * @param string $charset
     *
     * @return 成功した場合に TRUE を、失敗した場合に FALSE を返します。
     */
    function charset($charset)
    {
        if (!mysql_set_charset($charset, $this->_connection)) {
            $this->_errorMessage = "クライアント文字セットに失敗しました。";
            return DB_NG;
        }
        
        return DB_OK;
    }
    /**
     * テーブルが存在するか調べる。
     *
     * @param $table_name
     *
     * @return bool
     */
    function table_exists($table_name)
    {
        if (mysql_num_rows(mysql_query("SHOW TABLES LIKE '${table_name}'", $this->_connection)) === 1) {
            return DB_OK;
        } else {
            return DB_NG;
        }
    }
    
    /**
     * データベースが存在するか調べる。
     *
     * @param $database_name
     *
     * @return bool
     */
    function database_exists($database_name)
    {
        if (mysql_num_rows(mysql_query("SHOW DATABASES LIKE '${database_name}'", $this->_connection)) === 1) {
            return DB_OK;
        } else {
            return DB_NG;
        }
    }
    
    /**
     * クリエの送信
     *
     * @return 成功した場合に resource を返します。エラー時には FALSE を返します。
     */
    function query($query)
    {
        if (!$this->_autoCommit) {
            if ($this->_transactionCount === 0) {
                $resource = @mysql_query('SET AUTOCOMMIT=0', $this->_connection);
                $resource = @mysql_query('BEGIN', $this->_connection);
                if (!$resource) {
                    $this->_errorMessage = "トランザクションを開始できませんでした。";
                    return DB_NG;
                }
            }
            $this->_transactionCount++;
        }
        if (!$resource = @mysql_query($query, $this->_connection)) {
            $this->_errorMessage = "クリエの送信に失敗しました。";
            return DB_NG;
        }
        
        return $resource;
    }
    
    /**
     * マニピュレータクリエの送信
     *
     * @return 成功した場合に resource を返します。エラー時には FALSE を返します。
     */
    function templateQuery($table, $table_fields, $mode,
                           $where = false, $order = false, $desc = false,
                           $limit = 0, $offset = 0)
    {
        if (count($table_fields) === 0 && $mode !== DB_QUERY_DELETE) {
            $this->_errorMessage = "テーブルフィールド値が正しくセットされていません。";
            return DB_NG;
        }
        $sql = '';
        switch ($mode) {
            case DB_QUERY_INSERT:
                $values = '';
                $names  = '';
                foreach ($table_fields as $key => $value) {
                    $names  .= "$key,";
                    $values .= "'$value',";
                }
                $names  = substr($names, 0, -1);
                $values = substr($values, 0, -1);
                $sql = "INSERT INTO $table ($names) VALUES ($values)";
                break;
            case DB_QUERY_UPDATE:
                $set = '';
                foreach ($table_fields as $key => $value) {
                    $set .= "$key = '$value',";
                }
                $set = substr($set, 0, -1);
                $sql = "UPDATE $table SET $set";
                if ($where) {
                    $sql .= " WHERE $where";
                }
                break;
            case DB_QUERY_DELETE:
                $sql = "DELETE FROM $table";
                if ($where) {
                    $sql .= " WHERE $where";
                } else {
                    $this->_errorMessage = "DELETE文はWHEREを必ず指定してください。";
                    return DB_NG;
                }
                break;
            case DB_QUERY_SELECT:
                $select = '';
                foreach ($table_fields as $value) {
                    $select .= "$value,";
                }
                $select = substr($select, 0, -1);
                $sql = "SELECT $select FROM $table";
                if ($where) {
                    $sql .= " WHERE $where";
                }
                if ($order) {
                    $sql .= " ORDER BY $order";
                }
                if ($desc) {
                    $sql .= " DESC";
                }
                if ($limit > 0) {
                    $sql .= " LIMIT ${offset},${limit}";
                }
                break;
            default:
                $this->_errorMessage = "モードが正しくセットされていません。";
                return false;
        }
        
        return $sql;
    }
    
    /**
     * コミット
     *
     * @return エラーが発生していた場合エラーメッセージを 無い場合は false を返す
     */
    function commit()
    {
        if ($this->_transactionCount > 0) {
            $resource = @mysql_query('COMMIT', $this->_connection);
            $resource = @mysql_query('SET AUTOCOMMIT=1', $this->_connection);
            $this->_transactionCount = 0;
            if (!$resource) {
                $this->_errorMessage = "コミットに失敗しました。";
                return DB_NG;
            }
        }
        return DB_OK;
    }
    
    /**
     * オートコミット
     *
     * @return エラーが発生していた場合エラーメッセージを 無い場合は false を返す
     */
    function autoCommit($onoff = false)
    {
        $this->autoCommit = $onoff ? true : false;
        return DB_OK;
    }
    
    /**
     * エラー確認
     *
     * @return エラーが発生していた場合エラーメッセージを 無い場合は false を返す
     */
    function isError()
    {
        if ($this->_errorMessage !== "") {
            return $this->_errorMessage;
        }
        
        return DB_NO_ERROR;
    }
}
