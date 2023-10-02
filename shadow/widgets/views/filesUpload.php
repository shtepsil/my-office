<?php
/**
 * @var $this \yii\web\View
 * @var $context shadow\widgets\FilesUpload
 * @var $id int
 * @var $filters array
 * @var $files array
 */
use shadow\assets\FilesUploadAssets;
use yii\helpers\Html;

FilesUploadAssets::register($this);
$context = $this->context;
?>
<?= Html::beginTag('div', [
    'class' => 'row',
    'ng-controller' => 'UploadCtrl',
    'ng-init' => 'start_init(' . $files . ',' . $options . ',' . $filters . ')',
    'nv-file-drop' => '',
    'uploader' => 'uploader'
]) ?>
<style>
	canvas {
		background-color: #f3f3f3;
		-webkit-box-shadow: 3px 3px 3px 0 #e3e3e3;
		-moz-box-shadow: 3px 3px 3px 0 #e3e3e3;
		box-shadow: 3px 3px 3px 0 #e3e3e3;
		border: 1px solid #c3c3c3;
		height: 80px;
		margin: 6px 0 0 6px;
	}

	.img_files img {
		background-color: #f3f3f3;
		-webkit-box-shadow: 3px 3px 3px 0 #e3e3e3;
		-moz-box-shadow: 3px 3px 3px 0 #e3e3e3;
		box-shadow: 3px 3px 3px 0 #e3e3e3;
		border: 1px solid #c3c3c3;
		height: 80px;
		margin: 6px 0 0 6px;
	}
</style>
<div class="col-md-3 has-error">
	<h3><?= $context->title ?></h3>
	<div ng-show="uploader.isHTML5">
		<div nv-file-drop="" uploader="uploader">
			<div nv-file-over="" uploader="uploader" over-class="another-file-over-class" class="well my-drop-zone">
				Перенесите файл сюда
			</div>
		</div>
	</div>
	<span class="btn btn-primary btn-o btn-file margin-bottom-15"> Выбрать файл
		<input type="file" nv-file-select="" uploader="uploader" multiple /><br />
	</span>
	<p class="help-block help-block-error" ng-show="uploader.errorMessage">{{uploader.errorMessage}}</p>
</div>
<div class="col-md-9" style="margin-bottom: 40px">
	<h3>Загруженные файлы</h3>
	<p>Всего файлов: {{ uploader.queue.length }}</p>
	<table class="table img_files">
		<thead>
		<tr>
			<th width="50%">Название</th>
            <? if ($context->isSort): ?>
				<th>Порядок</th>
            <? endif ?>
			<th ng-show="uploader.isHTML5">Процесс</th>
			<th>Статус</th>
			<th>Действия</th>
		</tr>
		</thead>
		<tbody>
		<tr ng-repeat="item in uploader.queue">
			<td>
                <? if ($context->isName): ?>
					<input type="text" class="form-control"
						   name="<?= $context->name ?>[{{ item.file.id||'new'+uploader.queue.indexOf(item)}}][name]"
						   value="{{ item.file.title }}"
					>
                <? endif ?>
				<div class="clearfix"></div>
				<strong>{{ item.file.name }}</strong>
                <? if ($context->isImg): ?>
					<div ng-show="uploader.isHTML5" ng-thumb="{ file: item._file, height: 100 }"></div>
                <? endif ?>
				<input type="hidden" name="<?= $context->name ?>[{{ item.file.id||'new'+uploader.queue.indexOf(item)}}][url]" value="{{ item.file.url }}" />
			</td>
            <? if ($context->isSort): ?>
				<td>
					<input type="text" class="form-control"
						   name="<?= $context->name ?>[{{ item.file.id||'new'+uploader.queue.indexOf(item)}}][sort]"
						   value="{{ item.file.sort }}"
					>
				</td>
            <? endif ?>
            <? if (false): ?>
				<td ng-show="uploader.isHTML5" nowrap>{{ item.file.size/1024/1024|number:2 }} MB</td>
            <? endif ?>
			<td ng-show="uploader.isHTML5">
				<div class="progress" style="margin-bottom: 0;">
					<div class="progress-bar" role="progressbar" ng-if="!item.isUploaded" ng-style="{ 'width':  item.progress + '%' }"></div>
					<div class="progress-bar" role="progressbar" ng-if="item.isUploaded" ng-style="{ 'width':  '100%' }"></div>
				</div>
			</td>
			<td class="text-center">
				<span ng-show="item.isSuccess"><i class="glyphicon glyphicon-ok"></i></span>
				<span ng-show="item.isCancel"><i class="glyphicon glyphicon-ban-circle"></i></span>
				<span ng-show="item.isError"><i class="glyphicon glyphicon-remove"></i></span>
			</td>
			<td nowrap>
				<button type="button" class="btn btn-success btn-xs" ng-click="item.upload()" ng-disabled="item.isReady || item.isUploading || item.isSuccess">
					<span class="glyphicon glyphicon-upload"></span>
					Загрузить
				</button>
				<button type="button" class="btn btn-warning btn-xs" ng-click="item.cancel()" ng-disabled="!item.isUploading">
					<span class="glyphicon glyphicon-ban-circle"></span>
					Отменить
				</button>
				<button type="button" class="btn btn-danger btn-xs" ng-click="item.remove()">
					<span class="glyphicon glyphicon-trash"></span>
					Удалить
				</button>
			</td>
		</tr>
		</tbody>
	</table>
	<div>
		<div>
			Процесс загрузки:
			<div class="progress" style="">
				<div class="progress-bar" role="progressbar" ng-style="{ 'width': uploader.progress + '%' }"></div>
			</div>
		</div>
		<button type="button" class="btn btn-success btn-s" ng-click="uploader.uploadAll()" ng-disabled="!uploader.getNotUploadedItems().length">
			<span class="glyphicon glyphicon-upload"></span>
			Загрузить все
		</button>
		<button type="button" class="btn btn-warning btn-s" ng-click="uploader.cancelAll()" ng-disabled="!uploader.isUploading">
			<span class="glyphicon glyphicon-ban-circle"></span>
			Отменить все
		</button>
		<button type="button" class="btn btn-danger btn-s" ng-click="uploader.clearQueue()" ng-disabled="!uploader.queue.length">
			<span class="glyphicon glyphicon-trash"></span>
			Удалить все
		</button>
	</div>
</div>
</div>
