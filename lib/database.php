<?php

// データベース操作関連
include_once( dirname(__FILE__)."/../framework/db.php" );
include_once( dirname(__FILE__)."/create.php" );

class Database
{
    /**
     * データベースを初回起動する関数
     * 
     * 先に$main_configのグローバル定義が必要。
     * $main_configはmain.iniファイルの値が読み込まれている
     * 2重呼び出しをしても意味がない、不具合の原因になりえる
     * $call_guardで2重呼び出しを防止している
     * 
     * @param 
     * @return 成功：接続先 失敗：false
     *
     */
    static function initialize()
    {
        global $_MAIN_INI;
        static $call_guard = false;
        
        // 呼び出し済み？  セットされてる？
        if( $call_guard || !isset( $_MAIN_INI ) ) { Create::error( "Error::initialize function." ); }
        
        $database = $_MAIN_INI["DATABASE"];
        
        // データベースに接続
        $connect = Db::connect( $database["SERVER"], $database["USERNAME"], $database["PASSWORD"] ) or Create::error( "Error::The data base cannot be connected." );
        Db::set_charset( 'utf8', $connect ) or Create::error( "Error::Character-code setting failure." );
        
        // データベースが存在するか調べる
        if( Db::database_exists( $connect, $database["DB_NAME"] ) === true ) {
            // データベースを選択する
            Db::select_db( $database["DB_NAME"], $connect ) or Create::error( "Error::The data base cannot be used." );
        } else {
            // データベースを新規作成
            Db::create_db( $connect, $database["DB_NAME"] ) or Create::error( "Error::The data base cannot be created." );
            // データベースを選択する
            Db::select_db( $database["DB_NAME"], $connect ) or Create::error( "Error::The data base cannot be used." );
        }
        
        // 親テーブルが存在するか調べる
        if( Db::table_exists( $connect, $database["PARENT_TABLE"] ) === false ) {
            // 親テーブルの新規作成
            self::create_parent_table( $connect, $database["PARENT_TABLE"] ) or Create::error( "Error::The p_table cannot be created." );
        }
        
        // 子テーブルが存在するか調べる
        if( Db::table_exists( $connect, $database["CHILD_TABLE"] ) === false ) {
            // 子テーブルの新規作成
            self::create_child_table( $connect, $database["CHILD_TABLE"] ) or Create::error( "Error::The c_table cannot be created." );
        }
        
        // 成功フラグ
        $call_guard = true;
        
        return $connect;
    }
    
    private static function create_parent_table( $connect, $tabele_name )
    {
        global $_MAIN_INI;
        $field = $_MAIN_INI["PARENT_TABLE"];
        
        $query = "CREATE TABLE ".$tabele_name." ("
                .$field["ID"].         " BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,"
                .$field["KIND"].       " TINYINT NOT NULL DEFAULT 0,"
                .$field["TITLE"].      " VARCHAR(128) NOT NULL,"
                .$field["NAME"].       " VARCHAR(64) NOT NULL,"
                .$field["MAIL"].       " VARCHAR(128),"
                .$field["URL"].        " VARCHAR(128),"
                .$field["TEXT"].       " TEXT NOT NULL,"
                .$field["ATTACH"].     " VARCHAR(128),"
                .$field["MD5"].        " VARCHAR(32) NOT NULL,"
                .$field["CREATE_DATE"]." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',"
                .$field["STATE"].      " TINYINT NOT NULL DEFAULT 0,"
                .$field["REMOTE_ADDR"]." VARCHAR(32) NOT NULL,"
                .$field["REMOTE_HOST"]." VARCHAR(64) NOT NULL,"
                .$field["USER_AGENT"] ." VARCHAR(200) NOT NULL,"
                .$field["RES_AMOUNT"] ." SMALLINT UNSIGNED DEFAULT 1,"
                .$field["PAGE_VIEW"]  ." MEDIUMINT UNSIGNED DEFAULT 0,"
                .$field["UPDATE_NAME"]." VARCHAR(64) NOT NULL DEFAULT '-',"
                .$field["UPDATE_DATE"]." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',"
                ."PRIMARY KEY (id)"
                .")";
        
        return Db::query( $query, $connect );
        // 呼び出し元でエラーチェックを行うこと
    }
    
    private static function create_child_table( $connect, $tabele_name )
    {
        global $_MAIN_INI;
        $field = $_MAIN_INI["CHILD_TABLE"];
        
        $query = "CREATE TABLE ".$tabele_name." ("
                .$field["ID"].         " BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,"
                .$field["PARENT"].     " BIGINT UNSIGNED NOT NULL,"
                .$field["TITLE"].      " VARCHAR(128) NOT NULL,"
                .$field["NAME"].       " VARCHAR(64) NOT NULL,"
                .$field["MAIL"].       " VARCHAR(128),"
                .$field["URL"].        " VARCHAR(128),"
                .$field["TEXT"].       " TEXT NOT NULL,"
                .$field["ATTACH"].     " VARCHAR(128),"
                .$field["MD5"].        " VARCHAR(32) NOT NULL,"
                .$field["CREATE_DATE"]." DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',"
                .$field["STATE"].      " TINYINT NOT NULL DEFAULT '0',"
                .$field["REMOTE_ADDR"]." VARCHAR(32) NOT NULL,"
                .$field["REMOTE_HOST"]." VARCHAR(64) NOT NULL,"
                .$field["USER_AGENT"] ." VARCHAR(200) NOT NULL,"
                ."PRIMARY KEY (id)"
                .")";
        
        return Db::query( $query, $connect );
        // 呼び出し元でエラーチェックを行うこと
    }
}
?>
