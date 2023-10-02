<?php

use backend\controllers\MainController as d;
use yii\web\View;

$context = $this->context;

$tabs = require __DIR__ . '/tabs.php';

?>
<style>
    .wrap-debug {
        min-height: 400px;
        /*font-size: 16px;*/
    }

    .wrap-debug .dn {
        display: none;
    }

    .wrap-debug .tabs {
        display: flex;
        flex-direction: column;
    }

    .wrap-debug .tabs__links {
        display: flex;
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 10px;
        order: 0;
        white-space: nowrap;
        background-color: #fff;
        border: 1px solid #e3f2fd;
        box-shadow: 0 2px 4px 0 #e3f2fd;
    }

    .wrap-debug .tabs__links>a {
        display: inline-block;
        text-decoration: none;
        padding: 6px 10px;
        text-align: center;
        color: #1976d2;
    }

    .wrap-debug .tabs__links>a:hover {
        background-color: rgba(227, 242, 253, 0.3);
    }

    /* отобразить контент, связанный с вабранной радиокнопкой (input type="radio") */
    <?
    $selectors = '';
    foreach ($tabs as $tab_index => $tab) {
        $selectors .= '.wrap-debug .tabs>#content-' . $tab_index . ':target~.tabs__links>a[href="#content-' . $tab_index . '"],';
    }
    $selectors = substr($selectors, 0, -1);
    ?>
    <?= $selectors ?>{

                background-color: #bbdefb;
        cursor: default;
    }

    /*.wrap-debug .tabs>#content-1:target~.tabs__links>a[href="#content-1"],*/
    /*.wrap-debug .tabs>#content-2:target~.tabs__links>a[href="#content-2"],*/
    /*.wrap-debug .tabs>#content-3:target~.tabs__links>a[href="#content-3"] {*/
    /*background-color: #bbdefb;*/
    /*cursor: default;*/
    /*}*/

    .wrap-debug .tabs>div:not(.tabs__links) {
        display: none;
        order: 1;
    }

    .wrap-debug .tabs>div:target {
        display: block;
    }

    .wrap-debug img.loading {
        display: none;
        width: 22px;
        position: absolute;
        top: -77px;
    }

    .wrap-debug button,
    [class=*btn] {
        border: 0;
        cursor: pointer;
    }

    .wrap-debug .btn_debug {
        display: inline;
        font: 1.4rem/1.43em "Proxima Nova", sans-serif;
        padding: 8px 22px;
        overflow: hidden;
        -webkit-border-radius: 20px;
        -moz-border-radius: 20px;
        -o-border-radius: 20px;
        -ms-border-radius: 20px;
        -khtml-border-radius: 20px;
        border-radius: 20px;
    }

    .wrap-debug .btn_debug.blue {
        background-color: #4686cc;
        color: #fff;
    }

    .wrap-debug .btn_debug.error {
        background-color: #CC4646;
        color: #fff;
    }

    .wrap-debug input,
    .wrap-debug textarea {
        width: 100%;
        height: 40px;
        padding-left: 12px;
        padding-right: 12px;
        font: .8em/1.5em "Proxima Nova", sans-serif;
        color: #343332;
        border: 1px solid #d8d7d7;
        overflow: hidden;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        -o-border-radius: 3px;
        -ms-border-radius: 3px;
        -khtml-border-radius: 3px;
        border-radius: 3px;
        margin-bottom: 10px;
    }

    .wrap-debug .w120 {
        width: 120px;
    }

    .wrap-debug .w150 {
        width: 150px;
    }

    .wrap-debug .w250 {
        width: 250px;
    }

    .wrap-debug .w350 {
        width: 350px;
    }

    .wrap-debug .h90 {
        height: 90px;
    }
