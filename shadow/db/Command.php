<?php

namespace shadow\db;

use yii;

class Command extends yii\db\Command
{
    /**
     * @var string the SQL statement that this command represents
     */
//    private $_sql;
    /**
     * Returns the raw SQL by inserting parameter values into the corresponding placeholders in [[sql]].
     * Note that the return value of this method should mainly be used for logging purpose.
     * It is likely that this method returns an invalid SQL due to improper replacement of parameter placeholders.
     * @return string the raw SQL with parameter values inserted into the corresponding placeholders in [[sql]].
     */
    public function getRawSql()
    {
        if (empty($this->params)) {
            return $this->getSql();
        }
        $params = [];
        foreach ($this->params as $name => $value) {
            if (is_string($name) && strncmp(':', $name, 1)) {
                $name = ':' . $name;
            }
            $type=$this->db->getSchema()->getPdoType($value);
            if ($type==\PDO::PARAM_STR) {
                $params[$name] = $this->db->quoteValue($value);
            } elseif ($type==\PDO::PARAM_BOOL) {
                $params[$name] = ($value ? 'TRUE' : 'FALSE');
            } elseif ($type==\PDO::PARAM_NULL) {
                $params[$name] = 'NULL';
            }elseif ($type==\PDO::PARAM_INT) {
                $params[$name] = $value;
            } elseif (!is_object($value) && !is_resource($value)) {
                $params[$name] = $value;
            }
        }
        if (!isset($params[1])) {
            return strtr($this->getSql(), $params);
        }
        $sql = '';
        foreach (explode('?', $this->getSql()) as $i => $part) {
            $sql .= (isset($params[$i]) ? $params[$i] : '') . $part;
        }

        return $sql;
    }
}