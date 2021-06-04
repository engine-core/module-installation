<?php
/**
 * @link      https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\helpers;

use EngineCore\Ec;
use EngineCore\enums\AppEnum;
use EngineCore\enums\StatusEnum;
use EngineCore\enums\YesEnum;
use EngineCore\extension\installation\ExtensionInterface;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\helpers\ConsoleHelper;
use EngineCore\helpers\FileHelper;
use EngineCore\helpers\MigrationHelper;
use EngineCore\modules\installation\Module;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\console\Application;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class InstallerHelper
 *
 * @property Connection                       $db                  数据库连接组件，可读写
 * @property string                           $lockFile            安装锁文件，可读写
 * @property string                           $licenseFile         授权协议文件，可读写
 * @property array                            $tables              储存扩展的数据表数据，可读写
 * @property array                            $checkedExtensions   已经选择的扩展，可读写
 * @property array                            $defaultExtensions   默认需要安装的扩展，可读写
 * @property ExtensionInterface|ExtensionInfo $extensionModuleInfo 扩展管理模块信息类，只读
 * @property string                           $extensionCategory   当前已安装的扩展管理分类的扩展名，可读写
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class InstallerHelper extends BaseObject
{
    
    /**
     * @var bool 是否控制台应用
     */
    private $_isConsoleApp = false;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->_isConsoleApp = Yii::$app instanceof Application;
    }
    
    /**
     * 检查是否已经安装
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return is_file($this->getLockFile());
    }
    
    /**
     * 移除安装锁文件
     *
     * @return bool
     */
    public function unLock()
    {
        return FileHelper::removeFile($this->getLockFile());
    }
    
    /**
     * 创建安装锁定文件
     *
     * @return bool
     */
    public function lock(): bool
    {
        return FileHelper::createFile($this->getLockFile(), 'Lock at ' . date('Y-m-d H:i:s'));
    }
    
    /**
     * @var string 安装锁文件
     */
    private $_lockFile;
    
    /**
     * 获取安装锁文件
     *
     * @return string
     */
    public function getLockFile(): string
    {
        if (null === $this->_lockFile) {
            $this->setLockFile('@common/install.lock');
        }
        
        return $this->_lockFile;
    }
    
    /**
     * 设置安装锁文件
     *
     * @param string $lockFile
     */
    public function setLockFile(string $lockFile)
    {
        $this->_lockFile = Yii::getAlias($lockFile);
    }
    
    /**
     * @var string 授权协议文件
     */
    private $_licenseFile;
    
    /**
     * 获取授权协议文件
     *
     * @return string
     */
    public function getLicenseFile(): string
    {
        if (null === $this->_licenseFile) {
            $this->setLicenseFile('@root/LICENSE.md');
        }
        
        return $this->_licenseFile;
    }
    
    /**
     * 设置授权协议文件
     *
     * @param string $licenseFile
     */
    public function setLicenseFile(string $licenseFile)
    {
        $this->_licenseFile = Yii::getAlias($licenseFile);
    }
    
    /**
     * @var array 储存扩展的数据表数据
     */
    private $_tables;
    
    /**
     * 获取储存扩展的数据库表数据
     *
     * @return array
     */
    public function getTables(): array
    {
        if (null === $this->_tables) {
            $this->setTables();
        }
        
        return $this->_tables;
    }
    
    /**
     * 设置储存扩展的数据库表数据
     *
     * @param array $tables
     */
    public function setTables(array $tables = [])
    {
        $this->_tables = array_merge([
            // 模块扩展数据表
            ExtensionInfo::TYPE_MODULE     => MigrationHelper::createTableName('module', ExtensionInfo::EXT_RAND_CODE),
            // 控制器扩展数据表
            ExtensionInfo::TYPE_CONTROLLER => MigrationHelper::createTableName('controller', ExtensionInfo::EXT_RAND_CODE),
            // 主题扩展数据表
            ExtensionInfo::TYPE_THEME      => MigrationHelper::createTableName('theme', ExtensionInfo::EXT_RAND_CODE),
            // 系统配置扩展数据表
            ExtensionInfo::TYPE_CONFIG     => MigrationHelper::createTableName('config', ExtensionInfo::EXT_RAND_CODE),
        ], $tables);
    }
    
    /**
     * @var array 默认需要安装的扩展
     */
    private $_defaultExtensions;
    
    /**
     * 获取默认需要安装的扩展
     *
     * @return array
     */
    public function getDefaultExtensions(): array
    {
        return $this->_defaultExtensions;
    }
    
    /**
     * 设置默认需要安装的扩展
     *
     * @param array $defaultExtensions
     * 可用键名有：
     * `version` - string: 指定安装的版本
     * `app` - string|array: 指定在哪个app应用里安装
     *
     * ```php
     * [
     *  'engine-core/theme-bootstrap-v3' => [
     *      'version' => '~1.0.0',
     *      'app'     => ['backend'],
     *  ],
     * ]
     * ```
     *
     * @see \EngineCore\services\extension\Dependent::normalize()
     */
    public function setDefaultExtensions(array $defaultExtensions)
    {
        $this->_defaultExtensions = Ec::$service->getExtension()->getDependent()->normalize($defaultExtensions);
    }
    
    /**
     * @var array 已经选择的扩展
     */
    private $_extensions;
    
    /**
     * 获取已经选择的扩展，包含默认需要安装、已经安装和自选的扩展
     *
     * @return array
     */
    public function getCheckedExtensions(): array
    {
        if (null === $this->_extensions) {
            $this->_extensions = $this->cache()->getOrSet(Module::CACHE_CHECKED_EXTENSION, function () {
                $this->setCheckedExtensions([]);
                
                return $this->_extensions;
            });
        }
        
        return $this->_extensions;
    }
    
    /**
     * 设置已经选择的扩展
     *
     * @param array $extensions
     *
     * @see setDefaultExtensions()
     */
    public function setCheckedExtensions(array $extensions)
    {
        $checkedExtensions = $this->getDisabledExtensions();
        // 合并自选扩展与默认需要安装和已经安装的扩展
        if (!empty($extensions)) {
            $extensions = Ec::$service->getExtension()->getDependent()->normalize($extensions);
            $checkedExtensions = ArrayHelper::merge($checkedExtensions, $extensions);
            foreach ($checkedExtensions as $uniqueName => &$row) {
                $row['app'] = array_unique($row['app']);
            }
        }
        $this->_extensions = $checkedExtensions;
        $this->cache()->set(Module::CACHE_CHECKED_EXTENSION, $this->_extensions);
    }
    
    private $_disabledExtensions;
    
    /**
     * 获取禁止选择的扩展，包含默认需要安装和已经安装的扩展，并以数据库数据为准
     *
     * @return array
     */
    public function getDisabledExtensions(): array
    {
        if (null === $this->_disabledExtensions) {
            $this->_disabledExtensions = $this->getDefaultExtensions();
            $dbExtensions = Ec::$service->getExtension()->getRepository()->getDbConfiguration();
            foreach ($dbExtensions as $app => $row) {
                foreach ($row as $uniqueName => $data) {
                    // 目前系统只允许同一个扩展在同一个应用里安装一次，故取第一条数据即可
                    $data = $data[0];
                    if (isset($this->_disabledExtensions[$uniqueName])) {
                        $this->_disabledExtensions[$uniqueName]['version'] = $data['version'];
                        if (!in_array($app, $this->_disabledExtensions[$uniqueName]['app'])) {
                            $this->_disabledExtensions[$uniqueName]['app'][] = $app;
                        }
                    } else {
                        $this->_disabledExtensions[$uniqueName]['version'] = $data['version'];
                        $this->_disabledExtensions[$uniqueName]['app'][] = $app;
                    }
                }
            }
        }
        
        return $this->_disabledExtensions;
    }
    
    /**
     * 验证需要安装的扩展是否满足依赖关系
     *
     * @return bool
     */
    public function validate(): bool
    {
        return Ec::$service->getExtension()->getDependent()->validate($this->getCheckedExtensions());
    }
    
    /**
     * 获取没有安装的扩展，必须在执行前确保已经进行了依赖关系检测
     * @see \EngineCore\services\Extension\Dependent::validate()
     *
     * @return array
     */
    public function getUnInstallExtension(): array
    {
        $unInstallExtension = [];
        $configuration = Ec::$service->getExtension()->getRepository()->getLocalConfiguration();
        $dbConfiguration = Ec::$service->getExtension()->getRepository()->getDbConfiguration();
        foreach (Ec::$service->getExtension()->getDependent()->getPassed() as $uniqueName => $row) {
            foreach ($row['app'] as $app) {
                if (!isset($dbConfiguration[$app][$uniqueName])) {
                    /** @var ExtensionInfo $infoInstance */
                    $infoInstance = $configuration[$app][$uniqueName];
                    $unInstallExtension[][$uniqueName] = $infoInstance;
                }
            }
        }
        
        return $unInstallExtension;
    }
    
    /**
     * @var Connection
     */
    private $_db;
    
    /**
     * 获取数据库连接组件
     *
     * @return Connection
     */
    public function getDb(): Connection
    {
        if (null === $this->_db) {
            $this->setDb('db');
        }
        
        return $this->_db;
    }
    
    /**
     * 设置数据库连接组件
     *
     * @param Connection|array|string $db
     */
    public function setDb($db)
    {
        $this->_db = Instance::ensure($db, Connection::class);
    }
    
    /**
     * 把需要安装的扩展写入扩展仓库数据库，并执行扩展内的`install()`安装方法
     *
     * @see ExtensionInfo::install()
     *
     * @return bool
     */
    public function save(): bool
    {
        $consoleController = Yii::$app->controller;
        $unInstallExtension = $this->getUnInstallExtension();
        if (empty($unInstallExtension)) {
            return !empty($this->getExtensionCategory());
        }
        
        /**
         * 初始化扩展安装环境
         *
         * @return bool
         */
        $initialize = function () use (&$unInstallExtension, $consoleController) {
            if (!empty($this->getExtensionCategory())) {
                // 配置扩展仓库模型，为下一步【创建扩展配置文件】提供已经安装扩展的数据支持
                if (!Ec::$service->getExtension()->getRepository()->hasModel()) {
                    $this->getExtensionModuleInfo()->setRepositoryModel();
                    $unInstallExtension = $this->getUnInstallExtension();
                }
                if ($this->_isConsoleApp) {
                    $consoleController->stdout(
                        "\n====== The extension installation environment has been initialized successfully ======\n\n",
                        Console::FG_YELLOW);
                }
                
                return true;
            }
            if ($this->_isConsoleApp) {
                $consoleController->stdout(
                    "\n====== Initialize extension installation environment ======\n",
                    Console::FG_YELLOW);
            }
            $res = false;
            foreach ($unInstallExtension as $rows) {
                /** @var ExtensionInfo $infoInstance */
                foreach ($rows as $uniqueName => $infoInstance) {
                    if ($infoInstance instanceof ExtensionInterface) {
                        $res = $infoInstance->initialize();
                        if ($res) {
                            $this->setExtensionCategory($uniqueName);
                            // 配置扩展仓库模型，为下一步【创建扩展配置文件】提供已经安装扩展的数据支持
                            $this->getExtensionModuleInfo()->setRepositoryModel();
                        } elseif ($this->_isConsoleApp) {
                            $consoleController->stdout(
                                "\n====== Failed to initialize the extension installation environment ======\n",
                                Console::FG_RED);
                        }
                        break;
                    }
                }
            }
            if ($this->_isConsoleApp) {
                $consoleController->stdout("\n");
            }
            
            return $res;
        };
        
        if (false === $initialize()) {
            return false;
        }
        
        /**
         * 把需要安装的扩展添加进数据库里
         */
        $installExtension = function () use (&$unInstallExtension, $consoleController) {
            /**
             * 执行扩展内的安装方法
             *
             * @param ExtensionInfo $infoInstance
             *
             * @return bool
             */
            $runExtensionInstall = function ($infoInstance) use ($consoleController) {
                $running = Console::renderColoredString("%yRunning: %s::install() - %s%y\n");
                $endingSuccessful = Console::renderColoredString("%gEnding: %s::install()%g %n======>%n %C%s%C\n\n");
                $endingFailed = Console::renderColoredString("%gEnding: %s::install()%g %n======>%n %R%s%R\n\n");
                if ($this->_isConsoleApp) {
                    $consoleController->stdout(sprintf($running, get_class($infoInstance), $infoInstance->getApp()));
                    if ($infoInstance->install()) {
                        $consoleController->stdout(sprintf($endingSuccessful, get_class($infoInstance), 'Successful'));
                        
                        return true;
                    } else {
                        $consoleController->stdout(sprintf($endingFailed, get_class($infoInstance), 'Failed'));
                        
                        return false;
                    }
                } else {
                    return $infoInstance->install();
                }
            };
            /**
             * 构建待写入数据库里的扩展配置数据
             *
             * @param ExtensionInfo $infoInstance
             * @param array         $append
             *
             * @return array
             */
            $build = function (ExtensionInfo $infoInstance, $append = []): array {
                return array_merge([
                    'unique_id'   => $infoInstance->getUniqueId(),
                    'unique_name' => $infoInstance->getUniqueName(),
                    'is_system'   => YesEnum::YES, // 默认安装的扩展标记为系统扩展
                    'status'      => StatusEnum::STATUS_ON,
                    'run'         => ExtensionInfo::RUN_MODULE_EXTENSION, // 默认安装的扩展运行模式为系统扩展
                    'version'     => $infoInstance->getConfiguration()->getVersion(),
                    'category'    => $infoInstance->getCategory(),
                    'app'         => $infoInstance->getApp(),
                    'created_at'  => time(),
                ], $append);
            };
            // 待写入数据库里的扩展配置数据
            $data = [];
            ConsoleHelper::$showInfo = $this->_isConsoleApp;
            if ($this->_isConsoleApp) {
                $consoleController->stdout("====== Run extended internal installation method ======\n\n", Console::FG_YELLOW);
            }
            $command = $this->getDb()->createCommand();
            foreach ($unInstallExtension as $key => $rows) {
                /** @var ExtensionInfo $infoInstance */
                foreach ($rows as $uniqueName => $infoInstance) {
                    switch ($infoInstance->getType()) {
                        case ExtensionInfo::TYPE_CONTROLLER:
                            /** @var ControllerInfo $infoInstance */
                            $data = $build($infoInstance, [
                                'module_id'     => $infoInstance->getModuleId(),
                                'controller_id' => $infoInstance->getId(),
                            ]);
                            break;
                        case ExtensionInfo::TYPE_MODULE:
                            /** @var ModularityInfo $infoInstance */
                            $data = $build($infoInstance, [
                                'module_id' => $infoInstance->getId(),
                                'bootstrap' => $infoInstance->getBootstrap(),
                            ]);
                            break;
                        case ExtensionInfo::TYPE_THEME:
                            $data = $build($infoInstance, [
                                'theme_id' => $infoInstance->getId(),
                            ]);
                            break;
                        case ExtensionInfo::TYPE_CONFIG:
                            $data = $build($infoInstance);
                            break;
                    }
                    
                    if ($runExtensionInstall($infoInstance)) {
                        $command->upsert($this->tables[$infoInstance->getType()], $data, false)->execute();
                    }
                }
            }
        };
        
        // 安装扩展
        $installExtension();
        
        return true;
    }
    
    /**
     * 获取已经安装的扩展管理模块信息类
     *
     * @return ExtensionInterface|null
     * @throws Exception
     */
    public function getExtensionModuleInfo()
    {
        $infoInstance = null;
        if (!empty($uniqueName = $this->getExtensionCategory())) {
            $infoInstance = Ec::$service->getExtension()->getRepository()->getLocalConfiguration()[AppEnum::BACKEND][$uniqueName];
        }
        
        return $infoInstance;
    }
    
    /**
     * 初始化运行环境
     *
     * @param string $env
     *
     * @return bool
     */
    public function changeEnvironment($env = 'dev'): bool
    {
        switch (true) {
            case $env == 'prod':
                $environment = 'Production';
                break;
            case $env == 'ins':
                $environment = 'Installation';
                break;
            default:
                $environment = 'Development';
        }
        ConsoleHelper::$showInfo = $this->_isConsoleApp;
        $consoleController = Yii::$app->controller;
        if ($this->_isConsoleApp) {
            $consoleController->stdout("====== Initialize the environment as '{$environment}' ======\n\n", Console::FG_YELLOW);
        }
        
        //执行
        return ConsoleHelper::run(sprintf("%s --env={$environment} --overwrite=y",
            Yii::getAlias(ConsoleHelper::getCommander('init'))
        ));
    }
    
    /**
     * 获取需要安装的扩展管理分类的扩展名
     *
     * @@return string
     */
    public function getExtensionCategory(): string
    {
        return $this->cache()->get(Module::CACHE_EXTENSION_CATEGORY) ?: '';
    }
    
    /**
     * 设置需要安装的扩展管理分类的扩展名
     *
     * @param string $uniqueName
     */
    protected function setExtensionCategory(string $uniqueName)
    {
        $this->cache()->set(Module::CACHE_EXTENSION_CATEGORY, $uniqueName);
    }
    
    /**
     * 缓存组件
     *
     * @return \yii\caching\Cache
     */
    public function cache()
    {
        return Ec::$service->getSystem()->getCache()->getComponent();
    }
    
}