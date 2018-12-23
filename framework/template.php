<?php
include_once( dirname(__FILE__)."/Lite.php" );

define('CACHE_CACHEDIR', dirname(__FILE__).'/../cache/');
define('CACHE_LIFETIME', 1);
define('CACHE_GROUP', "default");
define('TEMPLATE_CACHEDIR', dirname(__FILE__).'/../cache/');
define('TEMPLATE_FILEDIR', dirname(__FILE__).'/../template/');

class Template
{
    static $_loopCounter = 0;
    
    static $_assignArray = array();
    
    static $_templateFileName  = NULL;
    static $_templateCacheName = NULL;
    
    static $_cache = NULL;
    
    function assign($key, $value = NULL)
    {
        if(is_array($key)) {
            Template::$_assignArray += $key;
        } else {
            Template::$_assignArray[$key] = $value;
        }
    }
    
    function checkCache()
    {
        
        $obj =& new Cache_Lite(array(
            'cacheDir' => CACHE_CACHEDIR,
            'lifeTime' => CACHE_LIFETIME
        ));
        
        if ($data = $obj->get($_SEVAER['SCRIPT_NAME'], CACHE_GROUP)) {
            echo $data;
            exit;
        }
        
        Template::$_cache =& $obj;
    }
    
    function view($fileName)
    {
        Template::_setFileName($fileName, $cacheDir);
        
        if (filemtime(Template::$_templateFileName) > @filemtime(Template::$_templateCacheName)) {
            $string = file_get_contents(Template::$_templateFileName);
            $string = Template::_convert( $string );
            $fp = fopen(Template::$_templateCacheName, "w");
            fwrite($fp, $string);
            fclose($fp);
        } else {
            $string = file_get_contents(Template::$_templateCacheName);
        }
        
        extract(Template::$_assignArray);
        ob_start();
        ob_implicit_flush(false);
        eval('?>'.$string.'<?php;');
        $data = ob_get_contents();
        ob_end_clean();
        
        if (Template::$_cache) {
            Template::$_cache->save($data, $_SEVAER['SCRIPT_NAME'], CACHE_GROUP);
        }
        
        echo $data;
        
    }
    
    function _moduleInclude($fileName)
    {
        if ((Template::$_loopCounter += 1) > 100) {
            exit('モジュールは100個以上読み込めません');
        }
        
        $string = file_get_contents(TEMPLATE_FILEDIR.$fileName);
        
        $string = stripslashes($string);
        $string = preg_replace('/\'/', '\\\'', $string );
        $string = preg_replace('/\{include file=(.*?)\}/', '\'.Template::_moduleInclude($1).\'', $string );
        
        eval("\$string = '".$string."';");
        
        return $string;
    }
    
    function _convert($string)
    {
        $string = stripslashes($string);
        $string = preg_replace('/^<\?xml/', '<<?php ?>?xml', $string);
        $string = preg_replace('/\'/', '\\\'', $string);
        $string = preg_replace('/\{include file=(.*?)\}/', '\'.Template::_moduleInclude($1).\'', $string );
        
        eval("\$string = '".$string."';");
        
        $pattern = array(
            '/\$\{(.+?)\}/'   => '<?php echo \$$1; ?>',
            '/%\{(.+?)\}/'    => '<?php $1 ?>'
        );
        
        $string = preg_replace(array_keys($pattern), array_values($pattern), $string );
        
        return $string;
    }
    
    function _setFileName($fileName)
    {
        Template::$_templateFileName  = TEMPLATE_FILEDIR.$fileName;
        Template::$_templateCacheName = TEMPLATE_CACHEDIR.'template_'.md5(TEMPLATE_FILEDIR).'_'.md5($fileName);
    }
}


?>
