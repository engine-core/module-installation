<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\controllers;

use EngineCore\modules\installation\StepTrait;
use EngineCore\web\Controller;
use Yii;

/**
 * Class CommonController
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class CommonController extends Controller
{
    
    use StepTrait;
    
    protected $defaultDispatchMap = ['index', 'set-db', 'license-agreement', 'check-env', 'set-admin',
        'extension-manager', 'finish'];
    
    /**
     * @var \EngineCore\modules\installation\Module
     */
    public $module;
    
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
    
        $this->getExtension()->loadConfig();
    }
    
    /**
     * {@inheritDoc}
     */
    public function beforeAction($action)
    {
        $currentStep = $this->getCurrentStep();
        // 禁止跳转至未完成的步骤
        if ((false === $this->isFinishedStep($action->id)) && $action->id !== $currentStep) {
            $this->redirect([$currentStep]);
            Yii::$app->end();
        }
        
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        return true;
    }
    
}