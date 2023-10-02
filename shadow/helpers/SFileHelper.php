<?php
/**
 * Created by PhpStorm.
 * Project: aitas
 * User: lxShaDoWxl
 * Date: 21.01.16
 * Time: 14:18
 */
namespace shadow\helpers;

use yii\helpers\FileHelper;

class SFileHelper extends FileHelper
{
    public static function fileSize($path)
    {
        $bytes = filesize($path);
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' Gb';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' Mb';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' Kb';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }

}