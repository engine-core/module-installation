<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\helpers;

use EngineCore\Ec;
use EngineCore\enums\EnableEnum;
use EngineCore\enums\YesEnum;
use EngineCore\extension\installation\InstallExtensionInterface;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\helpers\ConsoleHelper;
use EngineCore\helpers\FileHelper;
use EngineCore\helpers\MigrationHelper;
use EngineCore\modules\installation\Module as InstallationModule;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\db\Connection;
use yii\di\Instance;

/**
 * Class InstallerHelper
 *
 * @property string                                  $lockFile 获取安装锁文件，可读写
 * @property bool                                    $isLocked 检查是否已经安装，只读
 * @property bool                                    $lock 创建安装锁定文件，只读
 * @property bool                                    $unLock 移除安装锁文件，只读
 * @property array                                   $tables 储存扩展的数据表数据，可读写
 * @property array                                   $extensions 需要安装的扩展，可读写
 * @property array                                   $dependenciesStatus 需要安装的扩展的依赖状态，只读
 * @property array                                   $installExtension 通过校验的扩展，只读
 * @property InstallExtensionInterface|ExtensionInfo $extensionModuleInfo 扩展模块信息类，只读
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class InstallerHelper extends BaseObject
{
    
    /**
     * @var array 默认需要安装的扩展
     * 可用配置键名有：
     * `version`: 指定安装的版本
     * `app`: 指定在哪个app应用里安装
     */
    public $defaultExtensions = [
        // modules模块扩展
        'engine-core/module-backend-extension' => [ // 必须安装，用于管理系统扩展模块
            'version' => 'dev-master',
        ],
        // controllers控制器扩展
        'engine-core/controller-backend-site'  => '*',
        'engine-core/controller-frontend-site' => '*',
        // themes主题扩展
        'engine-core/theme-bootstrap-v3'       => [ // 基础主题
            'version' => 'dev-master',
            'app'     => ['backend', 'frontend'],
        ],
    ];
    
    /**
     * @var Connection|array|string
     */
    public $db = 'db';
    
    /**
     * @var array 需要安装的扩展
     */
    private $_extensions;
    
    /**
     * 检查是否已经安装
     *
     * @return bool
     */
    public function getIsLocked(): bool
    {
        return is_file($this->getLockFile());
    }
    
    /**
     * 移除安装锁文件
     *
     * @return bool
     */
    public function getUnLock()
    {
        return FileHelper::removeFile($this->getLockFile());
    }
    
    /**
     * 创建安装锁定文件
     *
     * @return bool
     */
    public function getLock()
    {
        return FileHelper::createFile($this->getLockFile(), 'locked');
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
        ], $tables);
    }
    
    /**
     * 获取缓存里的配置数据
     *
     * @param string $id
     * @param string $type 'components','container'
     *
     * @return array
     */
    public function getConfig(string $id, $type = 'components')
    {
        $data = Ec::$service->getSystem()->getCache()->getComponent()->get(InstallationModule::CACHE_CONFIG) ?: [];
        
        return $data[$type][$id] ?? [];
    }
    
    /**
     * 设置缓存里的配置数据
     *
     * @param string $id
     * @param array  $config
     * @param string $type 'components','container'
     */
    public function setConfig(string $id, array $config, $type = 'components')
    {
        $data = Ec::$service->getSystem()->getCache()->getComponent()->get(InstallationModule::CACHE_CONFIG) ?: [];
        $data[$type][$id] = array_merge($data[$type][$id] ?? [], $config);
        
        Ec::$service->getSystem()->getCache()->getComponent()->set(InstallationModule::CACHE_CONFIG, $data);
    }
    
    /**
     * 获取需要安装的扩展
     *
     * @return array
     */
    public function getExtensions(): array
    {
        if (null === $this->_extensions) {
            $this->setExtensions([]);
        }
        
        return $this->_extensions;
    }
    
    /**
     * 设置需要安装的扩展
     *
     * @param array $extensions
     */
    public function setExtensions(array $extensions)
    {
        // 包含默认需要安装的扩展
        $extensions = array_merge($this->defaultExtensions, $extensions);
        $this->_extensions = Ec::$service->getExtension()->getDependent()->normalize($extensions);
    }
    
    private $_dependenciesStatus;
    
    /**
     * 获取需要安装的扩展的依赖状态
     *
     * @return array
     * ```php
     * [
     * 'download'  => [], // 提示下载扩展
     * 'conflict'  => [], // 提示扩展版本冲突
     * 'uninstall' => [], // 提示需要安装的扩展
     * 'circular'  => [], // 无限循环依赖的扩展
     * 'passed'    => [], // 通过依赖检测的扩展
     * ]
     * ```
     */
    public function getDependenciesStatus(): array
    {
        if (null === $this->_dependenciesStatus) {
            $this->_dependenciesStatus = Ec::$service->getExtension()->getDependent()->getDependenciesStatus(
                $this->getExtensions(),
                'installation',
                false
            );
        }
        
        return $this->_dependenciesStatus;
    }
    
    /**
     * 获取通过校验的扩展
     *
     * @return array
     */
    public function getInstallExtension(): array
    {
        return $this->getDependenciesStatus()['passed'] ?? [];
    }
    
    /**
     * 把已经安装的扩展写入扩展仓库数据库
     */
    public function save()
    {
        return Ec::transaction(function () {
            $this->runExtensionInstall();
            $this->installExtension();
            
            return true;
        });
    }
    
    /**
     * 执行扩展内的安装方法
     */
    protected function runExtensionInstall()
    {
        foreach ($this->getInstallExtension() as $app => $row) {
            foreach ($row as $infoInstance) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance->install();
            }
        }
    }
    
    /**
     * 把已经安装的扩展添加进数据库里
     */
    protected function installExtension()
    {
        $this->db = Instance::ensure($this->db, Connection::class);
        // 构建待写入数据库里的扩展配置数据
        $data = [];
        /**
         * 构建待写入数据库里的扩展配置数据
         *
         * @param ExtensionInfo $infoInstance
         * @param array         $append
         *
         * @return array
         */
        $build = function (ExtensionInfo $infoInstance, $append = []) {
            return array_merge([
                'unique_id'   => $infoInstance->getUniqueId(),
                'unique_name' => $infoInstance->getUniqueName(),
                'is_system'   => YesEnum::YES, // 默认安装的扩展标记为系统扩展
                'status'      => EnableEnum::ENABLE,
                'run'         => ExtensionInfo::RUN_MODULE_EXTENSION, // 默认安装的扩展运行模式为系统扩展
            ], $append);
        };
        foreach ($this->getInstallExtension() as $app => $row) {
            /** @var ExtensionInfo $infoInstance */
            foreach ($row as $uniqueName => $infoInstance) {
                switch ($infoInstance->getType()) {
                    case ExtensionInfo::TYPE_CONTROLLER:
                        /** @var ControllerInfo $infoInstance */
                        $data[ExtensionInfo::TYPE_CONTROLLER][] = $build($infoInstance, [
                            'module_id'     => $infoInstance->getModuleId(),
                            'controller_id' => $infoInstance->getId(),
                            'app'           => $app,
                        ]);
                        break;
                    case ExtensionInfo::TYPE_MODULE:
                        $data[ExtensionInfo::TYPE_MODULE][] = $build($infoInstance, [
                            'module_id' => $infoInstance->getId(),
                            'app'       => $app,
                        ]);
                        break;
                    case ExtensionInfo::TYPE_THEME:
                        $data[ExtensionInfo::TYPE_THEME][] = $build($infoInstance, [
                            'theme_id' => $infoInstance->getId(),
                            'app'      => $app,
                        ]);
                        break;
                }
            }
        }
        
        // 插入数据库
        foreach ($data as $table => $row) {
            $this->db->createCommand()
                     ->batchInsert($this->tables[$table], array_keys($row[0]), $data[$table])
                     ->execute();
        }
    }
    
    /**
     * 获取扩展模块信息类
     *
     * @return InstallExtensionInterface|ExtensionInfo
     * @throws Exception
     */
    public function getExtensionModuleInfo(): InstallExtensionInterface
    {
        $infoInstance = null;
        $repository = Ec::$service->getExtension()->getRepository();
        if ($repository->existsByCategory(ExtensionInfo::CATEGORY_EXTENSION, false)) {
            $uniqueName = $repository->getListGroupByCategory(false)[ExtensionInfo::CATEGORY_EXTENSION][0];
            $infoInstance = $repository->getLocalConfiguration()['backend'][$uniqueName];
        } else {
            throw new Exception('Extension module not installed.');
        }
        
        return $infoInstance;
    }
    
    /**
     * 初始化运行环境
     *
     * @param string $env
     */
    public function changeEnvironment($env = '')
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
        //执行
        ConsoleHelper::run(sprintf("%s --env={$environment} --overwrite=y",
            Yii::getAlias(ConsoleHelper::getCommander('init'))
        ), false);
    }
    
}