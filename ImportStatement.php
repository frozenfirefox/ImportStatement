<?php
/**
 * @Author: Alpha
 * @Date:   2019-09-12 16:33:34
 * @Last Modified by:   Alpha
 * @Last Modified time: 2019-09-12 16:41:18
 */
namespace App\Libs\mylib;

use Illuminate\Support\Facades\Storage;
use DB;

class ImportStatement{
    protected $database;

    public function __construct()
    {
        $this->database = env('DB_DATABASE', 'canyin');
    }

    //导出表说明及文档
    public function getStatement($file_name = ''){
        $des = '';
        //获取数据库配置项
        $tables = DB::select("select table_name, table_comment from information_schema.tables where table_schema='".$this->database."'"); //

        set_time_limit(0);
        ob_end_clean();
        ob_implicit_flush();
        header('X-Accel-Buffering: no'); // 关键是加了这一行。

        echo "处理中...".date('Y/m/d H:i:s')."<br>";
        //接下来查准查表字段名
        foreach ($tables as $key => $value) {
            $table_info = DB::select("SELECT
              COLUMN_NAME 列名,
              COLUMN_TYPE 数据类型,
              DATA_TYPE 字段类型,
              CHARACTER_MAXIMUM_LENGTH 长度,
              IS_NULLABLE 是否为空,
              COLUMN_DEFAULT 默认值,
              COLUMN_COMMENT 备注
            FROM
             INFORMATION_SCHEMA.COLUMNS
            where
            table_schema ='canyin' AND TABLE_NAME = '".$value->table_name."'");

            //开始拼接字符窜
            $des .= "【".++$key."】".$value->table_name.'【说明】'.$value->table_comment."\r\n";
            $des .= "*********************************************************************************************\r\n";
            foreach ($table_info as $k1 => $v1) {
                if($k1 == 0){
                    $des .= implode('|', array_keys(obj2arr($v1)));
                    $des .= "\r\n";
                }
                $des .= implode('|', obj2arr($v1));
                $des .= "\r\n";
            }

            $des .= "---------------------------------------------------------------------------------------------\r\n";

            echo date('Y/m/d H:i:s').'--表名【'.$value->table_name.'】（'.$key."/".count($tables).'）<br>';
            usleep(100000);
        }

        $file_name = $file_name?:'description-author-alpha'.time().'.txt';

        Storage::put($file_name, $des);

        echo "处理完成...\r\n";

        return '如有疑问请联系alpha，存储文件名为：'.$file_name;
    }
}
