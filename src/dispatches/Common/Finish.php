<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\Common;

use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use EngineCore\modules\installation\dispatches\Dispatch;
use EngineCore\modules\installation\models\FinishForm;
use Yii;

/**
 * Class Finish
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Finish extends Dispatch
{
    
    public function run()
    {
        Yii::beginProfile('block1');
//        Ec::$service->extension->clearCache();

//        $this->controller->module->getInstaller()->createConfigFiles();
        
//        Ec::dump(Ec::$service->extension->repository->finder->configuration["engine-core/theme-bootstrap-v3"]->getExtensionDependencies(),"engine-core/theme-bootstrap-v3");
//        Ec::$service->getExtension()->dependent->checkDependencies("engine-core/theme-bootstrap-v3");
        Ec::$service->getExtension()->dependent->getDependenciesStatus($this->controller->module->installer->getExtensions(),'top',false);
        Ec::dump(Ec::$service->getExtension()->dependent->getResult());


        
//        Ec::dump(Ec::$service->extension->repository->finder->getConfiguration()["engine-core/module-installation"]->getExtensionDependencies());
//        Ec::dump($this->controller->module->installer->getExtensions());
        $db=Ec::$service->extension->repository->getDbConfiguration();
        Ec::dump($db);
        
        
//        Ec::dump(Ec::$service->extension->dependent->checkDependencies("engine-core/module-installation"));
//        Ec::$service->getExtension()->dependent->getDependenciesStatus($this->controller->module->installer->getExtensions(),'top');
//        Ec::dump(Ec::$service->getExtension()->dependent->getResult());
        Yii::endProfile('block1');
        
        if (Yii::$app->getRequest()->isPost) {
            /** @var FinishForm $model */
            $model = Ec::createObject(FinishForm::class, [
                $this->controller->module->getInstaller(),
            ], FinishForm::class);
            
            if ($model->save()) {
                return $this->controller->goHome();
            } else {
                $this->response->error();
            }
        }
        
        return $this->response->render();
    }
    
}