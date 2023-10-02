<?php
namespace shadow\sgii\model;

use shadow\db\mysql\Schema;
use yii\base\NotSupportedException;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use Yii;

/**
 * This generator will generate one or multiple ActiveRecord classes for the specified database table.
 *
 * @author lxShaDoWxl <viktor-09-05@mail.ru>
 * @since 0.0.1
 */
class Generator extends \yii\gii\generators\model\Generator
{
    const MULTILANGS_NONE = 'none';
    const MULTILANGS_YES = 'yes';
    const MULTILANGS_FORCE = 'force_all';
    /**
     * @var bool whether to overwrite (extended) model classes, will be always created, if file does not exist
     */
    public $generateModelClass = false;

    /**
     * @var null string for the table prefix, which is ignored in generated class name
     */
    public $tablePrefix = null;
    /**
     * @var string Создание мультиязычности в модели
     */
    public $multilangs = self::MULTILANGS_NONE;
    /**
     * @var string
     */
    public $nsController = 'backend\controllers';
    /**
     * @var string
     */
    public $nsViews = 'backend\views\modules';
    /**
     * @var string
     */
    public $nameModule = '';
    /**
     * @var array key-value pairs for mapping a table-name to class-name, eg. 'prefix_FOObar' => 'FooBar'
     */
    public $tableNameMap = [];
    protected $classNames2;
    protected $init_behaviors = [];
    protected $relations;
    /**
     * @var array Конфигурация настроки вывода поля в форме
     */
    public $form_fields;
    /**
     * @var array Конфигурация настроки вывода групп в форме
     */
    public $form_group = [];
    /**
     * @var bool Добавлять ли сценарий insert при добавление записи
     */
    public $required_insert = false;
    /**
     * @var array Массив пронстранств имен для добавление в класс
     */
    public $uses_add = [];
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Yii2 SGii';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class and controller for the specified database table.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
//                [['nsController', 'nsViews'], 'filter', 'filter' => 'trim'],
//                [['nsController', 'nsViews'], 'filter', 'filter' => function ($value) { return trim($value, '\\'); }],
//                [['nsController', 'nsViews'], 'required'],
//                [['nsController', 'nsViews'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
//                [['nsController', 'nsViews'], 'validateNamespace'],
                ['nameModule', 'string'],
                [['generateModelClass'], 'boolean'],
                [['multilangs'], 'in', 'range' => [self::MULTILANGS_NONE, self::MULTILANGS_YES, self::MULTILANGS_FORCE]],
                [['tablePrefix'], 'safe'],
            ]
        );
    }
    /**
     * @inheritdoc
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['multilangs', 'nameModule']);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'generateModelClass' => 'Generate Model Class',
                'multilangs' => 'Мультиязычность',
                'nameModule' => 'Модуль',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(
            parent::hints(),
            [
                'generateModelClass' => 'This indicates whether the generator should generate the model class, this should usually be done only once. The model-base class is always generated.',
                'tablePrefix' => 'Custom table prefix, eg <code>app_</code>.<br/><b>Note!</b> overrides <code>yii\db\Connection</code> prefix!',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php', 'model-extended.php'];
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $files = [];
        $this->relations = $this->generateRelations();
        $db = $this->getDbConnection();
        foreach ($this->getTableNames() as $tableName) {
            $this->init_behaviors = [];
            $className = $this->generateClassName($tableName);
            $tableSchema = $db->getTableSchema($tableName);
            $params = [
                'tableName' => $tableName,
                'className' => $className,
                'tableSchema' => $tableSchema,
                'labels' => $this->generateLabels($tableSchema),
                'rules' => $this->generateRules($tableSchema),
                'relations' => isset($this->relations[$tableName]) ? $this->relations[$tableName] : [],
                'generator' => $this,
            ];
            $this->initBehaviors($tableName);
            $params['init_behaviors'] = $this->init_behaviors;
            $s_model = new SModelController($params);
            $view_name = $tableName;
            if ($this->nameModule) {
                $this->ns = 'backend\modules\\' . $this->nameModule . '\models';
                $this->nsController = 'backend\modules\\' . $this->nameModule . '\controllers' ;
                $this->nsViews = 'backend\modules\\' . $this->nameModule . '\views\\' . Inflector::camel2id($className);
                $view_name = 'index';
            }
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $className . '.php',
                $s_model->renderModel()
            );
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->nsController)) . '/' . $className . 'Controller.php',
                $this->render('controller.php', $params)
            );
            $files[] = new CodeFile(
                Yii::getAlias('@' . str_replace('\\', '/', $this->nsViews)) . '/' . $view_name . '.php',
                $this->render('index_list.php', $params)
            );
            /*$modelClassFile = Yii::getAlias('@' . str_replace('\\', '/', $this->ns)) . '/' . $className . '.php';
            if ($this->generateModelClass || !is_file($modelClassFile)) {
                $files[] = new CodeFile(
                    $modelClassFile,
                    $this->render('model-extended.php', $params)
                );
            }*/
        }
        return $files;
    }

    /**
     * Generates a class name from the specified table name.
     *
     * @param string $tableName the table name (which may contain schema prefix)
     *
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $useSchemaName = null)
    {
        #Yii::trace("Generating class name for '{$tableName}'...", __METHOD__);
        if (isset($this->classNames[$tableName])) {
            #Yii::trace("Using '{$this->classNames2[$tableName]}' for '{$tableName}' from classNames2.", __METHOD__);
            return $this->classNames[$tableName];
        }
        if (isset($this->tableNameMap[$tableName])) {
            Yii::trace("Converted '{$tableName}' from tableNameMap.", __METHOD__);
            return $this->classNames2[$tableName] = $this->tableNameMap[$tableName];
        }
        if (($pos = strrpos($tableName, '.')) !== false) {
            $tableName = substr($tableName, $pos + 1);
        }
        $db = $this->getDbConnection();
        $patterns = [];
        $patterns[] = "/^{$this->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$this->tablePrefix}$/";
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        if (strpos($this->tableName, '*') !== false) {
            $pattern = $this->tableName;
            if (($pos = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                Yii::trace("Mapping '{$tableName}' to '{$className}' from pattern '{$pattern}'.", __METHOD__);
                break;
            }
        }
        $returnName = Inflector::id2camel($className, '_');
        Yii::trace("Converted '{$tableName}' to '{$returnName}'.", __METHOD__);
        return $this->classNames2[$tableName] = $returnName;
    }

    protected function generateRelations()
    {
        $relations = parent::generateRelations();
        // inject namespace | Добавление namespace
        //TODO переписать под определение реального namespace, а не простое добавление
//        $ns = "\\{$this->ns}\\";
//        foreach ($relations AS $model => $relInfo) {
//            foreach ($relInfo AS $relName => $relData) {
//                $relations[$model][$relName][0] = preg_replace(
//                    '/(has[A-Za-z0-9]+\()([a-zA-Z0-9]+::)/',
//                    '$1__NS__$2',
//                    $relations[$model][$relName][0]
//                );
//                $relations[$model][$relName][0] = str_replace('__NS__', $ns, $relations[$model][$relName][0]);
//            }
//        }
        return $relations;
    }
    /**
     * @var array Выбранные behaviors
     */
    public $select_behaviors = [
        'timestamp'
    ];
    /**
     * @var array Какие поля не заносить в валидацию
     * TODO использовать для того behaviors например для Timestamp
     */
    protected $attr_behaviors = [
        'timestamp' => [
            'created_at',
            'updated_at'
        ]
    ];
    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    public function generateRules($table)
    {
        $types = [];
        $lengths = [];
        $imgs = [];
        $files = [];
        $docs = [];
        $date_rules = [];
        $required_scenario = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if ($this->checkAttrBehaviors($column->name)) {
                continue;
            }
            $required = (!$column->allowNull && $column->defaultValue === null);
            if (!isset($this->form_fields[$column->name])) {
                $this->form_fields[$column->name] = [];
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    if (preg_match("/^is[A-Z].*/i", $column->name)) {
                        $this->form_fields = [
                                $column->name => [
                                    'type' => 'checkbox'
                                ]
                            ] + $this->form_fields;
                        $types['integer'][] = $column->name;
                    } elseif (preg_match("/^not_delete$/i", $column->name)) {
                        $types['integer'][] = '!' . $column->name;
                        unset($this->form_fields[$column->name]);
                    } elseif (preg_match("/^date.*/i", $column->name)) {
                        $this->form_fields[$column->name] = [
                            'widget' => [
                                'class' => 'DateTimePicker',
                                'config' => [
                                    'language' => 'ru',
                                    'size' => 'ms',
                                    'template' => '{input}',
                                    'pickButtonIcon' => 'glyphicon glyphicon-time',
                                    'clientOptions' => [
                                        'format' => 'dd/mm/yyyy',
                                        'minView' => 2,
                                        'autoclose' => true,
                                        'todayBtn' => true
                                    ]
                                ]
                            ]
                        ];
                        $date_rules[] = [[$column->name], 'date', 'timestampAttribute' => $column->name, 'format' => 'php:d/m/Y'];
                    } else {
                        $types['integer'][] = $column->name;
                    }
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case 'double': // Schema::TYPE_DOUBLE, which is available since Yii 2.0.3
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $types['safe'][] = $column->name;
                    break;
                default: // strings
                    if (preg_match("/^img.*/i", $column->name)) {
                        $this->form_fields[$column->name] = [
                            'type' => 'img',
                            'params' => [
                                'deleted' => $column->allowNull
                            ]
                        ];
                        $imgs[] = $column->name;
                        if ($required) {
                            $required_scenario[] = $column->name;
                            $required = false;
                        }
                    } elseif (preg_match("/^file.*/i", $column->name)) {
                        $this->form_fields[$column->name] = [
                            'type' => 'file',
                            'params' => [
                                'deleted' => $column->allowNull
                            ]
                        ];
                        $settings_column = explode('|', $column->comment);
                        if (count($settings_column) > 1) {
                            switch (trim($settings_column[1])) {
                                case 'all':
                                    $files[] = $column->name;
                                    break;
                                default:
                                    $docs[] = $column->name;
                            }
                        } else {
                            $docs[] = $column->name;
                        }
                        if ($required) {
                            $required_scenario[] = $column->name;
                            $required = false;
                        }
                    } else {
                        if ($column->size > 0) {
                            $lengths[$column->size][] = $column->name;
                        } else {
                            $types['string'][] = $column->name;
                            if (preg_match("/^body.*/i", $column->name)) {
                                $this->form_fields[$column->name]['type'] = 'textArea';
                                if (!preg_match("/.*small.*/i", $column->name)) {
                                    $this->form_fields[$column->name]['widget'] = [
                                        'class' => 'CKEditor',
                                        'config' => [
                                            'editorOptions' => [
                                                'enterMode' => 0
                                            ]
                                        ]
                                    ];
                                    $settings_column = explode('|', $column->comment);
                                    if (count($settings_column) > 1) {
                                        switch (trim($settings_column[1])) {
                                            case 'p':
                                                $this->form_fields[$column->name]['widget']['config']['editorOptions']['enterMode'] = 1;
                                                break;
                                            default:
                                        }
                                    }
                                    if (!in_array('shadow\widgets\CKEditor', $this->uses_add)) {
                                        $this->uses_add[] = 'shadow\widgets\CKEditor';
                                    }
                                }
                            }
                        }
                    }
            }
            if ($required) {
                $types['required'][] = $column->name;
            }
        }
        $rules = [];
        foreach ($types as $type => $columns) {
//            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
            $rules[] = [
                $columns,
                $type
            ];
        }
        foreach ($lengths as $length => $columns) {
//            $rules[] = "[['" . implode("', '", $columns) . "'], 'string', 'max' => $length]";
            $rules[] = [
                $columns,
                'string',
                'max' => $length
            ];
        }
        $save_files = [];
        if ($imgs) {
            $save_files = $save_files + $imgs;
            $rules[] = [
                $imgs,
                'image',
                'extensions' => ['jpg', 'gif', 'png', 'jpeg']
            ];
        }
        if ($docs) {
            $save_files = $save_files + $docs;
            $rules[] = [
                $docs,
                'file',
                'extensions' => ['doc', 'docx', 'pdf', 'xlsx', 'xls']
            ];
        }
        if ($files) {
            $save_files = $save_files + $files;
            $rules[] = [
                $files,
                'file',
            ];
        }
        if ($date_rules) {
            foreach ($date_rules as $date_rule) {
                $rules[] = $date_rule;
            }
            $this->uses_add[] = 'shadow\plugins\datetimepicker\DateTimePicker';
        }
        if ($save_files) {
            $this->init_behaviors['save_files'] = $save_files;
        }
        if ($required_scenario) {
            $this->required_insert = true;
            $rules[] = [
                $required_scenario,
                'required',
                'on' => ['insert']
            ];
        }
        $db = $this->getDbConnection();
        // Unique indexes rules
        try {
            $uniqueIndexes = $db->getSchema()->findUniqueIndexes($table);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);
                    if ($attributesCount === 1) {
//                        $rules[] = "[['" . $uniqueColumns[0] . "'], 'unique']";
                        $rules[] = [
                            $uniqueColumns[0],
                            'unique',
                        ];
                    } elseif ($attributesCount > 1) {
                        $labels = array_intersect_key($this->generateLabels($table), array_flip($uniqueColumns));
                        $lastLabel = array_pop($labels);
//                        $columnsList = implode("', '", $uniqueColumns);
//                        $rules[] = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList'], 'message' => 'The combination of " . implode(', ', $labels) . " and $lastLabel has already been taken.']";
                        $rules[] = [
                            $uniqueColumns,
                            'unique',
                            'targetAttribute' => $uniqueColumns,
                            'message' => "The combination of " . implode(', ', $labels) . " and $lastLabel has already been taken."
                        ];
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }
        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
