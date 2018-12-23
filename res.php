<?php

define('TEMPLATE_FILEDIR', dirname(__FILE__).'/template/');
include_once( dirname(__FILE__)."/../framework/template.php" );
include_once( dirname(__FILE__)."/../framework/db.php" );
include_once( dirname(__FILE__)."/../framework/coating.php" );
include_once( dirname(__FILE__)."/lib/cookie.php" );

$instance = new Index();

class Index
{
    function __construct()
    {
        $_MAIN  = parse_ini_file( "./config/main.ini", true );
        $method = $_MAIN["GET"];
        $mode   = $_MAIN["MODE"];
        $set    = $_MAIN["SET"];
        
        if (!preg_match('/^[0-9]+$/', $_GET[$method["NUMBER"]])) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", "お探しのトピックは見つかりませんでした。");
            Template::view("error.tpl");
        } else if ( $_SERVER["REQUEST_METHOD"] !== "POST" || $_POST[$method["MODE"]] !== $mode["INSERT"] ) {
            $this->main();
        } else {
            $this->insert_log_table();
        }
    }
    
    private function main()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $method   = $_MAIN["GET"];
        $dataBase = $_MAIN["DATABASE"];
        $Pfield   = $_MAIN["PARENT_TABLE"];
        $Cfield   = $_MAIN["CHILD_TABLE"];
        $post     = $_MAIN["POST"];
        $mode     = $_MAIN["MODE"];
        $set      = $_MAIN["SET"];
        
        // データベース初期化
        $connect = DB::connect($dataBase["SERVER"], $dataBase["USERNAME"], $dataBase["PASSWORD"]) or die("データベース接続失敗");
        $connect->select_db($dataBase["DB_NAME"]);
        $connect->charset("utf8");
        
        if (($errorMessage = $connect->isError()) !== DB_NO_ERROR) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $errorMessage);
            Template::view("error.tpl");
            exit();
        }
        
        
        // 親記事取得
        $table_fields = array($Pfield["ID"], $Pfield["TITLE"], $Pfield["URL"], $Pfield["MAIL"], $Pfield["NAME"], $Pfield["TEXT"], $Pfield["CREATE_DATE"], $Pfield["PAGE_VIEW"]);
        $query = $connect->templateQuery($dataBase["PARENT_TABLE"], $table_fields, DB_QUERY_SELECT, $Pfield["ID"]."=".$_GET[$method["NUMBER"]]);
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        
        $topic = mysql_fetch_assoc( $resource );
        
        $tmp = array();
        
        if ($topic !== FALSE) {
            $tmp["id"]    = $topic[$Pfield["ID"]];
            $tmp["title"] = Coating::form_encode($topic[$Pfield["TITLE"]]);
            $tmp["url"]   = Coating::form_encode($topic[$Pfield["URL"]]);
            $tmp["mail"]  = Coating::form_encode($topic[$Pfield["MAIL"]]);
            $tmp["name"]  = Coating::form_encode($topic[$Pfield["NAME"]]);
            $tmp["text"]  = Coating::program_highlight( Coating::auto_link( Coating::replace_tags( Coating::form_encode($topic[$Pfield["TEXT"]]) ) ) );
            $tmp["time"]  = Coating::time_string(strtotime($topic[$Pfield["CREATE_DATE"]]));
        } else {
            include(dirname(__FILE__)."/../maintenance/404.php");
            exit();
        }
        
        Template::assign("topic", $tmp);
        
        // 参照数を増やす
        $update_data[$Pfield["PAGE_VIEW"]] = $topic[$Pfield["PAGE_VIEW"]] + 1;
        $query = $connect->templateQuery($dataBase["PARENT_TABLE"], $update_data, DB_QUERY_UPDATE, $Pfield["ID"]."=".$topic[$Pfield["ID"]]);
        $connect->query($query);
        
        
        // 子記事取得
        $table_fields = array($Cfield["ID"], $Cfield["TITLE"], $Cfield["URL"], $Cfield["MAIL"], $Cfield["NAME"], $Cfield["TEXT"], $Cfield["CREATE_DATE"]);
        $query = $connect->templateQuery($dataBase["CHILD_TABLE"], $table_fields, DB_QUERY_SELECT, $Cfield["PARENT"]."=".$_GET[$method["NUMBER"]]);
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        
        while ( ( $result[] = mysql_fetch_assoc( $resource ) ) || array_pop( $result ) );
        
        $tmp = array();
        
        if(count($result) > 0 ) {
            $i = 0;
            foreach( $result as $value ) {
                $tmp[$i]["id"]    = $value[$Cfield["ID"]];
                $tmp[$i]["title"] = Coating::form_encode($value[$Cfield["TITLE"]]);
                $tmp[$i]["url"]   = Coating::form_encode($value[$Cfield["URL"]]);
                $tmp[$i]["mail"]  = Coating::form_encode($value[$Cfield["MAIL"]]);
                $tmp[$i]["name"]  = Coating::form_encode($value[$Cfield["NAME"]]);
                $tmp[$i]["text"]  = Coating::program_highlight( Coating::auto_link( Coating::replace_tags( Coating::form_encode($value[$Cfield["TEXT"]]) ) ) );
                $tmp[$i]["time"]  = Coating::time_string(strtotime($value[$Cfield["CREATE_DATE"]]));
                
                $i++;
            }
        }
        
        Template::assign( "res", $tmp );
        
        
        
        $cookieArray = array($post["NAME"], $post["MAIL"], $post["URL"], $post["KEY"], $post["COOKIE"]);
        $cookie = Cookie::read($cookieArray);
        $cookie["url"] = $cookie["url"] ? $cookie["url"] : "http://";
        $user = $cookie;
        
        Template::assign("user", $user);
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        Template::assign("post", $post);
        Template::assign("mode", $mode["INSERT"]);
        
        
        Template::view("res.tpl");
    }
    
    private function insert_log_table()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $Pfield   = $_MAIN["PARENT_TABLE"];
        $Cfield   = $_MAIN["CHILD_TABLE"];
        $method   = $_MAIN["POST"];
        $dataBase = $_MAIN["DATABASE"];
        
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        
        if(($errorMessage = $this->method_check($_MAIN))) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $errorMessage);
            Template::view("error.tpl");
            exit();
        }
        
        if ($_POST[$method["COOKIE"]] === "checked") {
            $cookieArray = array($method["NAME"], $method["MAIL"], $method["URL"], $method["KEY"], $method["COOKIE"]);
            Cookie::set($_POST, $cookieArray);
        }
        
        $connect = DB::connect($dataBase["SERVER"], $dataBase["USERNAME"], $dataBase["PASSWORD"]) or die("データベース接続失敗");
        $connect->select_db($dataBase["DB_NAME"]);
        $connect->charset("utf8");
        
        if (($errorMessage = $connect->isError()) !== DB_NO_ERROR) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $errorMessage);
            Template::view("error.tpl");
            exit();
        }
        
        
        $table_fields = array($Pfield["RES_AMOUNT"]);
        $query = $connect->templateQuery($dataBase["PARENT_TABLE"], $table_fields, DB_QUERY_SELECT, $Pfield["ID"]."=".$_GET[$method["NUMBER"]]);
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        
        $patent = mysql_fetch_assoc( $resource );
        
        
        $insert_data[$Cfield["PARENT"]]      = DB::escape_string($_POST[$method["NUMBER"]]);
        $insert_data[$Cfield["TITLE"]]       = DB::escape_string($_POST[$method["TITLE"]]);
        $insert_data[$Cfield["NAME"]]        = DB::escape_string($_POST[$method["NAME"]]);
        $insert_data[$Cfield["MAIL"]]        = DB::escape_string($_POST[$method["MAIL"]]);
        $insert_data[$Cfield["URL"]]         = DB::escape_string($_POST[$method["URL"]] === "http://" ? "" : $_POST[$method["URL"]]);
        $insert_data[$Cfield["TEXT"]]        = DB::escape_string($_POST[$method["TEXT"]]);
        $insert_data[$Cfield["MD5"]]         = md5( $_POST[$method["KEY"]] );
        $insert_data[$Cfield["CREATE_DATE"]] = date( "Y-m-d H:i:s" );
        $insert_data[$Cfield["REMOTE_ADDR"]] = $_SERVER["REMOTE_ADDR"];
        $insert_data[$Cfield["REMOTE_HOST"]] = gethostbyaddr( $_SERVER["REMOTE_ADDR"] );
        $insert_data[$Cfield["USER_AGENT"]]  = getenv('HTTP_USER_AGENT');
        
        
        $update_data[$Pfield["UPDATE_NAME"]] = $insert_data[$Cfield["NAME"]];
        $update_data[$Pfield["UPDATE_DATE"]] = $insert_data[$Cfield["CREATE_DATE"]];
        $update_data[$Pfield["RES_AMOUNT"]]  = $patent[$Pfield["RES_AMOUNT"]] + 1;
        
        
        
        $insert = $connect->templateQuery($dataBase["CHILD_TABLE"], $insert_data, DB_QUERY_INSERT);
        $update = $connect->templateQuery($dataBase["PARENT_TABLE"], $update_data, DB_QUERY_UPDATE, "id=".DB::escape_string($_POST[$method["NUMBER"]]));
        
        $connect->autoCommit();
        $connect->query($insert);
        $connect->query($update);
        $connect->commit();
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        header( "location: index.php" );
    }
    
    
    private function method_check($_MAIN)
    {
        $method = $_MAIN["POST"];
        $set    = $_MAIN["SET"];
        
        if( !$_POST[$method["TITLE"]] ) {
            return "タイトルが未記入です";
        }
        else if( strlen( $_POST[$method["TITLE"]] ) > 128 ) {
            return "タイトルが最大数を超えています";
        }
        else if( !$_POST[$method["NAME"]] ) {
            return "名前が未記入です";
        }
        else if( strlen( $_POST[$method["NAME"]] ) > 64 ) {
            return "名前が最大数を超えています";
        }
        else if( strlen( $_POST[$method["MAIL"]] ) > 128 ) {
            return "E-Mailが最大数を超えてます";
        }
        else if( !preg_match( "/^([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})$|^$|^$/i", $_POST[$method["MAIL"]] )) {
            return "E-Mailが正しくありません";
        }
        else if( $_POST[$method["NAME"]] == $set["MASTER_NAME"] && $_POST[$method["KEY"]] != $set["MASTER_PASS"] ) {
            return "管理人名は使えません";
        }
        else if( strlen( $_POST[$method["URL"]] ) > 128 ) {
            return "URLが最大数を超えています";
        }
        else if( !preg_match( "/^http:\/\/|^$/i", $_POST[$method["URL"]] ) ) {
            return "そのURLは登録できません";
        }
        else if( !$_POST[$method["TEXT"]] ) {
            return "コメントが未記入です";
        }
        else if( mb_strlen( $_POST[$method["TEXT"]] ) > 1000 ) {
            return "コメントが最大数を超えています";
        }
        else if( !mb_ereg_match( ".*[ぁ-んァ-ン]", $_POST[$method["TEXT"]] ) ) {
            return "コメントにひらがなを含めてください";
        }
        
        return "";
    }
}
?>
