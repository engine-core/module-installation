<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/* @var $this \yii\web\View */

/* @var $content string */

use EngineCore\widgets\FlashAlert;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Menu;

\EngineCore\modules\installation\InstallAsset::register($this);
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
        'content'    => 'IE=edge',
    ]);
    $this->registerMetaTag([
        'name'    => 'viewport',
        'content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no',
    ]);
    echo Html::csrfMetaTags();
    echo Html::tag('title', Html::encode($this->title));
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
css;
    $this->registerCss($css, ['type' => 'text/css']);
    $this->head();
    
    $items = [];
    foreach ($this->context->getSteps() as $k => $v) {
        $items[] = [
            'label'   => $v['label'],
            'url'     => $v['url'],
            'options' => [
                'data-url' => Url::to($v['url']),
            ],
        ];
    }
    $menu = [
        'options'      => [
            'class' => 'list-group',
        ],
        'itemOptions'  => [
            'class' => 'list-group-item',
        ],
        'linkTemplate' => '{label}',
        'items'        => $items,
    ];
    ?>
</head>
<body>

<div class="wrap">
    <?php $this->beginBody() ?>
    
    <?= \yii\bootstrap\Modal::widget([
        'id'     => 'msgBox',
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
            </div>
            <div class="panel-footer clearfix">
                <h3 class="panel-title pull-left"><?= Yii::$app->name ?></h3>
                <div class="pull-right">
                    EngineCore Version <strong><?= \EngineCore\Ec::getVersion(); ?></strong>
                </div>
            </div>
        </div>
        
        <?php $this->endBody() ?>
    </div>
</body>
</html>
<?php $this->endPage() ?>
