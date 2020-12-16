<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\Common;

use EngineCore\modules\installation\dispatches\Dispatch;
use Yii;

/**
 * Class LicenseAgreement
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class LicenseAgreement extends Dispatch
{
    
    public function run()
    {
        if (Yii::$app->getRequest()->isPost) {
            $nextStep = $this->controller->nextStep;
            if ($this->controller->isFinishedStep($this->id)) {
                goto redirect;
            }
            if (Yii::$app->getRequest()->post("license") == 1) {
                $this->controller->finishStep($this->id);
                redirect:
                
                return $this->controller->redirect([$nextStep]);
            } else {
                $this->response->error('同意安装协议才能继续安装!');
            }
        }
        
        return $this->response->render();
    }
    
}