<?php
/**
 * @link      https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\behaviors;

use EngineCore\modules\installation\StepTrait;
use EngineCore\web\Controller;
use yii\base\ActionEvent;
use yii\base\Behavior;

/**
 * 禁止跳转至未完成的步骤，直接返回到第一个未完成的步骤的行为类
 *
 * ```php
 * use EngineCore\modules\installation\behaviors\GoToCurrentStepBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => GoToCurrentStepBehavior::className(),
 *         ],
 *     ];
 * }
 * ```
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class GoToCurrentStepBehavior extends Behavior
{
    
    /**
     * @var Controller|StepTrait
     */
    public $owner;
    
    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'goto',
        ];
    }
    
    /**
     * 禁止跳转至未完成的步骤，直接返回到第一个未完成的步骤
     *
     * @param ActionEvent $event
     */
    public function goto($event)
    {
        $currentStep = $this->owner->getCurrentStep();
        // 禁止跳转至未完成的步骤
        if ((false === $this->owner->isFinishedStep($event->action->id)) && $event->action->id !== $currentStep) {
            $this->owner->action->response
                ->setJumpUrl([$currentStep])
                ->error(\Yii::t('ec/modules/installation', 'Unable to jump to an unfinished step. Please complete the current step first.'));
        }
    }
    
}