<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\BootstrapV3\Common;

use EngineCore\modules\installation\dispatches\Dispatch;
use Yii;

/**
 * Class Index
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Index extends Dispatch
{
    
    public function run()
    {
        if (Yii::$app->getRequest()->isPost) {
            return $this->controller->redirect($this->controller->nextStep);
        }
        
        return $this->response->render();
    }
    
}