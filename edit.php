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
        $mode   = $_MAIN["MODE"];
        $post   = $_MAIN["POST"];
        $get    = $_MAIN["GET"];
        $set    = $_MAIN["SET"];
        
        if ($_GET[$get["TYPE"]] === "login") {
            $this->login();
        } else if ( $_SERVER["REQUEST_METHOD"] === "POST" && $_POST[$post["MODE"]] !== $mode["INSERT"] ) {
            $this->edit();
        } else if ( $_SERVER["REQUEST_METHOD"] === "POST" && $_POST[$post["MODE"]] === $mode["INSERT"] ) {
            $this->update();
        }
    }
    
    private function login()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $method   = $_MAIN["GET"];
        $post     = $_MAIN["POST"];
        $set      = $_MAIN["SET"];
        
        
        Template::assign("get", $_GET);
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        Template::assign("post", $post);
        
        Template::view("login.tpl");
    }
    
    private function edit()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $method   = $_MAIN["GET"];
        $dataBase = $_MAIN["DATABASE"];
        $Pfield   = $_MAIN["PARENT_TABLE"];
        $Cfield   = $_MAIN["CHILD_TABLE"];
        $post     = $_MAIN["POST"];
        $mode     = $_MAIN["MODE"];
        $set      = $_MAIN["SET"];
        $post_data = $_POST;
        
        
        $connect = DB::connect($dataBase["SERVER"], $dataBase["USERNAME"], $dataBase["PASSWORD"]) or die("データベース接続失敗");
        $connect->select_db($dataBase["DB_NAME"]);
        $connect->charset("utf8");
        
        if (($errorMessage = $connect->isError()) !== DB_NO_ERROR) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $errorMessage);
            Template::view("error.tpl");
            exit();
        }
        
        if ($post_data[$post["KIND"]] === "topic") {
            $table = $dataBase["PARENT_TABLE"];
        } else {
            $table = $dataBase["CHILD_TABLE"];
        }
        
        $table_fields = array($Pfield["ID"], $Pfield["TITLE"], $Pfield["URL"], $Pfield["MAIL"], $Pfield["NAME"], $Pfield["TEXT"], $Pfield["MD5"]);
        $query = $connect->templateQuery($table, $table_fields, DB_QUERY_SELECT, $Pfield["ID"]."=".DB::escape_string($post_data[$method["NUMBER"]]));
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        
        $user = mysql_fetch_assoc( $resource );
        
        if ($post_data[$post["KEY"]] === "" || md5($post_data[$post["KEY"]]) !== $user[$Pfield["MD5"]]) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", "パスワードが一致しません");
            Template::view("error.tpl");
            exit();
        }
        
        
        Template::assign("user", $user);
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        Template::assign("post", $post);
        Template::assign("post_data", $_POST);
        Template::assign("mode", $mode["INSERT"]);
        
        
        Template::view("edit.tpl");
    }
    
    private function update()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $field    = $_MAIN["PARENT_TABLE"];
        $method   = $_MAIN["POST"];
        $dataBase = $_MAIN["DATABASE"];
        $post_data = $_POST;
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        
        // データチェック
        if(($errorMessage = $this->method_check($_MAIN))) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $errorMessage);
            Template::view("error.tpl");
            exit();
        }
        
        // Cookieセット
        if ($_POST[$method["COOKIE"]] === "checked") {
            $cookieArray = array($method["NAME"], $method["MAIL"], $method["URL"], $method["KEY"], $method["COOKIE"]);
            Cookie::set($_POST, $cookieArray);
        }
        
        // データベース接続
        $connect = DB::connect($dataBase["SERVER"], $dataBase["USERNAME"], $dataBase["PASSWORD"]) or die("データベース接続失敗");
        $connect->select_db($dataBase["DB_NAME"]);
        $connect->charset("utf8");
        
        if (($errorMessage = $connect->isError()) !== DB_NO_ERROR) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $errorMessage);
            Template::view("error.tpl");
            exit();
        }
        
        // tableの選択
        if ($post_data[$method["KIND"]] === "topic") {
            $table = $dataBase["PARENT_TABLE"];
        } else {
            $table = $dataBase["CHILD_TABLE"];
        }
        
        // パスワードのチェック
        $table_fields = array($field["MD5"]);
        $query = $connect->templateQuery($table, $table_fields, DB_QUERY_SELECT, $field["ID"]."=".DB::escape_string($post_data[$method["NUMBER"]]));
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        
        $user = mysql_fetch_assoc( $resource );
        
        if ($post_data[$method["KEY"]] === "" || md5($post_data[$method["KEY"]]) !== $user[$field["MD5"]]) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", "パスワードが一致しません");
            Template::view("error.tpl");
            exit();
        }
        
        
        $update_data[$field["TITLE"]] = DB::escape_string($post_data[$method["TITLE"]]);
        $update_data[$field["NAME"]]  = DB::escape_string($post_data[$method["NAME"]]);
        $update_data[$field["MAIL"]]  = DB::escape_string($post_data[$method["MAIL"]]);
        $update_data[$field["URL"]]   = DB::escape_string($post_data[$method["URL"]] === "http://" ? "" : $post_data[$method["URL"]]);
        $update_data[$field["TEXT"]]  = DB::escape_string($post_data[$method["TEXT"]]);
        
        
        
        
        $update = $connect->templateQuery($table, $update_data, DB_QUERY_UPDATE, "id=".DB::escape_string($post_data[$method["NUMBER"]]));
        
        if (($resource = $connect->query($update)) === DB_NG) {
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
