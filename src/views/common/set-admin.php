<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

$this->title = '管理员设置';
$form = \yii\widgets\ActiveForm::begin([
    'id' => 'admin-form',
    'enableAjaxValidation' => false,
    "options" => [
        "class" => "install-form"
    ]
]);
?>
<?=$form->field($model, 'email')->textInput(['class' => 'form-control'])?>
<?=$form->field($model, 'username')->textInput(['class' => 'form-control'])?>
<?=$form->field($model, 'password')->passwordInput(['class' => 'form-control'])?>
<?=$form->field($model, 'passwordConfirm')->passwordInput(['class' => 'form-control'])?>

<?php \yii\widgets\ActiveForm::end(); ?>