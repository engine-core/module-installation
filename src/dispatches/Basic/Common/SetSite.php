<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\dispatches\Basic\Common;

use EngineCore\Ec;
use EngineCore\modules\installation\dispatches\Basic\Dispatch;
use EngineCore\modules\installation\models\SetSiteForm;
use Yii;

/**
 * Class SetSite
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SetSite extends Dispatch
{
    
    public function run()
    {
        /** @var SetSiteForm $model */
        $model = Ec::createObject(SetSiteForm::class, [
            $this->controller->module->getInstaller(),
        ], SetSiteForm::class);
    
        $request = Yii::$app->request;
        if ($request->getIsPost()) {
            if ($model->load($request->post()) && $model->save()) {
                if ($this->controller->isFinishedStep($this->id)) {
                    goto redirect;
                }
                $this->controller->finishStep($this->id);
                redirect:
                
                return $this->controller->redirect([$this->controller->nextStep]);
            } else {
                $this->response->error();
            }
        }
        
        $model->loadDefaultValues();
        
        return $this->response->setAssign([
            'model' => $model,
        ])->render();
    }
    
}