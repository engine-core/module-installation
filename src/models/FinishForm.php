<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\models;

use EngineCore\Ec;

/**
 * Class FinishForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class FinishForm extends BaseForm
{
    
    /**
     * 保存
     *
     * @return bool
     */
    public function save()
    {
        
        $this->getInstaller()->getExtensionModuleInfo()->setRepositoryModel();
        
        
    
//        // 创建扩展配置文件，只生成已经安装扩展的配置文件
//        Yii::$app->runAction('extension/flush-config-files');
//
//        // 初始化环境为`Development`
//        $this->changeEnvironment();
//
//        // 创建安装锁定文件
//        $this->module->installer->lock;
    }
    
}