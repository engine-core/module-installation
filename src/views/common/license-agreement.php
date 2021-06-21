<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

/**@var string $license */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$this->title = '许可协议';
?>
    <h2>License agree</h2>

<?php $form = ActiveForm::begin([
    'options' => [
        'id' => 'install-form',
    ],
]); ?>

<?php echo $license; ?>

    <div class="checkbox">
        <?= Html::checkbox(
            'license',
            $this->context->isFinishedStep($this->context->action->id),
            [
                'label' => '<strong>同意并签署安装协议</strong>',
                'disabled' => $this->context->isFinishedStep($this->context->action->id),
            ]
        ); ?>
    </div>

<?php $form::end(); ?>