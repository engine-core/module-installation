<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\dispatches\Basic\Common;

use EngineCore\modules\installation\dispatches\Basic\Dispatch;
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
            if ($this->controller->isFinishedStep($this->id)) {
                goto redirect;
            }
            $this->controller->finishStep($this->id);
            redirect:

            return $this->controller->redirect([$this->controller->nextStep]);
        }

        return $this->response->render();
    }

}