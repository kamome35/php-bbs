<?php
include_once(dirname(__FILE__).'/db/mysql.php');

class DB
{
    /**
     * サーバへの接続をオープンしてデータベースオブジェクトを作成する
     *
     * @param string $server,
     *        string $username,
     *        string $password
     *
     * @return 成功した場合に MySQLオブジェクト を、失敗した場合に FALSE を返します。
     */
    function &connect($server, $username, $password)
    {
        $obj = new DB_mysql();
        
        return $obj->connect($server, $username, $password) ? $obj : false;
    }
     
    /**
     * SQLインジェクション攻撃を含む可能性のある文字列をエスケープする
     *
     * @param $string
     *
     * @return エスケープ文字列
     */
    function escape_string($string)
    {
        if(get_magic_quotes_gpc()) {
            $string = stripslashes($string);
        }
        return mysql_real_escape_string($string);
    //    $string = mysql_real_escape_string($string);
    //    return str_replace(array('\\', '%', '_'),
    //                       array('\\', '%', '_'),
    //                       $string);
    }
}

?>
