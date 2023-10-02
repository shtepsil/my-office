<?php
use yii\helpers\Html;
?>
<!-- Large modal -->
<button type="button" class="btn btn-primary dn" data-toggle="modal" data-target=".<?=(isset($modal_class))?$modal_class:'bd-example-modal-lg'?>">Large modal</button>

<div class="modal fade <?=(isset($modal_class))?$modal_class:'bd-example-modal-lg'?>" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><?=(isset($header))?$header:'Заголовок'?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"><?=(isset($body))?$body:'Тело окна'?></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=(isset($btn_close))?$btn_close:'Close'?></button>
                <button
                    type="button"
                    name="<?=(isset($name_btn_success)) ? $name_btn_success : ''?>"
                    class="btn btn-primary"
                    data-url="<?=(isset($data_url)) ? $data_url : ''?>"
                    data-type-method="<?=(isset($method)) ? $method : ''?>"
                >
                    <?=(isset($btn_success))?$btn_success:'Success'?>
                </button>
                <?=Html::img('@web/images/animate/loading.gif',['alt'=>'Загрузка','width'=>'20','class'=>'loading'])?>
            </div>
<?'<div class="">result</div>'?>
        </div>
    </div>
</div>