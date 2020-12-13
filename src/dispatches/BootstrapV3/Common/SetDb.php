<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\BootstrapV3\Common;

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
        $model = new DatabaseForm();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->controller->redirect($this->controller->nextStep);;
            } else {
                $this->response->error();
            }
        }

        return $this->response->setAssign([
            'model' => $model,
        ])->render();
    }
    
}