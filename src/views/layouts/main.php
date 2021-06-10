<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

/* @var $this \yii\web\View */

/* @var $content string */

use EngineCore\Ec;
use EngineCore\widgets\FlashAlert;
use EngineCore\widgets\Issue;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Menu;

\EngineCore\modules\installation\InstallationAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?php
    $this->registerMetaTag([
        'charset' => Yii::$app->charset,
    ]);
    $this->registerMetaTag([
        'http-equiv' => 'X-UA-Compatible',
        'content' => 'IE=edge',
    ]);
    $this->registerMetaTag([
        'name' => 'viewport',
        'content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no',
    ]);
    $this->registerMetaTag([
        'name' => 'description',
        'content' => Html::encode(Ec::$service->getSystem()->getSetting()->get('SITE_DESCRIPTION')),
    ], 'description');
    $this->registerMetaTag([
        'name' => 'keywords',
        'content' => Html::encode(Ec::$service->getSystem()->getSetting()->get('SITE_KEYWORD')),
    ], 'keywords');
    echo Html::csrfMetaTags();
    $title = Ec::$service->getSystem()->getSetting()->get('SITE_TITLE');
    $title = $title ? $title . ' - ' . $this->title : $this->title;
    echo Html::tag('title', Html::encode($title));
    $css = <<<css
        .list-group-item {
            border: 0;
            text-align: center;
        }
        .list-group-item:first-child {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        .list-group-item:last-child {
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }
        .list-group-item.active > a {
            color:#ffffff;
        }
        .list-group-item > a {
            color:#333333;
        }
        .list-group-item:hover, .list-group-item:hover > a,
        .list-group-item.active, .list-group-item.active:hover, .list-group-item.active:focus {
            z-index: 2;
            color: #fff;
            background-color: #337ab7;
            border-color: #337ab7;
        }
css;
    $this->registerCss($css, ['type' => 'text/css']);
    $this->head();

    $items = [];
    foreach ($this->context->getStepFlow() as $k => $v) {
        $items[] = [
            'label' => $v['label'],
            'url' => $v['url'],
            'options' => [
                'data-url' => Url::to($v['url']),
            ],
        ];
    }
    $menu = [
        "options" => [
            "class" => "list-group",
        ],
        "itemOptions" => [
            "class" => "list-group-item",
        ],
//        "linkTemplate" => '{label}',
        "items" => $items,
    ];
    ?>
</head>
<body>

<div class="wrap">
    <?php $this->beginBody() ?>

    <?= \yii\bootstrap\Modal::widget([
        'id' => 'msgBox',
        'header' => '<span class="modal-title">消息提示</span>',
    ]); ?>

    <div class="page-header text-center">
        <h1>欢迎使用<?= Yii::$app->name; ?>系统</h1>
    </div>

    <div class="container-fluid">
        <div class="panel">
            <div class="panel-body ">
                <div class="row">
                    <div class="col-md-2">
                        <?= Menu::widget($menu); ?>
                    </div>
                    <div class="col-md-10">
                        <?= FlashAlert::widget() ?>
                        <?= $content; ?>
                        <div class="row text-center">
                            <span>
                                <a href="javascript:void(0)" class="btn btn-small btn-default" id="prevButton">
                                    <i class="fa fa-arrow-circle-left"></i> 上一步
                                </a>
                            </span>
                            <span>
                                <a href="javascript:void(0)" class="btn btn-small  btn-success" id="nextButton">
                                    下一歩 <i class="fa fa-arrow-circle-right"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
                <?= Issue::widget() ?>
            </div>
            <div class="panel-footer clearfix text-muted">
                <div class="col-md-4">
                    <h3 class="panel-title pull-left"><?= Yii::$app->name ?></h3>
                </div>
                <div class="col-md-4">
                    <div class="text-center">越被嘲笑的理想，就越有被实现的价值</div>
                </div>
                <div class="col-md-4">
                    <div class="pull-right">
                        EngineCore Version <strong><?= \EngineCore\Ec::getVersion(); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->endBody() ?>
    </div>
</body>
</html>
<?php $this->endPage() ?>
