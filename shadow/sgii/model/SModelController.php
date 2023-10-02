<?php
/**
 * Created by PhpStorm.
 * Project: yii2_clear
 * User: lxShaDoWxl
 * Date: 08.06.16
 * Time: 15:59
 */
namespace shadow\sgii\model;

use PhpParser\Builder;
use PhpParser\BuilderAbstract;
use PhpParser\BuilderFactory;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use shadow\helpers\SArrayHelper;
use shadow\sgii\PrinterCode;
use yii\base\Object;
use yii\db\TableSchema;

class SModelController extends Object
{
    /**
     * @var \shadow\sgii\model\Generator
     */
    public $generator;
    /**
     * @var string Название таблицы
     */
    public $tableName;
    /**
     * @var string Название класса
     */
    public $className;
    /**
     * @var TableSchema Schema таблицы
     */
    public $tableSchema;

    /**
     * @var string[] Названия полей
     */
    public $labels;
    /**
     * @var string[] Условия для валидации
     */
    public $rules;
    /**
     * @var array Связи
     */
    public $relations;
    /**
     * @var array Поведения Behaviors
     */
    public $init_behaviors;
    /**
     * @var bool Мультиязычность
     */
    public $multi_lang = false;
    /**
     * @var string
     */
    public $default_doc = "/**\n * @inheritdoc\n */";
    /**
     * @var BuilderFactory
     */
    private $factory;
    /**
     * @var Builder\Class_
     */
    private $node;
    /**
     * @var Parser\Php5
     */
    private $parser;

