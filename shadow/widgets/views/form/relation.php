<?php
/**
 *
 * @var array $form_action
 * @var \yii\db\ActiveRecord[] | \yii\base\Model[] $items
 * @var \yii\web\View $this
 * @var $context \shadow\widgets\AdminForm
 * @var array $attributes
 * @var string $name
 * @var \yii\db\ActiveRecord | \yii\base\Model $model
 */
use yii\helpers\Html;

$context = $this->context;
$id_uni = hash('crc32', $name);

?>
    <div class="col-md-<?= isset($width) ? $width : 5 ?>">
        <table class="table table-primary table-striped table-hover">
            <colgroup>
                <col width="10px">
            </colgroup>
            <thead>
            <tr>
                <?php if ((isset($add) && $add != false) || !isset($add)): ?>
					<th>Действия</th>
                <?php endif; ?>
                <?php foreach ($attributes as $key => $value): ?>
                    <?php if(is_array($value)): ?>
                        <?php if(isset($value['label'])): ?>
                            <th><?= $value['label'] ?></th>
                        <?php else: ?>
                            <th><?= $model->getAttributeLabel( $key ) ?></th>
                        <?php endif; ?>
                    <?php else: ?>
                        <th><?= $model->getAttributeLabel( $value ) ?></th>
                    <?php endif; ?>
                <?php endforeach; ?>

            </tr>
            </thead>
            <tbody id="items-<?= $id_uni ?>">
            <?php if ((isset($add) && $add != false) || !isset($add)): ?>
				<tr class="item">
					<td class="actions text-center add-<?= $id_uni ?>">
						<a href="#" class="btn btn-xs btn-primary" title="Добавить"><i class="fa fa-plus-circle fa-inverse"></i></a>
					</td>
					<td class="actions text-center hidden deleted-<?= $id_uni ?>">
						<a href="#" class="btn btn-xs btn-danger " title="Удалить"><i class="fa fa-times fa-inverse"></i></a>
					</td>
                    <?php foreach ($attributes as $key=>$value): ?>
                        <?php if (!is_array($value)): ?>
                            <? if ($model->hasAttribute($value)||($model->getBehavior('ml')&&$model->hasLangAttribute($value))): ?>
								<td class="name">
                                    <?= $context->getRelationField(null, $name, $value) ?>
								</td>
                            <? endif; ?>
                        <?php else: ?>
							<td class="name">
                                <?= $context->getRelationField(null, $name, $key, $value) ?>
							</td>
                        <?php endif; ?>
                    <?php endforeach; ?>
				</tr>
				<tr class="item hidden clone_<?= $id_uni ?>">
					<td class="actions text-center add-<?= $id_uni ?>">
						<a href="#" class="btn btn-xs btn-primary" title="Добавить"><i class="fa fa-plus-circle fa-inverse"></i></a>
					</td>
					<td class="actions text-center hidden deleted-<?= $id_uni ?>">
						<a href="#" class="btn btn-xs btn-danger" title="Удалить"><i class="fa fa-times fa-inverse"></i></a>
					</td>
                    <?php foreach ($attributes as $key=>$value): ?>
                        <?php if (!is_array($value)): ?>
                            <? if ($model->hasAttribute($value)||($model->getBehavior('ml')&&$model->hasLangAttribute($value))): ?>
								<td class="name">
                                    <?= $context->getRelationField(null, $name, $value, [], true) ?>
								</td>
                            <? endif ?>
                        <?php else: ?>
							<td class="name">
                                <?= $context->getRelationField(null, $name, $key, $value, true) ?>
							</td>
                        <?php endif; ?>
                    <?php endforeach; ?>

				</tr>
            <?php endif; ?>
            <?php foreach ($items as $item): ?>
                <tr class="item">
                    <?php if ((isset($add) && $add != false) || !isset($add)): ?>
						<td class="actions text-center deleted-<?= $id_uni ?>">
							<a href="#" class="btn btn-xs btn-danger" title="Удалить"><i class="fa fa-times fa-inverse"></i></a>
						</td>
                    <?php endif; ?>
                    <?php foreach ($attributes as $key => $value): ?>
                        <?php if (!is_array($value)): ?>
                            <? if ($model->hasAttribute($value)||($model->getBehavior('ml')&&$model->hasLangAttribute($value))): ?>
                                <td class="name">
                                    <?= $context->getRelationField($item, $name, $value) ?>
                                </td>
                            <? endif; ?>
                        <?php else: ?>
                            <td class="name">
                                <?= $context->getRelationField($item, $name, $key, $value) ?>
                            </td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
<?php
if ((isset($add) && $add != false) || !isset($add)) {
	if(isset($type)&&$type=='MANY_MANY'){
        $name = $name . '[{new_index}]';
	}else{
        $name = $name . '[{new_index}][{field}]';
	}
    $this->registerJs(<<<JS
var name_{$id_uni} = '{$name}';
var index_{$id_uni} = 0;
$('#items-{$id_uni}').on('click', '.deleted-{$id_uni}>a', function (e) {
    e.preventDefault();
    $(this).parents('tr').remove();
}).on('click', '.add-{$id_uni}>a', function (e) {
    e.preventDefault();
    var tr_parent = $(this).parents('tr');
    var clone = $('.clone_{$id_uni}').clone();
    index_{$id_uni} = index_{$id_uni} + 1;
    $('[name]', tr_parent).each(function (i, input) {

        var name_input = name_{$id_uni};
        var field = $(input).data('field');
        if ($(input).is('[type="hidden"]')&&$(input).next().is('input[type="checkbox"]')){
            field = $(input).next().data('field');
        }
        $(input).attr('name', name_input.replace('{new_index}', 'new' + index_{$id_uni}).replace('{field}', field).replace('{value}', $(input).val()));
    });
    $('.deleted-{$id_uni}', tr_parent).removeClass('hidden');
    $('.add-{$id_uni}', tr_parent).remove();

    clone.removeClass('hidden').removeClass('clone_{$id_uni}');
    $(tr_parent).before(clone);
    if (!(select2_remote_class===undefined)&&!(select2_remote_class["{$id_uni}"]===undefined)){
      $('.select2', clone).remove();
      init_remote_select_{$id_uni}($(select2_remote_class["{$id_uni}"]+':not(disabled)',clone));
    }
    
});
JS
    );
}
?>