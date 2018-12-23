<?php

class Cookie
{
    function set($dataArray, $setArray = array(), $expire = 2592000)
    {
        foreach($dataArray as $key => $value) {
            if ($setArray === array() || (in_array($key, $setArray))) {
                setcookie($key, $value, time() + $expire);
            }
        }
    }
    
    function read($keyArray)
    {
        $cookie = array();
        
        foreach($keyArray as $value) {
            if (array_key_exists($value, $_COOKIE)) {
                $cookie[$value] = $_COOKIE[$value];
            } else {
                $cookie[$value] = NULL;
            }
        }
        
        return $cookie;
    }
}
?>
