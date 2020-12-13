<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\Common;

use EngineCore\modules\installation\dispatches\Dispatch;
use EngineCore\modules\installation\helpers\EnvironmentHelper;
use Yii;

/**
 * Class CheckEnv
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class CheckEnv extends Dispatch
{
    
    public function run()
    {
        $env = new EnvironmentHelper();
        
        if (Yii::$app->getRequest()->isPost) {
            $nextStep = $this->controller->nextStep;
            if ($this->controller->isFinishedStep($this->id)) {
                goto redirect;
            }
            $this->controller->finishStep($this->id);
            redirect:
            
            return $this->controller->redirect([$nextStep]);
        }
        
        return $this->response->setAssign([
            'summary'      => $env->checker->getResult()['summary'],
            'requirements' => $env->checker->getResult()['requirements'],
            'serverInfo'   => $env->checker->getServerInfo(),
            'nowDate'      => $env->checker->getNowDate(),
        ])->render();
    }
    
}