</style>
<div class="TextContent padSpace wrap-debug">
    <h1 class="title">Debug</h1>
    <div class="textInterface">
        <div class="tabs">
            <? foreach ($tabs as $tab_index => $tab): ?>
                <div id="content-<?= $tab_index ?>">
                    <?= $this->renderAjax('//blocks/debug/' . $tab['path'], [
                        'tab_index' => $tab_index,
                        'context' => $context
                    ]) ?>
                </div>
            <? endforeach ?>
            <div class="tabs__links">
                <? foreach ($tabs as $tab_index => $tab): ?>
                    <a href="#content-<?= $tab_index ?>"><?= $tab_index . ' ' . $tab['name'] ?></a>
                <? endforeach ?>
            </div>

        </div>
    </div>

</div>
<?
$this->registerJs(<<<JS
//JS
function tabsAjax(tab, Data, stop){
    $('.tab' + tab + '-buttons [class*=btn]').on('click',function(){
        var tthis = $(this),
        res = $('.res-tab' + tab),
        wrap = $('.wrap-debug'),
        load = wrap.find('.tab' + tab + '-buttons img.loading'),
        form_elements = tthis.parent().find('input, textarea'),
        textarea = tthis.parent().find('textarea'),
        name = tthis.attr('name'),
        url = 'tab-debug-ajax',
        action = 'debug',
        method = 'post';

        if(Data === undefined){ Data = {}; }

        // Если action передан со страницы
        if(Data['action'] !== undefined){
            action = Data['action'];
        }

        // Если action передан с кнопки
        if(tthis.attr('data-url') !== undefined){
            action = tthis.attr('data-action');
            // method = 'get';
            // Просто для показа в консоли
            Data['method'] = method;
        }

        Data['type'] = tthis.attr('name');

        if(form_elements.length > 0){
            if(form_elements.length > 1){
                Data['inputs'] = form_elements.serializeArray();
            }else{
                switch(form_elements.attr('type')){
                    case 'checkbox':
                        Data[form_elements.attr('name')] = form_elements.prop('checked');
                        break;
                    case 'text':
                        Data[form_elements.attr('name')] = form_elements.val();
                        break;
                    default:
                        Data[form_elements.attr('name')] = form_elements.val();

                }
            }
        }

        res.html('result' + tab);

        if(url === undefined || url === ''){
            $.growl.warning({title: 'Ошибка', message: 'Не передан url', duration: 5000});
            return;
        }

        /*
		$.growl.error({title: 'Ошибка', message: 'Всем привет я error', duration: 5000});
		$.growl.notice({title: 'Ошибка', message: 'Всем привет я notice', duration: 5000});
		$.growl.warning({title: 'Ошибка', message: 'Всем привет я warning', duration: 5000});
		*/

        // var csrf_param = $('meta[name=csrf-param]').attr('content');
        // var csrf_token = $('meta[name=csrf-token]').attr('content');
        // Data[csrf_param] = csrf_token;

        url = '/' + url + '?a=' + action;
        Data['request'] = {
            url: '/admin' + url,
            method: method,
            a: action
        };
        cl(Data);
        if(stop !== undefined) return;
        
        $.ajax({
            url: Data.request.url,
            method: Data.request.method,
            dataType: 'json',
            data: Data,
            success: function(data){
                cl(data);
                if(data.response.data !== undefined){
                    res.html(data.response.data);
                }else{
                    res.html('Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
                }
            },
            error: function(data){
                res.html('Fail<br>' + JSON.stringify(data));
                // res.html('Fail<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
            }
        });
        return;

        $.ajax({
            url: url,
            method: 'post',
            dataType: 'json',
            cache: false,
            data: Data,
            beforeSend: function(){ load.fadeIn(100); }
        }).done(function(data){
            res.html('Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
        }).fail(function(data){
            res.html('Fail<br>' + JSON.stringify(data));
            // res.html('Fail<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
        }).always(function(){
            load.fadeOut(100);
        });
    });
}

JS
    , View::POS_BEGIN
)
    ?>