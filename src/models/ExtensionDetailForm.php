<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\models;

use EngineCore\extension\repository\info\ExtensionInfo;

/**
 * Class ExtensionDetailForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionDetailForm extends BaseForm
{
    
    /**
     * 保存，必须在执行前确保已经进行了依赖关系检测，才能确保被依赖的扩展可以被自动添加进需要安装的扩展缓存数据里
     * @see \EngineCore\services\Extension\Dependent::validate()
     *
     * @return bool
     */
    public function save()
    {
        // 把准备安装的扩展更新进已选择扩展的缓存里
        $unInstallExtension = $this->getInstaller()->getUnInstallExtension();
        if (!empty($unInstallExtension)) {
            $checkedExtensions = [];
            foreach ($unInstallExtension as $row) {
                /** @var ExtensionInfo $infoInstance */
                foreach ($row as $infoInstance) {
                    $uniqueName = $infoInstance->getUniqueName();
                    $app = $infoInstance->getApp();
                    $version = $infoInstance->getConfiguration()->getVersion();
                    if (!isset($checkedExtensions[$uniqueName])) {
                        $checkedExtensions[$uniqueName] = ['version' => $version, 'app' => [$app]];
                    } elseif (!in_array($app, $checkedExtensions[$uniqueName]['app'])) {
                        $checkedExtensions[$uniqueName]['app'][] = $app;
                    }
                }
            }
            $this->getInstaller()->setCheckedExtensions($checkedExtensions);
        }
        
        return true;
    }
    
}