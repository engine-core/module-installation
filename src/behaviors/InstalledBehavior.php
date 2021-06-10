<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\base\Module;
use yii\web\Application;

/**
 * 检测项目是否已经安装的行为类
 *
 * ```php
 * use EngineCore\modules\installation\behaviors\InstalledBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => InstalledBehavior::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class InstalledBehavior extends Behavior
{

    /**
     * @var \EngineCore\modules\installation\Module
     */
    public $owner;

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            Module::EVENT_BEFORE_ACTION => 'isInstalled',
        ];
    }

    /**
     * 是否已经安装
     *
     * @param ActionEvent $event
     */
    public function isInstalled($event)
    {
        if ($this->owner->getInstaller()->isLocked() && Yii::$app instanceof Application) {
            Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
            Yii::$app->end();
        }
    }

}