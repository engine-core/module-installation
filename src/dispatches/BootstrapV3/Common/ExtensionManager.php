<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\BootstrapV3\Common;

use EngineCore\Ec;
use EngineCore\helpers\ConsoleHelper;
use EngineCore\helpers\SecurityHelper;
use EngineCore\modules\installation\dispatches\Dispatch;
use EngineCore\modules\installation\models\ExtensionForm;
use Yii;

/**
 * Class ExtensionManager
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionManager extends Dispatch
{
    
    public function run()
    {
//        Ec::$service->getExtension()->getRepository()->getFinder()->clearCache();
    
        /** @var ExtensionForm $model */
        $model = Ec::createObject(ExtensionForm::class, [], ExtensionForm::class);
        $model->loadDefaultValues();
        
        if (Yii::$app->getRequest()->isPost) {
//            ConsoleHelper::run('./yii migrate');
            return;
//            if ($model->save()) {
//                $this->response->success();
//            } else {
//                $this->response->error();
//            }
        }
        
        return $this->response->setAssign([
//            'dependList' => Ec::$service->getExtension()->getRepository()->getLocalConfiguration(),
            'dependList' => Ec::$service->getExtension()->getDependent()->getDefinitions(),
        ])->render();
    }
    
}