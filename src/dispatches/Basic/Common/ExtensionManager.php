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
use EngineCore\modules\installation\models\ExtensionManageForm;
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
        /** @var ExtensionManageForm $model */
        $model = Ec::createObject(ExtensionManageForm::class, [
            $this->controller->module->getInstaller(),
        ], ExtensionManageForm::class);
        
        $request = Yii::$app->request;
        if ($request->getIsPost()) {
            if ($model->load($request->post()) && $model->save()) {
                // 验证扩展是否满足依赖关系
                if (false === $model->getInstaller()->validate()) {
                    $this->controller->disableStep($this->id);
                    
                    return $this->response->error(Yii::t('ec/modules/installation', 'Please satisfy the extension dependency before proceeding to the next step.'), '');
                }
                
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
        
        $model->getInstaller()->validate();
        
        return $this->response->setAssign([
            'model'      => $model,
            'extensions' => $this->getExtensions(),
            'download'   => $this->getDownload(),
            'conflict'   => $this->getConflict(),
            'circular'   => $this->getCircular(),
        ])->render();
    }
    
    /**
     * 获取需要下载的扩展
     *
     * @return array
     */
    protected function getDownload(): array
    {
        $arr = [];
        foreach (Ec::$service->getExtension()->getDependent()->getDownload() as $uniqueName => $row) {
            foreach ($row as $k => $v) {
                $arr[] = [
                    'name'    => $uniqueName,
                    'parent'  => $v['extensions'] ? implode(' -> ', $v['extensions']) : Yii::t('ec/modules/installation', 'Installation'),
                    'version' => $v['requireVersion'],
                ];
            }
        }
        
        return $arr;
    }
    
    /**
     * 获取无限循环的扩展
     *
     * @return array
     */
    protected function getCircular(): array
    {
        $arr = [];
        foreach (Ec::$service->getExtension()->getDependent()->getCircular() as $uniqueName => $row) {
            $arr[] = [
                'name'  => $uniqueName,
                'chain' => implode(' -> ', $row),
            ];
        }
        
        return $arr;
    }
    
    /**
     * 获取版本冲突的扩展
     *
     * @return array
     */
    protected function getConflict(): array
    {
        $arr = [];
        foreach (Ec::$service->getExtension()->getDependent()->getConflict() as $uniqueName => $row) {
            foreach ($row['items'] as $item) {
                $chain = $item['extensions'] ?: [Yii::t('ec/modules/installation', 'Installation')];
                $chain[] = $uniqueName;
                $arr[] = [
                    'name'           => implode(' -> ', $chain),
                    'version'        => $row['localVersion'],
                    'requireVersion' => $item['requireVersion'],
                ];
            }
        }
        
        return $arr;
    }
    
    /**
     * 获取本地所有扩展
     *
     * @return array
     */
    protected function getExtensions(): array
    {
        $arr = [];
        // 禁选扩展包括【默认扩展和已安装扩展】
        $disabled = $this->controller->module->getInstaller()->getDisabledExtensions();
        // 已选扩展包括【默认扩展、用户自选扩展和已安装扩展】
        $checked = $this->controller->module->getInstaller()->getCheckedExtensions();
        foreach (Ec::$service->getExtension()->getRepository()->getFinder()->getConfiguration() as $uniqueName =>
                 $configuration) {
            $arr[$uniqueName]['name'] = $configuration->getName();
            $arr[$uniqueName]['description'] = $configuration->getDescription();
            $arr[$uniqueName]['version'] = $configuration->getVersion();
            $arr[$uniqueName]['app'] = $configuration->getApp();
            $arr[$uniqueName]['disabled'] = isset($disabled[$uniqueName]);
            $arr[$uniqueName]['checked'] = isset($checked[$uniqueName]);
            $arr[$uniqueName]['checkedApp'] = $checked[$uniqueName]['app'] ?? [];
            $arr[$uniqueName]['disabledApp'] = $disabled[$uniqueName]['app'] ?? [];
        }
        
        return $arr;
    }
    
}