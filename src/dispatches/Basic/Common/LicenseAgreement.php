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
use yii\helpers\Markdown;

/**
 * Class LicenseAgreement
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class LicenseAgreement extends Dispatch
{

    public function run()
    {
        if (Yii::$app->getRequest()->getIsPost()) {
            if ($this->controller->isFinishedStep($this->id)) {
                goto redirect;
            }
            if (Yii::$app->getRequest()->post("license") == 1) {
                $this->controller->finishStep($this->id);
                redirect:

                return $this->controller->redirect([$this->controller->nextStep]);
            } else {
                $this->response->error(Yii::t('ec/modules/installation', 'Agree to the installation agreement to continue the installation.'));
            }
        }

        return $this->response->setAssign([
            'license' => $this->getLicense(),
        ])->render();
    }

    protected function getLicense()
    {
        $license = file_get_contents($this->controller->module->getInstaller()->getLicenseFile());

        return Markdown::process($license);
    }

}