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
        if (false === $this->controller->module->getInstaller()->validate()) {
            $this->controller->disableStep('extension-manager');
            
            return $this->controller->redirect(['extension-manager']);
        }
        
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
        
        return $this->response->setAssign([
            'operations' => [
                Yii::t('ec/modules/installation', 'Automatically jump to the backend home page.'),
                Yii::t('ec/modules/installation', 'Install the extension and automatically complete the database migration required by the extension.'),
                Yii::t('ec/modules/installation', 'According to the installed extension, the menu configuration, system configuration and permission configuration required by the extension are automatically generated.'),
                Yii::t('ec/modules/installation', 'For security reasons, the current installation wizard module will be automatically uninstalled after completing the installation steps.'),
                Yii::t('ec/modules/installation', 'The current system environment [Installation - Installation environment] will automatically switch to [Development - Development environment].'),
            ],
        ])->render();
    }
    
}