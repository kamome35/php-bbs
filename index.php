<?php

define('TEMPLATE_FILEDIR', dirname(__FILE__).'/template/');
include_once( dirname(__FILE__)."/framework/template.php" );
include_once( dirname(__FILE__)."/framework/db.php" );
include_once( dirname(__FILE__)."/framework/coating.php" );

$instance = new Index();

class Index
{
    function __construct()
    {
        $this->main();
    }
    
    private function main()
    {
        $_MAIN    = parse_ini_file( "./config/main.ini", true );
        $method   = $_MAIN["GET"];
        $dataBase = $_MAIN["DATABASE"];
        $field    = $_MAIN["PARENT_TABLE"];
        $set      = $_MAIN["SET"];
        
        // ページ取得
        if (preg_match('/^[0-9]+$/', $_GET[$method["NUMBER"]])) {
            $page = $_GET[$method["NUMBER"]] - 1;
            if ($page < 0)
                $page = 0;
        } else {
            $page = 0;
        }
        
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
        
        $table_fields = array($field["ID"], $field["TITLE"], $field["NAME"], $field["TEXT"], $field["UPDATE_NAME"], $field["UPDATE_DATE"], $field["RES_AMOUNT"]);
        $query = $connect->templateQuery($dataBase["PARENT_TABLE"], $table_fields, DB_QUERY_SELECT, false, $field["UPDATE_DATE"], true, $set["DISPLAY_NUM"], $set["DISPLAY_NUM"] * $page);
        
        if (($resource = $connect->query($query)) === DB_NG) {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", $connect->isError());
            Template::view("error.tpl");
            exit();
        }
        
        while( ( $result[] = mysql_fetch_assoc( $resource ) ) || array_pop( $result ) );
        
        // データがあれば表示処理へ
        if(count($result) > 0 ) {
            $i = 0;
            foreach( $result as $value ) {
                $view[$i]["new_flag"]    = time() < strtotime($value[$field["UPDATE_DATE"]]) + $set["NEW_DAYS"] * 86400 ? true : false;
                $view[$i]["id"]          = $value[$field["ID"]];
                $view[$i]["title"]       = Coating::form_encode( $value[$field["TITLE"]]);
                $view[$i]["quoted"]      = mb_strimwidth(trim(strip_tags(Coating::replace_tags($value[$field["TEXT"]]))), 0, $set["QUOTED_LENGTH"], "...", "utf8");
                $view[$i]["name"]        = Coating::form_encode( $value[$field["NAME"]]);
                $view[$i]["state"]       = "";//$value[$field["STATE"]];
                $view[$i]["res_num"]     = $value[$field["RES_AMOUNT"]];
                $view[$i]["last_person"] = Coating::form_encode($value[$field["UPDATE_NAME"]]);
                $view[$i]["time"]        = Coating::time_string(strtotime($value[$field["UPDATE_DATE"]]));
                $i++;
            }
            Template::assign("topic", $view);
        } else {
            Template::assign("bbs_title", $set["BBS_TITLE"]);
            Template::assign("message", "トピックデータが見つかりません");
            Template::view("error.tpl");
            exit();
        }
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        
        Template::view("index.tpl");
    }
}
?>
