<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\dispatches\Basic\Common;

use EngineCore\Ec;
use EngineCore\modules\installation\dispatches\Basic\Dispatch;
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