<?php
/**
 * @link https://github.com/paulzi/yii2-adjacency-list
 * @copyright Copyright (c) 2015 PaulZi <pavel.zimakoff@gmail.com>
 * @license MIT (https://github.com/paulzi/yii2-adjacency-list/blob/master/LICENSE)
 */

namespace shadow\plugins\adjacencylist;

/**
 * @author PaulZi <pavel.zimakoff@gmail.com>
 */
trait AdjacencyListQueryTrait
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function roots()
    {
        /** @var \yii\db\ActiveQuery $this */
        $class = $this->modelClass;
        if (isset($class::$adjacencyListParentAttribute)) {
            return $this->andWhere([$class::$adjacencyListParentAttribute => null]);
        } else {
            /** @var \yii\db\ActiveRecord|AdjacencyListBehavior $model */
            $model = new $class;
            return $this->andWhere([$model->parentAttribute => null]);
        }
    }
}
