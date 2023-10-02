<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 28.07.15
 * Time: 10:51
 */
namespace shadow\plugins\adjacencylist;

class AdjacencyListAR extends \shadow\SActiveRecord
{
    public static function find()
    {
        return new AdjacencyListQuery(get_called_class());
    }
}