<?php
/**
 * Created by PhpStorm.
 * Project: morkovka
 * User: lxShaDoWxl
 * Date: 12.10.15
 * Time: 10:33
 */
namespace backendold\helpers;

use yii\helpers\ArrayHelper;

class SArrayHelper extends ArrayHelper
{
    /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     * arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function mergeOptions($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    if (isset($res[$k])) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::mergeOptions($res[$k], $v);
                } else {
                    if ($k == 'class' && isset($res[$k])) {
                        $array = self::merge(explode(' ', $res[$k]), explode(' ', $v));
                        $class_a = [];
                        foreach ($array as $key => $value) {
                            $value = trim($value);
                            if ($value && !in_array($value, $class_a)) {
                                $class_a[] = $value;
                            }
                        }
                        if ($class_a) {
                            $res[$k] = implode(' ', $class_a);
                        }
                    } else {
                        $res[$k] = $v;
                    }
                }
            }
        }
        return $res;
    }
    /**
     * Generate list of months
     *
     * @return array
     */
    public static function months()
    {
        return [
            1 => \Yii::t('shadow', 'January'),
            2 => \Yii::t('shadow', 'February'),
            3 => \Yii::t('shadow', 'March'),
            4 => \Yii::t('shadow', 'April'),
            5 => \Yii::t('shadow', 'May'),
            6 => \Yii::t('shadow', 'June'),
            7 => \Yii::t('shadow', 'July'),
            8 => \Yii::t('shadow', 'August'),
            9 => \Yii::t('shadow', 'September'),
            10 => \Yii::t('shadow', 'October'),
            11 => \Yii::t('shadow', 'November'),
            12 => \Yii::t('shadow', 'December'),
        ];
    }
    /**
     * Generate list of days
     * @param string $type
     * @return array
     */
    public static function days($type = 'full')
    {
        if ($type == 'small') {
            return [
                1 => \Yii::t('shadow', 'Mon'),
                2 => \Yii::t('shadow', 'Tue'),
                3 => \Yii::t('shadow', 'Wed'),
                4 => \Yii::t('shadow', 'Thu'),
                5 => \Yii::t('shadow', 'Fri'),
                6 => \Yii::t('shadow', 'Sat'),
                '0' => \Yii::t('shadow', 'Sun'),
            ];
        } else {
            return [
                1 => \Yii::t('shadow', 'Monday'),
                2 => \Yii::t('shadow', 'Tuesday'),
                3 => \Yii::t('shadow', 'Wednesday'),
                4 => \Yii::t('shadow', 'Thursday'),
                5 => \Yii::t('shadow', 'Friday'),
                6 => \Yii::t('shadow', 'Saturday'),
                '0' => \Yii::t('shadow', 'Sunday'),
            ];
        }
    }
}