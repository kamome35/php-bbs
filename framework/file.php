<?php

/*
 * タイトル：ファイル操作クラス
 *     概要：ファイルの書き込み、読み込み、作成を行う
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
class File
{
    /**
     *
     * @param ファイル名 $file_name(string)
     * @return 真偽
     *
     */
    static function read( $file_name )
    {
        // 読み込み失敗 || ファイルが存在しない
        if( !is_readable( $file_name ) || !file_exists( $file_name ) ) {
            return false;
        }
        
        // 初期化
        $data = array();
        
        // ファイルロック開始▼
        
        $lp = fopen( $file_name.".lock", 'w' ) or die( "FILE LOCK NO 02\n" );
        flock( $lp, LOCK_EX );
        
        // ファイルオープン
        $fp = fopen( $file_name, 'r' ) or die( "FILE OPEN ERROR NO 01\n" );
        flock( $fp, LOCK_SH );
        
        // データを配列に格納
        while( $line = fgets( $fp ) ) {
            array_push( $data, $line );
        }
        
        // 閉じる
        flock( $fp, LOCK_UN );
        fclose( $fp );
        
        // ファイルロック終了▲
        flock( $lp, LOCK_UN );
        fclose( $lp );
        
        return $data;
    }
    
    /**
     *
     * @param ファイル名 $file_name(string) 書込みデータ $data(string)
     * @return 真偽
     *
     */
    static function write( $file_name, $data )
    {
        // ファイルの有無
        if( file_exists( $file_name ) ) {
            // 書き込み可能か調べる
            if( !is_writable( $file_name ) ) {
                return false;
            }
        } else {
            // 新規作成
            if( !File::create( $file_name ) ) {
                return false;
            }
        }
        
        // 配列を文字列にする
        if( is_array( $data ) ) {
            $data = implode( '', $data );
        }
        
        // ファイルロック開始▼
        $lp = fopen( "{$file_name}.lock", 'w' ) or die( "FILE LOCK NO 02\n" );
        flock( $lp, LOCK_EX );
        
        // ファイルを開く
        $fp = fopen( $file_name, 'rb+' ) or  die( "FILE OPEN ERROR NO 02\n" );
        flock( $fp, LOCK_EX );
        
        // ファイルバッファを有効
        set_file_buffer( $fp, 0 );
        
        // 既存データ削除
        ftruncate( $fp, 0 );
        
        // データを書き込み
        fwrite( $fp, $data );
        
        // ファイルを閉じる
        flock( $fp, LOCK_UN );
        fclose( $fp );
        
        // ファイルロック終了▲
        flock( $lp, LOCK_UN );
        fclose( $lp );
        unlink( "{$file_name}.lock" );
        
        return true;
    }
    
    /**
     *
     * @param ファイル名 $file_name(string)
     * @return 真偽
     *
     */
    static function create( $file_name )
    {
        // 新規作成
        if( !touch( $file_name ) ) {
            // 作成失敗
            return false;
        }
        
        // パーミッションの変更
        chmod( $file_name, 0606 );
        
        return true;
    }
}

?>
