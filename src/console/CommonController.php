<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\console;

use EngineCore\console\Controller;
use EngineCore\console\MigrationTrait;
use EngineCore\Ec;
use EngineCore\enums\EnableEnum;
use EngineCore\enums\YesEnum;
use EngineCore\extension\repository\info\ControllerInfo;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\repository\info\ThemeInfo;
use EngineCore\helpers\ConsoleHelper;
use EngineCore\modules\installation\models\ExtensionForm;
use yii\{
    base\InvalidConfigException, console\Exception, console\ExitCode, helpers\ArrayHelper, helpers\Console
};
use Yii;

/**
 * Class CommonController
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class CommonController extends Controller
{
    
    use MigrationTrait;
    
    /**
     * @inheritdoc
     */
    public $defaultAction = 'install';
    
    /**
     * @var \EngineCore\modules\installation\Module
     */
    public $module;
    
    public function actionTest()
    {
        /** @var ExtensionForm $model */
        $model = Ec::createObject(ExtensionForm::class, [], ExtensionForm::class);
        $model->validate();
        $this->stdout($this->dump($model->getFirstErrors()));
//        Yii::$app->runAction('migrate/fresh');
    }
    
    protected function dump($d)
    {
        return Ec::dump($d,null,true,null,false);
    }
    
    /**
     * UnInstall the project.
     *
     * @return int the status of the action execution. 0 means normal, other values mean abnormal.
     */
    public function actionUninstall()
    {
        // 仅开发环境下可执行该操作
        if (!YII_ENV_DEV) {
            $this->stdout("====== This operation can only be performed in a development environment. ======\n",
                Console::FG_YELLOW);
            
            return ExitCode::OK;
        }
        if ($this->confirm(
            "Are you sure you want to uninstall the project?\nAll data will be lost irreversibly!")
        ) {
            // 清空数据库
            $this->truncateDatabase();
            // 删除缓存
            Yii::$app->runAction('cache/flush-all');
            Yii::$app->runAction('cache/flush-schema', ['interactive' => false]);
            // 删除扩展配置文件
            $this->deleteConfigFile();
            // 删除锁定文件
            $this->deleteLockFile();
            // 初始化环境为`Development`
            $this->stdout("\n");
            $this->changeEnvironment('ins');
            // 卸载完成
            $this->stdout("====== Uninstall is successful. ======\n", Console::FG_YELLOW);
        } else {
            $this->stdout('Action was cancelled by user. Nothing has been performed.');
        }
        $this->stdout("\n");
        
        return ExitCode::OK;
    }
    
    /**
     * Start the installation of the project.
     *
     * @return int
     */
    public function actionInstall()
    {
        // 仅开发环境下可执行该操作
        if (!YII_ENV_DEV) {
            $this->stdout("====== This operation can only be performed in a development environment. ======\n",
                Console::FG_YELLOW);
            
            return ExitCode::OK;
        }
        // 检查是否已经安装
        if ($this->module->installer->isLocked) {
            // 安装成功，请不要重复安装
            $this->stdout("====== The installation is successful. Please do not repeat the installation. ======\n",
                Console::FG_YELLOW);
            
            return ExitCode::OK;
        }
        
        $this->checkLocalExtensionConfig();
        
        // 安装扩展数据库迁移
        $this->setExtensionMigrationPath();
        $this->stdout("====== Run migration ======\n\n", Console::FG_YELLOW);
        
        $code = Yii::$app->runAction('migrate/up');
        if ($code == ExitCode::UNSPECIFIED_ERROR) {
            return $code;
        }
        
        // 执行扩展内安装方法
        $this->_runExtensionInstall();
        
        // 把已安装的扩展写入数据库
        $this->installExtensionInDb();
        
        // 配置扩展仓库模型，为下一步【创建扩展配置文件】提供已经安装扩展的数据支持
        $this->module->installer->extensionModuleInfo->setRepositoryModel();
        
        // 创建扩展配置文件，只生成已经安装扩展的配置文件
        Yii::$app->runAction('extension/flush-config-files');
        
        // 初始化环境为`Development`
        $this->changeEnvironment();
        
        // 创建安装锁定文件
        $this->module->installer->lock;
        
        // 安装成功
        $this->stdout("====== Installation is successful. Welcome to use EngineCore. ======\n\n", Console::FG_YELLOW);
        
        // 刷新缓存
        Ec::$service->getExtension()->clearCache();
        
        return ExitCode::OK;
    }
    
    /**
     * @var array Extended configuration information that you need to install.
     */
    private $_extensionConfig = [];
    
    /**
     * 初始化运行环境
     *
     * @param string $env
     */
    protected function changeEnvironment($env = 'dev')
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
        $this->stdout("====== Initialize the environment as '{$environment}' ======\n\n", Console::FG_YELLOW);
        $this->module->installer->changeEnvironment($env);
    }
    
    /**
     * Whether the local extended configuration information is valid.
     *
     * @throws InvalidConfigException
     */
    protected function checkLocalExtensionConfig()
    {
        $localConfiguration = Ec::$service->getExtension()->getRepository()->getLocalConfiguration();
        if (empty($localConfiguration)) {
            $this->stderr("The following extension is necessary:\n\n", Console::FG_RED, Console::UNDERLINE);
            
            foreach ($this->module->installer->getExtensions() as $uniqueName => $row) {
                $this->stdout($uniqueName . " require version {$row['version']}.\n", Console::FG_YELLOW);
            }
            throw new InvalidConfigException("\nEmpty extension, please download the required extension first.\n");
        } else {
            $data = $this->module->installer->getDependenciesStatus();
            
            
            $res = Ec::$service->getExtension()->getDependent()->getDependenciesStatus(
                $this->module->installer->defaultExtensions,
                'installation'
            );
            $data = Ec::$service->getExtension()->getDependent()->getData();
            if (false == $res) {
                // 提示下载扩展
                if (!empty($data['download'])) {
                    $this->stderr("The following extension is necessary:\n\n", Console::FG_RED, Console::UNDERLINE);
                    foreach ($data['download'] as $extension) {
                        $this->stdout($extension . "\n", Console::FG_YELLOW);
                    }
                    $this->stdout("\nLack of necessary extension, please download the
                    required extension first.\n", Console::FG_RED);
                }
                // 提示扩展版本冲突
                if (!empty($data['conflict'])) {
                    $this->stderr("Please solve the following extended version dependency:\n\n",
                        Console::FG_RED, Console::UNDERLINE);
                    foreach ($data['conflict'] as $uniqueName => $item) {
                        $localVersion = ArrayHelper::remove($item, 'localVersion');
                        $this->stdout("The current version of the '{$uniqueName}' extension is {$localVersion} " .
                            "and the following extensions exist in the version conflict.\n");
                        foreach ($item as $uName => $needVersion) {
                            $this->stdout(" - '" . $uName . "' need '{$needVersion}' version.\n",
                                Console::FG_YELLOW);
                        }
                    }
                    $this->stdout("\nVersion conflict, please solve the conflict problem first.\n",
                        Console::FG_RED);
                }
                // 循环依赖
                if (!empty($data['circular'])) {
                    $this->stderr("Circular dependency is detected for the following extension:\n\n",
                        Console::FG_RED, Console::UNDERLINE);
                    foreach ($data['circular'] as $uniqueName => $config) {
                        $this->stdout("{$config}\n");
                    }
                }
                if (!empty($data['download']) || !empty($data['conflict']) || !empty($data['circular'])) {
                    exit(ExitCode::UNSPECIFIED_ERROR);
                }
            }
            $this->_extensionConfig = $data['passed'];
        }
    }
    
    /**
     * Set the extended migration path
     */
    protected function setExtensionMigrationPath()
    {
        foreach ($this->_extensionConfig as $uniqueName => $config) {
            /** @var ControllerInfo|ModularityInfo $infoInstance */
            $infoInstance = $config['infoInstance'];
            if ($infoInstance->hasMethod('getMigrationPath')) {
                $this->migrationPath[] = $infoInstance->getMigrationPath();
            }
        }
    }
    
    /**
     * Install extensions into the database.
     */
    protected function installExtensionInDb()
    {
        // 构建待写入数据库里的扩展配置数据
        $data = [];
        $array = function ($infoInstance, $append = []) {
            /** @var ControllerInfo|ModularityInfo|ThemeInfo $infoInstance */
            return array_merge([
                'id'             => $infoInstance->getUniqueId(),
                'extension_name' => $infoInstance->getUniqueName(),
                'is_system'      => YesEnum::YES, // 默认安装的扩展标记为系统扩展
                'status'         => EnableEnum::ENABLE,
                'run'            => ExtensionInfo::RUN_MODULE_EXTENSION, // 默认安装的扩展运行模式为系统扩展
            ], $append);
        };
        foreach ($this->_extensionConfig as $uniqueName => $config) {
            /** @var ControllerInfo|ModularityInfo|ThemeInfo $infoInstance */
            $infoInstance = $config['infoInstance'];
            switch (true) {
                case is_subclass_of($infoInstance, ControllerInfo::class):
                    $data['controller'][] = $array($infoInstance, [
                        'module_id'     => $infoInstance->getModuleId(),
                        'controller_id' => $infoInstance->id,
                    ]);
                    break;
                case is_subclass_of($infoInstance, ModularityInfo::class):
                    $data['module'][] = $array($infoInstance, [
                        'module_id' => $infoInstance->id,
                    ]);
                    break;
                case is_subclass_of($infoInstance, ThemeInfo::class):
                    $data['theme'][] = $array($infoInstance);
                    break;
            }
        }
        foreach ($data as $table => $row) {
            $this->db->createCommand()
                     ->batchInsert($this->module->installer->tables[$table], array_keys($row[0]), $data[$table])
                     ->execute();
        }
    }
    
    /**
     * Execution extension internal installation method.
     */
    private function _runExtensionInstall()
    {
        if (!empty($this->_extensionConfig)) {
            $this->stdout("\n====== Run extended internal installation method ======\n", Console::FG_YELLOW);
            foreach ($this->_extensionConfig as $uniqueName => $config) {
                /** @var ExtensionInfo $infoInstance */
                $infoInstance = $config['infoInstance'];
                Console::startProgress(0, 100, 'Running: ' . get_class($infoInstance) . '::install()', 100);
                if ($infoInstance->install()) {
                    for ($n = 1; $n <= 100; $n++) {
                        Console::updateProgress($n, 100);
                    }
                    Console::endProgress(' ===> ');
                    $this->stdout('successful', Console::FG_GREEN);
                } else {
                    Console::updateProgress(100, 100);
                    Console::endProgress(' ===> ');
                    $this->stdout('failed', Console::FG_RED);
                }
                $this->stdout("\n");
            }
            $this->stdout("\n");
        }
    }
    
    /**
     * Delete the install lock file.
     */
    protected function deleteLockFile()
    {
        $this->stdout("* Delete the install lock file.\n", Console::FG_GREEN);
        $this->module->installer->unLock;
    }
    
    /**
     * Delete the extension configuration files.
     */
    protected function deleteConfigFile()
    {
        $envService = Ec::$service->getExtension()->getEnvironment();
        $this->stdout("* Delete the extension configuration files.\n", Console::FG_GREEN);
        $this->stdout(" " . (count($envService->getConfigFileList()))
            . " configuration files are deleted:\n", Console::FG_YELLOW);
        foreach ($envService->removeConfigFiles() as $file) {
            $this->stdout(" {$file}\n");
        }
        $this->stdout("\n");
    }
    
}