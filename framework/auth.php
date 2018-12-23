<?php

/**
 *
 * @version 1.0
 *
 */
class Auth
{
    static function basic( $pass_file )
    {
        $pass = parse_ini_file( $pass_file );
        
        if( empty( $_SERVER['PHP_AUTH_USER'] ) || $_SERVER['PHP_AUTH_USER'] == "" ) {
            header( "WWW-Authenticate: Basic realm=\"auth\"" );
            header( "HTTP/1.0 401 Unauthorized" );
            return false;
        } else {
            if( isset( $pass[$_SERVER['PHP_AUTH_USER']] ) && $pass[$_SERVER['PHP_AUTH_USER']] === $_SERVER['PHP_AUTH_PW']) {
                return true;
            } else {
                header("WWW-Authenticate: Basic realm=\"auth\"");
                header("HTTP/1.0 401 Unauthorized");
                return false;
            }
        }
    }
}

?>
