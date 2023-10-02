<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backendold\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use \yii\widgets\Menu;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use \backendold\controllers\MainController as d;

AppAsset::register($this);

//$type_user = Yii::$app->user->role;

$dropdownMenuItems_webmaster = [
        ['label' => 'Time трекер', 'url' => ['time-trecker'], 'active' =>  d::active('time-trecker')],
        ['label' => 'Проекты', 'url' => ['projects'], 'active' =>  d::active('projects')],
        ['label' => 'Статистика', 'url' => ['statistics'], 'active' =>  d::active('statistics')],
        ['label' => 'Проекты/задачи', 'url' => ['projects-list'], 'active' =>  d::active('projects-list')],
        ['label' => 'Отчёты', 'url' => ['reports'], 'active' =>  d::active('reports')],
        ['label' => 'Настройки', 'url' => ['settings-webmaster'], 'active' =>  d::active('settings-webmaster')],
        ['label' => 'Список задач', 'url' => ['task-list'], 'active' =>  d::active('task-list')],
        ['label' => 'Расчёты', 'url' => ['calculations'], 'active' =>  d::active('calculations')],
        ['label' => 'Тест', 'url' => ['test'], 'active' =>  d::active('test')],
        ['label' => 'Debug', 'url' => ['debug'], 'active' =>  d::active('debug')],
//    ['label' => 'Email', 'url' => ['email'], 'active' =>  d::active('email')],
];

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    // Html::csrfMetaTags() - для безопасности форм
    echo Html::csrfMetaTags()
    ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php // d::pri(Yii::$app->request); ?>
<?php // d::pre(Yii::$app->request->headers['host']); ?>
<div class="wrap
    <?=(Yii::$app->request->url == '/admin/login')?'auth-body':''?>
    <?=(Yii::$app->request->url == '/admin/' || Yii::$app->request->url == '/admin')?'main-body':''?>
">

    <nav class="navbar-inverse navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-header">
                <button class="navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".js-navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/" style="padding:15px 15px;">Финансовый учет</a>
            </div>
            <div class="collapse navbar-collapse js-navbar">

                <?
                if (!Yii::$app->user->isGuest) {
//                    d::pre('Гость');
                    $menu = [
                        ['label' => 'Войти', 'url' => ['/site/login']]
                    ];
                }else {
                    $menu = [
//                        ['label' => 'Тест ККМ', 'url' => ['/kkm']],
//                        ['label' => 'Gii', 'url' => ['/gii']],
                        ['label' => 'На главную', 'url' => ['/']],
                        [
                            'label' => 'Gii',
                            'url' => ['gii'],
                            'active' =>  d::active('gii'),
                            'template' => '
                            <a href="{url}"
                               target="_blank"
                            >{label}</a>',
                        ],
                        [
                            'label' => 'WebMaster',
                            'url' => ['#'],
                            'items' => $dropdownMenuItems_webmaster,
                            'options' => [
                                'id' => 'menu',
                                'class' => 'dropdown',
                                'data-id' => 'menu',
                            ],
                            'template' => '
                            <a  
                                id="drop1" 
                                href="{url}" 
                                class="dropdown-toggle" 
                                data-toggle="dropdown"
                            >{label}</a>',
                        ],
                        [
                            'label' => 'Выйти (' . '0' . ')',
                            'url' => ['site/logout'],
                            'options' => [
                                'class' => 'logout',
                            ],
                            'template' =>
                                Html::beginForm(['/site/logout'], 'post')
                                . Html::submitButton(
                                    'Выйти (' . ((isset(Yii::$app->user->identity->username)) ? Yii::$app->user->identity->username : '') . ')',
                                    ['class' => 'btn btn-link logout']
                                )
                                . Html::endForm()
                        ],
                    ];
                }

                echo Menu::widget([
                    'items' => $menu,
                    'activeCssClass'=>'active',
                    'firstItemCssClass'=>'fist-item2',
                    'lastItemCssClass' =>'last-item3',
                    'options' => [
                        'id'=>'menu',
                        'class' => 'menu nav navbar-nav navbar-right',
                        'data-id'=>'menu',
                    ],
//                    'itemOptions'=>['class'=>'myclass', 'style'=>'background: #444;'],
                    'submenuTemplate' => "\n<ul class='dropdown-menu' role='menu'>\n{items}\n</ul>\n",
                ]); ?>

            </div>
        </div>
    </nav>


    <div class="container">

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?/*
        <a href="<?=Url::home()?>">
            <?=Html::img('@web/images/design.png',['alt'=>'На главную','width'=>'100'])?>
        </a>
        */?>
        <?= $content ?>
    </div>
</div>

<footer class="footer <?=(Yii::$app->request->url == '/admin/login')?'auth-footer':''?>">
    <div class="container">
        <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>

<?php

/*

use backendold\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => 'Home', 'url' => ['/site/index']],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() */ ?>
