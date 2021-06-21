<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

/* @var \EngineCore\modules\installation\models\SetSiteForm $model */

$this->title = '网站设置';
$form = \yii\widgets\ActiveForm::begin([
    'id' => 'install-form',
]);
?>

<?= $form->field($model, 'title')->textInput([
    'maxlength' => 10,
    'placeholder' => Yii::$app->name,
]) ?>
<?= $form->field($model, 'description')->textarea([
    'rows' => 4,
    'maxlength' => 128,
    'placeholder' => '这是一个专注于为各类网站建设提供一个高效便捷的应用框架。',
]) ?>
<?= $form->field($model, 'keyword')->textarea([
    'rows' => 4,
    'maxlength' => 128,
    'placeholder' => 'EngineCore',
]) ?>
<?= $form->field($model, 'icp')->textInput([
    'maxlength' => 30,
]) ?>

<?php $form::end(); ?>