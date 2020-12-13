<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\BootstrapV3\Common;

use EngineCore\modules\installation\dispatches\Dispatch;
use EngineCore\modules\installation\models\SiteForm;
use Yii;

/**
 * Class SetSite
 */
class SetSite extends Dispatch
{
    
    public function run()
    {
        $model = new SiteForm();
        $model->loadDefaultValues();
    
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $this->response->success();
            } else {
                $this->response->error();
            }
        }
    
        return $this->response->setAssign([
            'model' => $model,
        ])->render();
    }
    
}