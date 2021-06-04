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
use EngineCore\modules\installation\models\ExtensionDetailForm;
use Yii;

/**
 * Class ExtensionDetail
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionDetail extends Dispatch
{
    
    public function run()
    {
        /** @var ExtensionDetailForm $model */
        $model = Ec::createObject(ExtensionDetailForm::class, [
            $this->controller->module->getInstaller(),
        ], ExtensionDetailForm::class);
        
        if (false === $model->getInstaller()->validate()) {
            $this->controller->disableStep('extension-manager');
            
            return $this->controller->redirect(['extension-manager']);
        }
        
        if (Yii::$app->getRequest()->getIsPost()) {
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
            'extensions' => $this->getExtensions(),
        ])->render();
    }
    
    /**
     * 获取需要安装的扩展，包括被依赖的扩展
     *
     * @return array
     */
    protected function getExtensions(): array
    {
        $definitions = Ec::$service->getExtension()->getDependent()->getDefinitions();
        $dbConfiguration = Ec::$service->getExtension()->getRepository()->getDbConfiguration();
        // 剔除不需要安装的数据
        $definitions = array_intersect_key($definitions, Ec::$service->getExtension()->getDependent()->getPassed());
        foreach (Ec::$service->getExtension()->getDependent()->getPassed() as $uniqueName => $row) {
            $extensionDependencies = $definitions[$uniqueName]['extensionDependencies'];
            // 重置app，该值改为需要安装的app
            $definitions[$uniqueName]['app'] = [];
            // 重置扩展依赖数据
            $definitions[$uniqueName]['extensionDependencies'] = [];
            foreach ($row['app'] as $app) {
                // 没有安装，只保存需要安装的数据
                if (!isset($dbConfiguration[$app][$uniqueName])) {
                    $definitions[$uniqueName]['app'][] = $app;
                    $definitions[$uniqueName]['extensionDependencies'][$app] = $extensionDependencies[$app] ?? [];
                }
            }
            // 剔除不需要安装的数据
            if (empty($definitions[$uniqueName]['app'])) {
                unset($definitions[$uniqueName]);
            }
        }
        
        return $definitions;
    }
    
}