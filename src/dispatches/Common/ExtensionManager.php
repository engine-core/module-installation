<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\Common;

use EngineCore\Ec;
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
        /** @var ExtensionForm $model */
        $model = Ec::createObject(ExtensionForm::class, [
            $this->controller->module->getInstaller(),
        ], ExtensionForm::class);
        
        $model->loadDefaultValues();
        
        if (Yii::$app->getRequest()->isPost) {
            $nextStep = $this->controller->nextStep;
            if ($this->controller->isFinishedStep($this->id)) {
                goto redirect;
            }
            if ($model->save()) {
                $this->controller->finishStep($this->id);
                redirect:
                
                return $this->controller->redirect([$nextStep]);
            } else {
                $this->response->error();
            }
        }
        
        return $this->response->setAssign([
            'extensions' => $this->extensionList(),
        ])->render();
    }
    
    protected function extensionList()
    {
        $arr = [];
        $definitions = Ec::$service->getExtension()->getDependent()->getDefinitions();
        foreach ($this->controller->module->installer->getExtensions() as $uniqueName => $row) {
            // 本地存在扩展
            if (isset($definitions[$uniqueName])) {
                $arr[$uniqueName] = $exists = $definitions[$uniqueName];
                $arr[$uniqueName]['app'] = []; // 储存在哪个app里安装扩展
                unset($arr[$uniqueName]['extensionDependencies']);
                // 指定在哪个app里安装扩展
                if (isset($row['app'])) {
                    // 规范app数据
                    $row['app'] = array_intersect($exists['app'], (array)$row['app']);
                    $arr[$uniqueName]['app'] = !empty($row['app'])
                        ? $row['app'] //存在有效app
                        : $exists['app']; // 没有有效app，则默认在所有有效的app里安装扩展
                    foreach ($row['app'] as $app) {
                        if (isset($exists['extensionDependencies'][$app])) {
                            $arr[$uniqueName]['extensionDependencies'][$app] = $exists['extensionDependencies'][$app];
                        }
                    }
                } // 没有指定app，则默认在所有有效的app里安装扩展
                else {
                    $arr[$uniqueName]['app'] = $exists['app'];
                    $arr[$uniqueName]['extensionDependencies'] = $exists['extensionDependencies'];
                }
            } // 本地不存在扩展
            else {
                $arr[$uniqueName] = $row;
            }
        }
        
        return $arr;
    }
    
}