<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

/**@var array $operations */

use yii\helpers\Html;

$this->title = '安装完成';
?>

    <p class="lead">安装向导结束后将执行以下操作：</p>

<?= Html::ul($operations, [
    'item' => function ($item, $index) {
        return Html::tag('li', ++$index . '、' . Html::encode($item));
    },
]); ?>