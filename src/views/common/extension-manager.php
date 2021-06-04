<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/* @var $extensions Configuration */
/* @var $download array */
/* @var $circular array */
/* @var $conflict array */

/* @var $model \EngineCore\modules\installation\models\ExtensionManageForm */

use EngineCore\enums\AppEnum;
use EngineCore\extension\repository\configuration\Configuration;
use kartik\grid\CheckboxColumn;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;

$this->title = '扩展中心';
?>

<div class="page-header">
    <h1>本地扩展
        <small>请选择需要安装的扩展，无法更改默认需要安装和已经安装的扩展</small>
    </h1>
</div>

<?php
$columns = [
    [
        'class'            => CheckboxColumn::class,
        'name'             => Html::getInputName($model, 'extension'),
        'checkboxOptions'  => function ($model, $key, $index, $column) {
            $options['checked'] = $model['checked'];
            $options['disabled'] = $model['disabled'];
            
            return $options;
        },
        'rowSelectedClass' => GridView::TYPE_SUCCESS,
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
        'label'          => '当前版本',
        'attribute'      => 'version',
        'contentOptions' => [
            'style' => 'text-align:center;',
        ],
    ],
];

foreach (AppEnum::list() as $app => $title) {
    $columns[] = [
        'label'          => $title,
        'format'         => 'raw',
        'contentOptions' => [
            'style' => 'text-align:center;',
        ],
        'value'          => function ($m, $key, $index, $column) use ($app, $model) {
            /** @var Configuration $m */
            $html = '';
            if (in_array($app, $m['app'])) {
                $name = Html::getInputName($model, 'app') . "[{$key}][]";
                $options['value'] = $app;
                $options['checked'] = in_array($app, $m['checkedApp']);
                $options['disabled'] = in_array($app, $m['disabledApp']);
                $html = Html::checkbox($name, false, $options);
            }
            
            return $html;
        },
    ];
}

$dataProvider = new ArrayDataProvider([
    'allModels'  => $extensions,
    'pagination' => [
        'pageSize' => -1, //不使用分页
    ],
]);

$form = \yii\widgets\ActiveForm::begin([
    'id' => 'install-form',
]);

echo GridView::widget([
    'dataProvider'     => $dataProvider,
    'layout'           => "{items}",
    'emptyText'        => '不存在任何扩展',
    'hover'            => true,
    'bordered'         => false,
    'emptyTextOptions' => ['class' => 'text-center text-muted'],
    'columns'          => $columns,
]);

\yii\widgets\ActiveForm::end();
?>

<?php if (!empty($download)): ?>
    <div class="page-header">
        <h1>下载扩展
            <small>以下为需要下载的扩展</small>
        </h1>
    </div>
    
    <?php
    $dataProvider = new ArrayDataProvider([
        'allModels'  => $download,
        'pagination' => [
            'pageSize' => -1, //不使用分页
        ],
    ]);
    
    $columns = [
        [
            'label'     => '名称',
            'attribute' => 'name',
        ],
        [
            'label'     => '请求主体',
            'attribute' => 'parent',
        ],
        [
            'label'     => '版本规则',
            'attribute' => 'version',
        ],
    ];
    
    echo GridView::widget([
        'dataProvider'     => $dataProvider,
        'layout'           => "{items}",
        'emptyText'        => '不存在任何扩展',
        'hover'            => true,
        'bordered'         => false,
        'emptyTextOptions' => ['class' => 'text-center text-muted'],
        'columns'          => $columns,
    ]);
    ?>
<?php endif; ?>

<?php if (!empty($circular)): ?>
    <div class="page-header">
        <h1>无限依赖
            <small>以下扩展存在无限循环依赖关系</small>
        </h1>
    </div>
    
    <?php
    $dataProvider = new ArrayDataProvider([
        'allModels'  => $circular,
        'pagination' => [
            'pageSize' => -1, //不使用分页
        ],
    ]);
    
    $columns = [
        [
            'label'     => '名称',
            'attribute' => 'name',
        ],
        [
            'label'     => '依赖链',
            'attribute' => 'chain',
        ],
    ];
    
    echo GridView::widget([
        'dataProvider'     => $dataProvider,
        'layout'           => "{items}",
        'emptyText'        => '不存在任何扩展',
        'hover'            => true,
        'bordered'         => false,
        'emptyTextOptions' => ['class' => 'text-center text-muted'],
        'columns'          => $columns,
    ]);
    ?>
<?php endif; ?>

<?php if (!empty($conflict)): ?>
    <div class="page-header">
        <h1>版本冲突
            <small>以下扩展存在版本冲突</small>
        </h1>
    </div>
    
    <?php
    $dataProvider = new ArrayDataProvider([
        'allModels'  => $conflict,
        'pagination' => [
            'pageSize' => -1, //不使用分页
        ],
    ]);
    
    $columns = [
        [
            'label'     => '名称',
            'attribute' => 'name',
        ],
        [
            'label'     => '版本规则',
            'attribute' => 'requireVersion',
        ],
        [
            'label'     => '当前版本',
            'attribute' => 'version',
        ],
    ];
    
    echo GridView::widget([
        'dataProvider'     => $dataProvider,
        'layout'           => "{items}",
        'emptyText'        => '不存在任何扩展',
        'hover'            => true,
        'bordered'         => false,
        'emptyTextOptions' => ['class' => 'text-center text-muted'],
        'columns'          => $columns,
    ]);
    ?>
<?php endif; ?>
