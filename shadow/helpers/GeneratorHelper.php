<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 30.04.15
 * Time: 10:17
 */
namespace shadow\helpers;
use shadow\SCodeFile;
use yii\base\View;

class GeneratorHelper {
    public $options = [];
    public $name = '';
    public $template = '';
    /**
     * @var \yii\gii\CodeFile[]
     */
    public $files;

    public function start($template,$name, $options=[])
    {
        $this->template = $template;
        $this->name = $name;
        $this->options = $options;
        $path_template = \Yii::getAlias('@template').'/'.$this->template.'.php';
        if(file_exists($path_template)){
            $params = [];
            switch($this->template){
                case 'assets':
                    $outputFile = \Yii::getAlias('@frontend/assets').'/'.$this->name.'.php';
                    $params = $this->generateParams();
                    break;
                case 'action':
                    $outputFile = \Yii::getAlias('@frontend/components/actions').'/'.$this->name.'.php';
                    $params = $this->generateParams();
                    break;
                case 'actions':
                    $outputFile = \Yii::getAlias('@frontend/config/actions.php');
                    $params = $this->generateParams();
                    break;
            }

            if (isset($outputFile)) {
                $view = new View();
                $this->files[] = new SCodeFile(
                    $outputFile,
                    $view->renderFile($path_template, $params, $this)
                );
            }
        }
    }
    public function generateParams(){
        $result = [];
        switch($this->template){
            case 'assets':
                $result = $this->options;
                $result['className'] = $this->name;
                if(!isset($result['patch'])){
                    $result['patch'] = '@frontend/assets/'.$this->name;
                }
                break;
            case 'action':
                $result = $this->options;
                $result['className'] = $this->name;
                break;
            case 'actions':
                $result = $this->options;
                break;
        }
        $result['generator'] = $this;
        return $result;
    }
    public function save()
    {
        foreach ($this->files as $file) {
//            $file->operation = SCodeFile::OP_CREATE;
            $file->save();
        }
    }
    public function echoArray($key,$value,$t)
    {
        $search=array(
            "{^('.*'=>\[)}i",
            "{'\"(.*)\"'}m"
        );
        $pattern = array(
            ($t!=1)?"\n". str_repeat(" ", $t+3)."$1": str_repeat(" ", $t+3)."$1" ,
            "$1"
        );
        if(is_array($value)){
            $result ="'" . $key . "'=>[";
            foreach ($value as $keys => $values) {
                $result .= $this->echoArray($keys, $values,$t+4);
            }
            $result .= "\n". str_repeat(" ", $t+3)."],";
            $result = preg_replace($search, $pattern, $result);
            return $result;
        }else{
            $result ="\n". str_repeat(" ", $t+3). "'" . $key . "'=>'" . $value . "',";
//			$result = preg_replace($search, $pattern, $result);
            return $result;
        }
    }
}