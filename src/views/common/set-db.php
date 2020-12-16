<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

$this->title = '数据库设置';
$form = \yii\widgets\ActiveForm::begin([
    'id'      => 'install-form',
]);
?>

<?= $form->field($model, 'scheme')->dropDownList(\ekevin\dsn\Dsn::getTypeList()) ?>
<?= $form->field($model, 'username')->textInput() ?>
<?= $form->field($model, 'password')->passwordInput() ?>
<?= $form->field($model, 'database')->textInput() ?>

    <p class="text-right">
        <a href="#advanced" role="button" data-toggle="collapse"
           aria-expanded="false" aria-controls="advanced"
           style="text-decoration: underline">高级设置</a>
    </p>
    <div id="advanced" class="collapse">
        <?= $form->field($model, 'hostname')->textInput() ?>
        <?= $form->field($model, 'port')->textInput() ?>
        <?= $form->field($model, 'tablePrefix')->textInput() ?>
    </div>

<?php \yii\widgets\ActiveForm::end(); ?>