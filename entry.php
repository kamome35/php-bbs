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
        
        if( $_SERVER["REQUEST_METHOD"] !== "POST" || $_POST[$method["MODE"]] !== $mode["INSERT"] ) {
            $this->main();
        } else {
            $this->insert_log_table();
        }
    }
    
    private function main()
    {
        $_MAIN  = parse_ini_file( "./config/main.ini", true );
        $method = $_MAIN["POST"];
        $mode   = $_MAIN["MODE"];
        $set    = $_MAIN["SET"];
        
        $cookieArray = array($method["NAME"], $method["MAIL"], $method["URL"], $method["KEY"], $method["COOKIE"]);
        $cookie = Cookie::read($cookieArray);
        $cookie["url"] = $cookie["url"] ? $cookie["url"] : "http://";
        $user = $cookie;
        
        Template::assign("user", $user);
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        Template::assign("post", $method);
        Template::assign("mode", $mode["INSERT"]);

        Template::view("entry.tpl");
    }
    
    private function insert_log_table()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $field    = $_MAIN["PARENT_TABLE"];
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
            $cookie = array($method["NAME"], $method["MAIL"], $method["URL"], $method["KEY"], $method["COOKIE"]);
            Cookie::set($_POST, $cookie);
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
        
        
        
        $insert_data[$field["TITLE"]]       = DB::escape_string($_POST[$method["TITLE"]]);
        $insert_data[$field["NAME"]]        = DB::escape_string($_POST[$method["NAME"]]);
        $insert_data[$field["MAIL"]]        = DB::escape_string($_POST[$method["MAIL"]]);
        $insert_data[$field["URL"]]         = DB::escape_string($_POST[$method["URL"]] === "http://" ? "" : $_POST[$method["URL"]]);
        $insert_data[$field["TEXT"]]        = DB::escape_string($_POST[$method["TEXT"]]);
        $insert_data[$field["MD5"]]         = md5($_POST[$method["KEY"]]);
        $insert_data[$field["CREATE_DATE"]] = date("Y-m-d H:i:s");
        $insert_data[$field["REMOTE_ADDR"]] = $_SERVER["REMOTE_ADDR"];
        $insert_data[$field["REMOTE_HOST"]] = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
        $insert_data[$field["USER_AGENT"]]  = getenv('HTTP_USER_AGENT');
        $insert_data[$field["UPDATE_DATE"]] = $insert_data[$field["CREATE_DATE"]];
        
        
        
        $query = $connect->templateQuery($dataBase["PARENT_TABLE"], $insert_data, DB_QUERY_INSERT);
        
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