//            $targetAttributes = implode(', ', $targetAttributes);
//            $rules[] = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
            $this->form_fields[$attributes] = [
                'type' => 'dropDownList',
                'relation' => [
                    'class' => $refClassName
                ],
            ];
            $rules[] = [
                $attributes,
                'exist',
                'skipOnError' => true,
                'targetClass' => $refClassName,
                'targetAttribute' => $refs
            ];
        }
        return $rules;
    }
    private function checkAttrBehaviors($name)
    {
        if ($this->select_behaviors) {
            foreach ($this->select_behaviors as $select_behavior) {
                if (
                    isset($this->attr_behaviors[$select_behavior])
                    &&
                    in_array($name, $this->attr_behaviors[$select_behavior])
                ) {
                    $this->init_behaviors[$select_behavior] = true;
                    return true;
                }
            }
        }
        return false;
    }
    protected function initBehaviors($tableName)
    {
        $relations = isset($this->relations[$tableName]) ? $this->relations[$tableName] : [];
        $class_name = $this->generateClassName($tableName);
        $db = $this->getDbConnection();
        if ($relations) {
            if (isset($relations[$class_name . 'Langs'])) {
                $table_schema = $db->getSchema()->getTableSchema($tableName . '_lang');
                $columns = $table_schema->columnNames;
                if (count($columns) > 3) {
                    unset($columns[0], $columns[1], $columns[2]);
                    $this->init_behaviors['ml'] = $columns;
                }
            }
            if (isset($relations[$class_name . 'Imgs'])) {
                $relation_img = $relations[$class_name . 'Imgs'];
                preg_match("/has[A-Za-z0-9]+\([a-zA-Z0-9]+::className\(\),\s\['(.*)'\s=>/", $relation_img[0], $match);
                if (isset($match[1])) {
                    $this->init_behaviors['save_relations'][$relation_img[1]] = [
                        'type' => 'img',
                        'attribute' => $match[1]
                    ];
                }
            }
        }
    }
}