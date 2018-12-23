<?php

/*
 * タイトル：コーティングクラス
 *     概要：文字列のコーティングを行う
 *
 *  version
 *      1.0：H21.12.01 新規作成
 *
 */

/**
 *
 * @version 1.0
 *
 */
class Coating
{
    /**
     *
     * @param 変換前 $string(string) 文字コード $encode
     * @return 変換後
     *
     */
    static function form_encode( $string, $encode = "UTF-8" )
    {
        // 文字コードが違う時
        if( mb_detect_encoding( $string ) != $encode ) {
            // 文字コードを変換
            $value = mb_convert_encoding( $string, $encode, "ASCII,JIS,UTF-8,EUC-JP,SJIS" );
        }
        // 変換文字対象テーブルの作成
        $translation = get_html_translation_table( HTML_SPECIALCHARS, ENT_QUOTES );
        $translation[',']  = '&#44;';
        $translation[' ']  = '&nbsp;';
        $translation['　'] = '&nbsp;&nbsp;';
        $translation["\t"] = '&nbsp;&nbsp;&nbsp;&nbsp;';
        $translation["\r"] = '';
        $translation["\n"] = '<br />';
        
        // HTML表示形式に変換
        return strtr( $string, $translation );
    }
    
    /**
     *
     * @param 変換前 $string(string)
     * @return 変換後
     *
     */
    static function form_decode( $string )
    {
        // 変換文字対象テーブルの作成
        $translation = array_flip( get_html_translation_table( HTML_SPECIALCHARS, ENT_QUOTES ) );
        $translation['&#44;']  = ',';
        $translation['&nbsp;'] = ' ';
        $translation['<br />'] = "\n";
        
        // HTML表示形式をデコード
        return strtr( $string, $translation );
    }
    
    /**
     *
     * @param 変換前 $string(string) 囲み文字 $delimiter(string)
     * @return 変換後
     *
     */
    static function program_highlight( $string, $delimiter = 'code' )
    {
        $keyword = array( "int","double","float","fool","long","const","char","void","return","if","for","while","switch","case","while","extern","#define","#include");
        
        
        if( preg_match_all( '/<code( num="([0-9]{1,5}?)")?>(.*?)<\/code>/', $string, $matches, PREG_SET_ORDER ) ) {
            foreach( $matches as $value ) {
                $num = $value[2] ? $value[2] : 1;
                $count_string = "";
                $count = count( explode( '<br />', $value[3] ) );
                for( $i = $num; $i < $count + $num; $i++ ) {
                    $count_string .= "$i<br />";
                }
                // キーワード
                foreach( $keyword as $value2 ) {
                    $value[3] = preg_replace( '/(^|>|&|;|<|,|\()('.$value2.')(&|;|<|,|\))/', "$1<span class=\"keyword\">$2</span>$3" , $value[3] );
                }
                
                // コメント
                $value[3] = preg_replace( '/(\/\*.*?\*\/|\/\/.*?)<br \/>/', "<span class=\"comment\">$1</span><br />" , $value[3] );
                
                // 文字列
                $code_string = preg_replace( '/((&quot;|&#39;).*?(&quot;|&#39;))/', "<span class=\"string\">$1</span>" , $value[3] );
                
                $string = preg_replace( '/<code( num="([0-9]{1,5}?)")?>.*?<\/code>/', "</p><div class=\"code\"><table><tr><th>$count_string</th><td>$code_string</td></tr></table></div><p>" , $string, 1 );
            }
        }
        
        return $string;
    }
    
    /**
     *
     * @param 変換前 $string(string) 囲み文字 $delimiter(string)
     * @return 変換後
     *
     */
    static function rip_program_highlight( $string, $delimiter = 'code' )
    {
        if( preg_match_all( '/<div class="'.$delimiter.'"><table><tr><th>.*?<\/th><td>(.*?)<\/td><\/tr><\/table><\/div>/', $string, $matches ) ) {
            foreach( $matches[1] as $value ) {
                $string = preg_replace( '/<div class="'.$delimiter.'"><table><tr><th>.*?<\/th><td>'.$value.'<\/td><\/tr><\/table><\/div>', "<$delimiter><br />$value</$delimiter>" , $string, 1 );
            }
        }
        
        return $string;
    }
    
    /**
     *
     * @param 引用元 $string(string) 引用文字数 $length(int) タグを取り除く $html_tags(bool) 取り除かないタグ $tags(string)
     * @return 変換後
     *
     */
    static function quotation( $string, $length, $html_tags = false, $tags = NULL )
    {
        // trueならHTMLタグを取り除く
        if( $html_tags === true ) {
            $string = strip_tags( $string, $tags );
        }
        
        if( mb_strlen( $string ) > $length ) {
            return mb_substr( $string, 0, $length, $encode ) . "...";
        }
        return $string;
    }
    
    /**
     *
     * @param 変換前 $string(string)
     * @return 変換後
     *
     */
    static function auto_link( $string )
    {
        // タグを削除
        $string = preg_replace( '/&lt;a&nbsp;.+&gt;(.+)&lt;\/a&gt;/i', "[$1]", $string );
        
        // 自動リンクを挿入
        $string = preg_replace( '/\[?(http:\/\/[\w\.\/\-=&%?,;#]*)\]?/', "<a href=\"$1\" target=\"_blank\">$1</a>", $string );
        
        return $string;
    }
    
    /**
     *
     * @param 変換前 $string(string)
     * @return 変換後
     *
     */
    static function replace_tags( $string )
    {
        $search = array(
                '/\[b\](.*?)\[\/b\]/is',
                '/\[i\](.*?)\[\/i\]/is',
                '/\[u\](.*?)\[\/u\]/is',
                '/\[url\=(http:\/\/[\w\.\/\-=&%?,;#]*)\](.*?)\[\/url\]/is',
                '/\[color\=(#[0-9a-f]{6}?)\](.*?)\[\/color\]/is',
                '/\[code](<br \/>)?(.*?)(<br \/>)*\[\/code\]/is',
                '/\[code\=([0-9]{1,5}?)\](<br \/>)?(.*?)(<br \/>)*\[\/code\]/is'
                );
        
        $replace = array(
                '<strong>$1</strong>',
                '<em>$1</em>',
                '<u>$1</u>',
                '<a href="$1" target="_blank">$2</a>',
                '<span color="$1">$2</span>',
                '<code>$2</code>',
                '<code num="$1">$3</code>'
                );
        
        $string = preg_replace( $search, $replace, $string ); 
        
        return $string;
    }
    
    /**
     *
     * @param 変換前 $timestamp(timestamp)
     * @return 変換後
     *
     */
    static function time_string( $timestamp, $hour_off = false )
    {
        $day_the_week = array( "日", "月", "火", "水", "木", "金", "土" );
        
        $w = date( "w", $timestamp );
        
        if( $hour_off ) {
            return date( "Y/m/d($day_the_week[$w])", $timestamp );
        } else {
            return date( "Y/m/d($day_the_week[$w]) H:i", $timestamp );
        }
    }
}

?>
