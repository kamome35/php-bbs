<?php
include_once( dirname(__FILE__)."/../../../framework/coating.php" );

class Create
{
    static function encode( $string, $to_encode = 'UTF-8' )
    {
        if( mb_detect_encoding( $string, $to_encode ) != $to_encode ) {
            return mb_convert_encoding( $string, $to_encode );
        }
        return $string;
    }
    
    static function error( $message )
    {
        $_MAIN    = parse_ini_file("../config/main.ini", true);
        $set      = $_MAIN["SET"];
        
        Template::assign("bbs_title", $set["BBS_TITLE"]);
        Template::assign("message", $message);
        
        Template::view("error.tpl");
        
        exit();
    }
}

?>
