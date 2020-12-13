<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/* @var $extensions array */

use EngineCore\Ec;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

$this->title = '扩展安装';
$definitions = Ec::$service->getExtension()->getDependent()->getDefinitions();
?>

    <div class="page-header">
        <h1>默认安装以下扩展
            <small>向导将自动安装扩展所需的数据库迁移文件、系统配置、菜单配置等</small>
        </h1>
    </div>

<?php foreach ($extensions as $uniqueName => $row): ?>
    <?php
    $target = md5($uniqueName);
    $open = false;
    if (isset($definitions[$uniqueName])) {
        $open = true;
    }
    ?>
    <div class="panel panel-<?= $open ? 'info' : 'danger'; ?>">
        <div class="panel-heading" data-toggle="collapse" data-target="#<?= $target; ?>" aria-expanded="false"
             aria-controls="<?= $target; ?>">
            <h3 class="panel-title">
                <?= $uniqueName . ' : ' . $row['version']; ?>
                <span class="pull-right"><?= $open ? '' : ' 未下载' ?></span>
            </h3>
        </div>
        <?php if ($open): ?>
            <div class="collapse in" id="<?= $target; ?>">
                <div class="panel-body">
                    <p><?= $row['description']; ?></p>
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <?php
                            echo Html::tag('li', Html::a('扩展依赖', "#{$target}-extensionDependencies", ['data-toggle' => 'tab']), [
                                'class' => 'active',
                            ]);
                            echo Html::tag('li', Html::a('composer依赖', "#{$target}-composerDependencies", ['data-toggle' => 'tab']));
                            ?>
                        </ul>
                        <!-- extension -->
                        <div class="tab-content">
                            <div class="active tab-pane" id="<?= $target; ?>-extensionDependencies">
                                <?php
                                if (empty($row['extensionDependencies'])) {
                                    $dataProvider = new ArrayDataProvider([
                                        'allModels' => [],
                                    ]);
                                    echo GridView::widget([
                                        'dataProvider'     => $dataProvider,
                                        'layout'           => "{items}",
                                        'emptyText'        => '不存在任何依赖关系',
                                        'hover'            => true,
                                        'bordered'         => false,
                                        'emptyTextOptions' => ['class' => 'text-center text-muted'],
                                    ]);
                                } else {
                                    foreach ($row['extensionDependencies'] as $app => $v) {
                                        $html = <<<HTML
<div style="margin-top:10px">
    <div class="panel panel-default">
      <div class="panel-heading">%s</div>
      <div class="panel-body">
        %s
      </div>
    </div>
</div>
HTML;
                                        $dataProvider = new ArrayDataProvider([
                                            'allModels'  => $v,
                                            'pagination' => [
                                                'pageSize' => -1, //不使用分页
                                            ],
                                        ]);
                                        $content = GridView::widget([
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
                                                            return Html::tag('div', Html::tag('span', '版本冲突',
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
                                                    'label'     => '依赖版本',
                                                    'attribute' => 'requireVersion',
                                                ],
                                                [
                                                    'label'     => '应用环境',
                                                    'attribute' => 'requireApp',
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
                                        ]);
                                        echo sprintf($html, '在 ' . $app . ' 应用安装时需要依赖以下扩展', $content);
                                    }
                                }
                                ?>
                            </div>
                            <!-- /.tab-pane -->
                            <!-- composer -->
                            <div class="tab-pane" id="<?= $target; ?>-composerDependencies">
                                <?php
                                $dataProvider = new ArrayDataProvider([
                                    'allModels'  => $row['composerDependencies'],
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
                                        if ($model['installed']
                                            && !Ec::$service->getSystem()->getVersion()->compare($model['localVersion'], $model['requireVersion'])
                                        ) {
                                            $options = ['class' => 'warning'];
                                        }
                                        
                                        return $options;
                                    },
                                    'columns'          => [
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
                                                if ($model['installed']
                                                    && !Ec::$service->getSystem()->getVersion()->compare($model['localVersion'], $model['requireVersion'])) {
                                                    return Html::tag('div', Html::tag('span',
                                                        '版本冲突',
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
                                            'label'     => '依赖版本',
                                            'attribute' => 'requireVersion',
                                        ],
                                        [
                                            'class'     => 'kartik\grid\BooleanColumn',
                                            'attribute' => 'installed',
                                            'label'     => '已安装',
                                        ],
                                    ],
                                ]); ?>
                            </div>
                            <!-- /.tab-pane -->
                        </div>
                        <!-- /.tab-content -->
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
endforeach;
?>