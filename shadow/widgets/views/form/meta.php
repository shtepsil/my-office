<?php
/**
 * Created by PhpStorm.
 * Project: yii2-cms
 * User: lxShaDoWxl
 * Date: 27.04.15
 * Time: 15:42
 * @var \yii\db\ActiveRecord | \yii\base\Model $item
 */
use yii\helpers\Json;

$meta = [];
$seo = [
	'title'=>'',
	'keywords' => '',
	'description' => ''
];
if(!$item->isNewRecord){
	$table = $item->tableName();
	switch($table){
		case 'structure':
			if($item->seo){
				$seo = Json::decode($item->seo);
			}
			break;
	}
}
?>
<div class="panel-body">
	<div class="form-group" id="field-meta_title">
		<label class="control-label col-md-3" for="page_meta_title">Мета заголовок</label>
		<div class="col-md-9">
			<input type="text" id="page_meta_title" name="seo[title]" value="<?=$seo['title']?>" class="form-control">
			<span class="help-block text-muted"></span>
		</div>
	</div>
	<div class="form-group" id="field-meta_keywords">
		<label class="control-label col-md-3" for="page_meta_keywords">Мета ключевые слова</label>
		<div class="col-md-9">
			<input type="text" id="page_meta_keywords" name="seo[keywords]" value="<?=$seo['keywords']?>" class="form-control">
			<span class="help-block text-muted"></span>
		</div>
	</div>
	<div class="form-group" id="field-meta_description">
		<label class="control-label col-md-3" for="page_meta_description">Мета описание</label>
		<div class="col-md-9">
			<textarea id="page_meta_description" name="seo[description]" cols="50" rows="3" class="form-control"><?=$seo['description']?></textarea>
			<span class="help-block text-muted"></span>
		</div>
	</div>
</div>
