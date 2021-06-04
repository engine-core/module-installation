<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/* @var $extensions array */

use EngineCore\Ec;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

$this->title = '扩展详情';
?>

<div class="page-header">
    <h1>准备安装
        <small>以下扩展即将被安装，已包括被依赖的扩展</small>
    </h1>
</div>

<?php if (empty($extensions)) : ?>
    <p class="lead">已选扩展已全部被安装，暂无新的可被安装的扩展。</p>
<?php else: ?>
    <?php foreach ($extensions as $uniqueName => $row): ?>
        <?php
        $target = md5($uniqueName);
        ?>
        <div class="panel panel-info">
            <div class="panel-heading" data-toggle="collapse" data-target="#<?= $target; ?>" aria-expanded="false"
                 aria-controls="<?= $target; ?>">
                <h3 class="panel-title">
                    <?= $uniqueName; ?>
                    <span class="pull-right"><?= $row['version']; ?></span>
                </h3>
            </div>
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
                                                    'label'     => '当前版本',
                                                    'attribute' => 'localVersion',
                                                ],
                                                [
                                                    'label'     => '版本规则',
                                                    'attribute' => 'requireVersion',
                                                ],
                                                [
                                                    'label'     => '应用环境',
                                                    'attribute' => 'requireApp',
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
                                                                . '不符合所需的版本规则要求 ' . $model['requireVersion'])
                                                            . '。在解决冲突前，扩展功能可能存在不兼容或无法使用的情况。',
                                                        'data-html'   => 'true',
                                                    ]);
                                                }
                                                
                                                return $model['localVersion'];
                                            },
                                        ],
                                        [
                                            'label'     => '版本规则',
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
        </div>
    
    <?php endforeach; ?>
<?php endif; ?>
