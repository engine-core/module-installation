<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use EngineCore\Ec;
use EngineCore\modules\installation\helpers\InstallerHelper;
use Yii;
use yii\db\Connection;

/**
 * Class InstallHelperTrait
 *
 * @property InstallerHelper $installer
 * @property Connection $db
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait InstallHelperTrait
{

    /**
     * @var InstallerHelper
     */
    private $_installer;

    /**
     * 获取安装助手类
     *
     * @return InstallerHelper
     */
    public function getInstaller(): InstallerHelper
    {
        if (null === $this->_installer) {
            $this->_installer = Ec::createObject(InstallerHelper::class, [], InstallerHelper::class);
        }

        return $this->_installer;
    }

    /**
     * 获取数据库连接组件
     *
     * @return Connection
     */
    public function getDb(): Connection
    {
        return $this->getInstaller()->getDb();
    }

    /**
     * 初始化安装环境
     */
    public function initialize()
    {
        // 加载最新扩展别名，确保能够完全加载本地系统扩展
        foreach (Ec::$service->getExtension()->getRepository()->getFinder()->getAliases() as $ns => $path) {
            Yii::setAlias($ns, $path);
        }
        // 加载扩展实体配置
        Ec::$service->getExtension()->entity($this)->loadConfig();
        // 生成系统默认配置文件
        if (!is_file(Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->settingFile))) {
            $settings = Ec::$service->getSystem()->getSetting()->getProvider()->getDefaultConfig();
            Ec::$service->getExtension()->getEnvironment()->flushSettingFile($settings);
        }
        if (!is_file(Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->menuFile))) {
            $menus = Ec::$service->getMenu()->getConfig()->getProvider()->getDefaultConfig();
            Ec::$service->getExtension()->getEnvironment()->flushMenuFile($menus);
        }
    }

}