    private $uses_add = [];
    /**
     * Initializes the object.
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     */
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        $this->factory = new BuilderFactory;
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP5);
    }

    public function renderModel()
    {
//        $arra_test = $this->parser->parse(file_get_contents(\Yii::getAlias('@shadow/sgii/test_model.php')));
        $node_namespace = $this->factory->namespace($this->generator->ns);
        $node_namespace->addStmt(new Node\Stmt\Use_(
            [
                new  Node\Stmt\UseUse(
                    new Node\Name('yii')
                )
            ]
        ));
        $this->node = $this->factory->class($this->className)
            ->setDocComment($this->docClass())
            ->extend('\\' . ltrim($this->generator->baseClass, '\\'));
        $this->node->addStmt(
            $this->factory->method('tableName')
                ->makePublic()
                ->makeStatic()
                ->setDocComment($this->default_doc)
                ->addStmt(new Node\Stmt\Return_(new Node\Scalar\String_($this->tableName)))
        );
        $this->node->addStmt(
            $this->factory->method('rules')
                ->makePublic()
                ->setDocComment($this->default_doc)
                ->addStmt(new Node\Stmt\Return_($this->ExpArray($this->rules)))
        );
        $this->initLabels();
        $this->initRelations();
        $this->initBehaviors();
        $this->initForms();
        if ($this->generator->required_insert) {
            $this->node->addStmt(
                $this->factory->method('beforeValidate')
                    ->makePublic()
                    ->setDocComment($this->default_doc)
                    ->addStmt(
                        new Node\Stmt\If_(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                'isNewRecord'
                            ),
                            [
                                'stmts' => [
                                    new Node\Expr\Assign(
                                        new Node\Expr\PropertyFetch(
                                            new Node\Expr\Variable('this'),
                                            'scenario'
                                        ),
                                        new Node\Scalar\String_('insert')
                                    )
                                ],
                            ]
                        )
                    )
                    ->addStmt(
                        new Node\Stmt\Return_(
                            new Node\Expr\StaticCall(
                                new Node\Name(['parent']),
                                'beforeValidate'
                            )
                        )
                    )
            );
        }
        foreach (SArrayHelper::merge($this->uses_add, $this->generator->uses_add) as $use) {
            $node_namespace->addStmt(new Node\Stmt\Use_(
                [
                    new  Node\Stmt\UseUse(
                        new Node\Name($use)
                    )
                ]
            ));
        }
        $node_namespace->addStmt($this->node);
        $stmts = array($node_namespace->getNode());
        $prettyPrinter = new PrinterCode([
            'shortArraySyntax' => Node\Expr\Array_::KIND_SHORT
        ]);
        return $prettyPrinter->prettyPrintFile($stmts);
    }

    /**
     * @return string основной PHPDoc
     */
    private function docClass()
    {
        $result = "\n/**\n * This is the model class for table \"{$this->tableName}\".\n *\n";
        foreach ($this->tableSchema->columns as $column) {
            $result .= " * @property {$column->phpType} \${$column->name}\n";
        }
        if (!empty($this->relations)) {
            $result .= " *\n";
            foreach ($this->relations as $name => $relation) {
                $result .= ' * @property ' . $relation[1] . ($relation[2] ? '[]' : '') . " $" . lcfirst($name) . "\n";
            }
        }
        $result .= ' */';
        return $result;
    }

    private function initLabels()
    {
        foreach ($this->labels as &$label) {
            $a = explode('|', $label);
            $label = trim($a[0]);
        }
        $labels = $this->ExpArray($this->labels);
        $method = $this->factory->method('attributeLabels')
            ->makePublic()
            ->setDocComment($this->default_doc);
        if ($this->generator->multilangs == 'none') {
            $method->addStmt(new Node\Stmt\Return_($labels));
        } else {
            $method
                ->addStmt(
                    new Node\Expr\Assign(
                        new Node\Expr\Variable('result'),
                        $labels
                    )
                )
                ->setDocComment($this->default_doc)
                ->addStmt(
                    $this->multilangLabels()
                );
            $method->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('result')));
        }
        $this->node->addStmt($method);
    }

    private function initRelations()
    {
        $doc_php = "/**\n * @return \yii\db\ActiveQuery\n */";
        foreach ($this->relations as $name => $relation) {
            if (!class_exists('backend\models\Pages', false)) {
                $test = false;
            }
            $name = str_replace("0", "", $name);
            $method = $this->factory->method('get' . $name)
                ->makePublic()
                ->setDocComment($doc_php);
            $method->addStmts($this->parser->parse('<? ' . $relation[0]));
            $this->node->addStmt($method);
        }
    }
    private function initBehaviors()
    {
        if ($this->init_behaviors) {
            $behaviors = [];
            $method = $this->factory->method('behaviors')
                ->makePublic()
                ->setDocComment($this->default_doc);
            if (isset($this->init_behaviors['timestamp'])) {
                $this->uses_add[] = 'yii\behaviors\TimestampBehavior';
                $behaviors[] = new Node\Expr\ArrayItem(
                    new Node\Expr\StaticCall(
                        new Node\Name(['TimestampBehavior']),
                        'className'
                    ),
                    null
                );
            }
            if (isset($this->init_behaviors['ml'])) {
                $this->uses_add[] = 'shadow\multilingual\behaviors\MultilingualBehavior';
                $this->uses_add[] = 'shadow\multilingual\behaviors\MultilingualQuery';
                $behaviors[] = new Node\Expr\ArrayItem(
                    $this->generateMultiLangBehavior(),
                    new Node\Scalar\String_('ml')
                );
            }
            if (isset($this->init_behaviors['save_relations'])) {
                $behaviors[] = new Node\Expr\ArrayItem(
                    $this->generateSaveRelationsBehavior(),
                    null
                );
            }
            if (isset($this->init_behaviors['save_files'])) {
                $behaviors[] = new Node\Expr\ArrayItem(
                    $this->generateSaveFilesBehavior(),
                    null
                );
            }
            $method->addStmt(new Node\Stmt\Return_(new Node\Expr\Array_($behaviors, ['new_line' => true])));
            $this->node->addStmt($method);
        }
    }
    private function initForms()
    {
        if ($this->generator->form_fields) {
            $this->uses_add[] = 'yii\helpers\Inflector';
            $method = $this->factory->method('FormParams')
                ->makePublic();
            $ElseIsNewRecord = null;
            $StmtsIsNewRecord = [
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable('this'),
                    'loadDefaultValues',
                    [
                        new Node\Arg(
                            $this->normalizeValue(true)
                        )
                    ]
                )
            ];
            foreach ($this->rules as $rule) {
                if ($rule[1] === 'date') {
                    foreach ($rule[0] as $value) {
//                        $StmtsIsNewRecord[]=new Node\Expr\PropertyFetch(
//                            new Node\Expr\Variable('this'),
//                            $value
//                        );
                        $StmtsIsNewRecord[] = new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                $value
                            ),
                            new Node\Expr\FuncCall(
                                new Node\Name(['date']),
                                [
                                    new Node\Arg(
                                        $this->normalizeValue(str_replace('php:', '', $rule['format']))
                                    )
                                ]
                            )
                        );
                        $ElseIsNewRecord[] = new Node\Expr\Assign(
                            new Node\Expr\PropertyFetch(
                                new Node\Expr\Variable('this'),
                                $value
                            ),
                            new Node\Expr\FuncCall(
                                new Node\Name(['date']),
                                [
                                    new Node\Arg(
                                        $this->normalizeValue(str_replace('php:', '', $rule['format']))
                                    ),
                                    new Node\Arg(
                                        new Node\Expr\PropertyFetch(
                                            new Node\Expr\Variable('this'),
                                            $value
                                        )
                                    ),
                                ]
                            )
                        );
                    }
                }
            }
            if ($ElseIsNewRecord) {
                $ElseIsNewRecord = new Node\Stmt\Else_(
                    $ElseIsNewRecord
                );
            }
            $method->addStmt(
                new Node\Stmt\If_(
                    new Node\Expr\PropertyFetch(
                        new Node\Expr\Variable('this'),
                        'isNewRecord'
                    ),
                    [
                        'stmts' => $StmtsIsNewRecord,
                        'else' => $ElseIsNewRecord
                    ]
                )
            );
            $method->addStmt(
                new Node\Expr\Assign(
                    new Node\Expr\Variable('controller_name'),
                    new Node\Expr\StaticCall(
                        new Node\Name(['Inflector']),
                        'camel2id',
                        [
                            new Node\Arg(
                                new Node\Expr\PropertyFetch(
                                    new Node\Expr\PropertyFetch(
                                        new Node\Expr\StaticPropertyFetch(
                                            new Node\Name(['Yii']),
                                            'app'
                                        ),
                                        'controller'
                                    ),
                                    'id'
                                )
                            ),
                        ]
                    )
                )
            );
            $method->addStmt(
                new Node\Expr\Assign(
                    new Node\Expr\Variable('fields'),
                    $this->ExpArray($this->generator->form_fields, true, true)
                )
            );
            $main_group = $this->ExpArray(
                [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
//                    'fields' => $fields,
                ]
                , true, true);
            $main_group->items[] = new Node\Expr\ArrayItem(
                new Node\Expr\Variable('fields'),
                $this->normalizeValue('fields')
            );
            $main_group->setAttribute('new_line', true);
            $main_group->setAttribute('count_tab', 3);
            $groups = new Node\Expr\Array_(
                [
                    new Node\Expr\ArrayItem(
                        $main_group,
                        $this->normalizeValue('main')
                    )
                ],
                [
                    'new_line' => true,
                    'count_tab' => 2
                ]
            );
            foreach ($this->generator->form_group as $key_g => $group) {
                $this->_level = 2;
                $groups->items[] = new Node\Expr\ArrayItem(
                    $this->ExpArray($group, true, true),
                    $this->normalizeValue($key_g)
                );
            }
            $this->_level = 0;
            $method->addStmt(
                new Node\Expr\Assign(
                    new Node\Expr\Variable('result'),
                    new Node\Expr\Array_(
                        [
                            new Node\Expr\ArrayItem(
                                new Node\Expr\Array_(
                                    [
                                        new Node\Expr\ArrayItem(
                                            new Node\Expr\BinaryOp\Concat(
                                                new Node\Expr\Variable('controller_name'),
                                                new Node\Scalar\String_('/save')
                                            )
//                                            new Node\Scalar\Encapsed(
//                                                [
//                                                    new Node\Expr\Variable('controller_name'),
//                                                    new Node\Scalar\EncapsedStringPart('/save')
//                                                ]
//                                            )
                                        )
                                    ]
                                ),
                                $this->normalizeValue('form_action')
                            ),
                            new Node\Expr\ArrayItem(
                                new Node\Expr\Array_(
                                    [
                                        new Node\Expr\ArrayItem(
                                            new Node\Expr\BinaryOp\Concat(
                                                new Node\Expr\Variable('controller_name'),
                                                new Node\Scalar\String_('/index')
                                            )
                                        )
                                    ]
                                ),
                                $this->normalizeValue('cancel')
                            ),
                            new Node\Expr\ArrayItem(
                                $groups,
                                $this->normalizeValue('groups')
                            )
                        ],
                        [
                            'new_line' => true,
                            'count_tab' => 1
                        ]
                    )
                )
            );
            if ($this->generator->multilangs !== 'none') {
                $method->addStmt(
                    new Node\Stmt\If_(
                        new Node\Expr\MethodCall(
                            new Node\Expr\Variable('this'),
                            'getBehavior',
                            [
                                new Node\Arg(
                                    new Node\Scalar\String_('ml')
                                )
                            ]
                        ),
                        [
                            'stmts' => [
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable('this'),
                                    'ParamsLang',
                                    [
                                        new Node\Arg(
                                            new Node\Expr\Variable('result')
                                        ),
                                        new Node\Arg(
                                            new Node\Expr\Variable('fields')
                                        ),
                                    ]
                                )
                            ]
                        ]
                    )
                );
            }
            $method->addStmt(new Node\Stmt\Return_(new Node\Expr\Variable('result')));
            $this->node->addStmt($method);
        }
    }
    private function generateMultiLangBehavior()
    {
        $ml = $this->parser->parse(<<<PHP
<?
\$ml=[
    'class' => MultilingualBehavior::className(),
    'languages' => Yii::\$app->params['languages'],
    'defaultLanguage' => 'ru',
    'langForeignKey' => 'owner_id',
    'tableName' => "{{%category_lang}}",
    'attributes' => [
        'name',
        'body',
    ]
];
PHP
        );
        /** @var Node\Expr\Array_ $ml */
        $ml = $ml[0]->expr;
        $ml->setAttribute('count_tab', 2);
        $ml->items[4]->value->value = "{{%{$this->tableName}_lang}}";
        $attr = $this->ExpArray($this->init_behaviors['ml']);
        $attr->setAttribute('count_tab', 3);
        $ml->items[5]->value = $attr;
        $ml->setAttribute('new_line', true);
        $this->node->addStmt(
            $this->factory
                ->method('find')
                ->makePublic()
                ->makeStatic()
                ->setDocComment($this->default_doc)
                ->addStmt(
                    new Node\Expr\Assign(
                        new Node\Expr\Variable('q'),
                        new Node\Expr\New_(
                            new Node\Name(['MultilingualQuery']),
                            [
                                new Node\Arg(
                                    new Node\Expr\FuncCall(
                                        new Node\Name(['get_called_class'])
                                    )
                                )
                            ]
                        )
                    )
                )
                ->addStmt(new Node\Stmt\If_(
                    new Node\Expr\BinaryOp\Equal(
                        new Node\Expr\PropertyFetch(
                            new Node\Expr\StaticPropertyFetch(
                                new Node\Name(['Yii']),
                                'app'
                            ),
                            'id'
                        ),
                        new Node\Scalar\String_('app-backend')
                    ),
                    [
                        'stmts' => [
                            new Node\Expr\MethodCall(
                                new Node\Expr\Variable('q'),
                                'multilingual'
                            )
                        ],
                        'else' => new Node\Stmt\Else_(
                            [
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable('q'),
                                    'localized'
                                )
                            ]
                        )
                    ]
                ))
                ->addStmt(
                    new Node\Stmt\Return_(new Node\Expr\Variable('q'))
                )
        );
        return $ml;
    }
    private function generateSaveRelationsBehavior()
    {
        $save_relation = $this->parser->parse(<<<PHP
<?
\$save_relation=[
    'class' => \shadow\behaviors\SaveRelationBehavior::className(),
    'relations' => [
    ]
];
PHP
        );
        /** @var Node\Expr\Array_ $save_relation */
        $save_relation = $save_relation[0]->expr;
        $save_relation->setAttribute('count_tab', 2);
//        $relations = $this->ExpArray($this->init_behaviors['ml']);
        $relations = new Node\Expr\Array_();
        foreach ($this->init_behaviors['save_relations'] as $key => $value) {
            $this->_level = 3;
            $relations->items[] = new Node\Expr\ArrayItem(
                $this->ExpArray($value, true, true),
                new Node\Expr\StaticCall(
                    new Node\Name([$key]),
                    'className'
                )
            );
            if (isset($value['type']) && $value['type'] == 'img') {
                $this->generator->form_group['imgs'] = [
                    'title' => 'Изображения',
                    'icon' => 'picture-o',
                    'options' => [],
                    'fields' => [
                        'js_files' => [
                            'files' => [
                                'relation' => [
                                    'class' => $key,
                                    'query' => [
                                        'where' => [
                                            $value['attribute'] =>
                                                new Node\Expr\PropertyFetch(
                                                    new Node\Expr\Variable('this'),
                                                    'id'
                                                )
                                        ]
                                    ]
                                ],
                                'name' => lcfirst($key),
                                'filters' => [
                                    'imageFilter' => true,
                                ],
                            ]
                        ],
                    ]
                ];
            }
        }
        $this->_level = 0;
        $relations->setAttribute('count_tab', 3);
        $relations->setAttribute('new_line', true);
        $save_relation->items[1]->value = $relations;
        $save_relation->setAttribute('new_line', true);
        return $save_relation;
    }
    private function generateSaveFilesBehavior()
    {
        $save_files = $this->parser->parse(<<<PHP
<?
\$save_relation=[
    'class' => \shadow\behaviors\UploadFileBehavior::className(),
    'attributes' => [
    ]
];
PHP
        );
        /** @var Node\Expr\Array_ $save_file */
        $save_file = $save_files[0]->expr;
        $save_file->setAttribute('count_tab', 2);
        $attr = $this->ExpArray($this->init_behaviors['save_files']);
        $attr->setAttribute('count_tab', 3);
        $save_file->items[1]->value = $attr;
        $save_file->setAttribute('new_line', true);
        return $save_file;
    }
    /**
     * @return Node\Stmt\If_
     */
    private function multilangLabels()
    {
        $if = new Node\Stmt\If_(
            new Node\Expr\Assign(
                new Node\Expr\Variable('ml'),
                new Node\Expr\MethodCall(
                    new Node\Expr\Variable('this'),
                    'getBehavior',
                    [
                        new Node\Arg(
                            new Node\Scalar\String_('ml')
                        )
                    ]
                )
            )
        );
        $if->setAttribute('comments', [new Comment\Doc('/**@var $ml MultilingualBehavior */')]);
        $if->stmts[] = new Node\Expr\MethodCall(
            new Node\Expr\Variable('ml'),
            'attributeLabels',
            [
                new Node\Arg(
                    new Node\Expr\Variable('result')
                )
            ]
        );
        return $if;
    }

    protected $_level = 0;
    /**
     * @param $items array
     * @param $new_line bool
     * @param $count_tab bool
     * @return Node\Expr\Array_
     */
    private function ExpArray($items, $new_line = true, $count_tab = false)
    {
        $result = [];
        $level = $this->_level;
        $current_level = $this->_level;
        $array = new Node\Expr\Array_($result, ['new_line' => $new_line]);
        foreach ($items as $key => $value) {
            if (!is_numeric($key)) {
                $key_item = new Node\Scalar\String_($key);
            } else {
                $key_item = null;
            }
            if (is_array($value)) {
                $this->_level++;
                if ($level == 1 && $this->_level < 2) {
                    $result[] = new Node\Expr\ArrayItem($this->ExpArray($value, true, $count_tab), $key_item);
                    $array->setAttribute('new_line', false);
                } else {
                    $result[] = new Node\Expr\ArrayItem($this->ExpArray($value, false, $count_tab), $key_item);
                    if ($level > 0) {
                        $array->setAttribute('new_line', false);
                    }
                }
                if ($count_tab && $this->_level > 1) {
                    $array->setAttribute('new_line', true);
                    $array->setAttribute('count_tab', $this->_level);
                }
                $this->_level--;
            } else {
                if ($key === 'targetClass' || $key === 'class') {
                    $result[] = new Node\Expr\ArrayItem(
                        new Node\Expr\StaticCall(
                            new Node\Name([$value]),
                            'className'
                        ),
                        $key_item
                    );
                } else {
                    $result[] = new Node\Expr\ArrayItem($this->normalizeValue($value), $key_item);
                }
                if ($count_tab && $this->_level > 0) {
                    $array->setAttribute('new_line', true);
                    $array->setAttribute('count_tab', $this->_level + 1);
                }
            }
        }
        if ($level == 0) {
            $this->_level = 0;
        }
        $this->_level = $current_level;
        $array->items = $result;
        return $array;
    }
    /**
     * Normalizes a value: Converts nulls, booleans, integers,
     * floats, strings and arrays into their respective nodes
     *
     * @param mixed $value The value to normalize
     *
     * @return Node\Expr The normalized value
     */
    protected function normalizeValue($value)
    {
        if ($value instanceof Node) {
            return $value;
        } elseif (is_null($value)) {
            return new Node\Expr\ConstFetch(
                new Node\Name('null')
            );
        } elseif (is_bool($value)) {
            return new Node\Expr\ConstFetch(
                new Node\Name($value ? 'true' : 'false')
            );
        } elseif (is_int($value)) {
            return new Node\Scalar\LNumber($value);
        } elseif (is_float($value)) {
            return new Node\Scalar\DNumber($value);
        } elseif (is_string($value)) {
            return new Node\Scalar\String_($value);
        } elseif (is_array($value)) {
            $items = [];
            $lastKey = -1;
            foreach ($value as $itemKey => $itemValue) {
                // for consecutive, numeric keys don't generate keys
                if (null !== $lastKey && ++$lastKey === $itemKey) {
                    $items[] = new Node\Expr\ArrayItem(
                        $this->normalizeValue($itemValue)
                    );
                } else {
                    $lastKey = null;
                    $items[] = new Node\Expr\ArrayItem(
                        $this->normalizeValue($itemValue),
                        $this->normalizeValue($itemKey)
                    );
                }
            }
            return new Node\Expr\Array_($items);
        } else {
//            throw new \LogicException('Invalid value');
        }
    }
}