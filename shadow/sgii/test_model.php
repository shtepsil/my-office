<?php
namespace common\models;


class GalleryTest extends \shadow\SActiveRecord
{

    public function FormParams()
    {
        if ($this->isNewRecord) {
            $this->loadDefaultValues(true);
        }
        $controller_name = Inflector::camel2id(Yii::$app->controller->id);
        $fields = [
            'name' => [],
            'brand_id' => [
                'type' => 'dropDownList',
                'relation' => [
                    'class'=>Brands::className()
                ],
            ],
            'body' => [
                'type' => 'textArea',
                'widget' => [
                    'class' => CKEditor::className(),
                    'config' => [
                        'editorOptions' => [
                            'enterMode' => 1
                        ]
                    ]
                ]
            ],
        ];
        $result = [
            'form_action' => ["$controller_name/save"],
            'cancel' => ["site/$controller_name"],
            'groups' => [
                'main' => [
                    'title' => 'Основное',
                    'icon' => 'suitcase',
                    'options' => [],
                    'fields' => $fields,
                ],
            ]
        ];
        if ($this->getBehavior('ml')) {
            $this->ParamsLang($result, $fields);
        }
        return $result;
    }
}