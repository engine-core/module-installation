<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/* @var $dependList array */

use EngineCore\Ec;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

$this->title = '扩展安装';
?>

<?php foreach ($dependList as $uniqueName => $row): ?>
    <?php $target = md5($uniqueName); ?>
    <div class="panel panel-default">
        <div class="panel-heading" data-toggle="collapse" data-target="#<?= $target; ?>" aria-expanded="false"
             aria-controls="<?= $target; ?>">
            <h3 class="panel-title">
                <?= $uniqueName; ?> 扩展依赖
            </h3>
        </div>
        <div class="collapse in" id="<?= $target; ?>">
            <div class="panel-body">
                <?php
                $dataProvider = new ArrayDataProvider([
                    'allModels'  => $row['dependencies'],
                    'pagination' => [
                        'pageSize' => -1, //不使用分页
                    ],
                ]);
                echo GridView::widget([
                    'dataProvider'     => $dataProvider,
                    'layout'           => "{items}",
                    'emptyText'        => '不存在任何依赖关系',
                    'hover'            => true,
                    'bordered'         => false,
                    'emptyTextOptions' => ['class' => 'text-center text-muted'],
                    'rowOptions'       => function ($model, $key, $index, $grid) {
                        $options = [];
                        if (
                            ($model['installed'] || $model['downloaded'])
                            && !Ec::$service->getSystem()->getVersion()->compare($model['localVersion'], $model['requireVersion'])
                        ) {
                            $options = ['class' => 'warning'];
                        }
                        
                        return $options;
                    },
                    'columns'          => [
                        [
                            'label' => '扩展名称',
                            'value' => function ($model, $key) {
                                return $key;
                            },
                        ],
                        [
                            'label'     => '名称',
                            'attribute' => 'name',
                        ],
                        [
                            'label'     => '描述',
                            'attribute' => 'description',
                        ],
                        [
                            'label'  => '当前版本',
                            'format' => 'raw',
                            'value'  => function ($model) {
                                if (
                                    ($model['installed'] || $model['downloaded'])
                                    && !Ec::$service->getSystem()->getVersion()->compare($model['localVersion'], $model['requireVersion'])
                                ) {
                                    return Html::tag('div', Html::tag('span',
                                        $model['localVersion'] . ' 版本冲突',
                                        ['class' => 'text-danger']), [
                                        'data-toggle' => 'tooltip',
                                        'title'       => nl2br('当前版本' . $model['localVersion']
                                                . '不符合所需的依赖版本要求 ' . $model['requireVersion'])
                                            . '。在解决冲突前，扩展功能可能存在不兼容或无法使用的情况。',
                                        'data-html'   => 'true',
                                    ]);
                                }
                                
                                return $model['localVersion'];
                            },
                        ],
                        [
                            'label' => '依赖版本',
                            'value' => function ($model) {
                                return $model['requireVersion'];
                            },
                        ],
                        [
                            'class'     => 'kartik\grid\BooleanColumn',
                            'attribute' => 'downloaded',
                            'label'     => '已下载',
                        ],
                        [
                            'class'     => 'kartik\grid\BooleanColumn',
                            'attribute' => 'installed',
                            'label'     => '已安装',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
    
    <?php
endforeach;
?>