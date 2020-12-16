<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\Common;

use EngineCore\Ec;
use EngineCore\modules\installation\dispatches\Dispatch;
use EngineCore\modules\installation\models\DatabaseForm;
use Yii;

/**
 * Class SetDb
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SetDb extends Dispatch
{
    
    public function run()
    {
        /** @var DatabaseForm $model */
        $model = Ec::createObject(DatabaseForm::class, [
            $this->controller->module->getInstaller(),
        ], DatabaseForm::class);
        
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                if (!$this->controller->isFinishedStep($this->id)) {
                    $this->controller->finishStep($this->id);
                }
                
                return $this->controller->redirect([$this->controller->nextStep]);
            } else {
                $this->response->error();
            }
        }
        
        return $this->response->setAssign([
            'model' => $model,
        ])->render();
    }
    
}