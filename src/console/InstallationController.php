<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\console;

use EngineCore\console\Controller;
use EngineCore\console\MigrationTrait;
use EngineCore\Ec;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\modules\installation\InstallHelperTrait;
use EngineCore\modules\installation\models\DatabaseForm;
use EngineCore\modules\installation\models\ExtensionManageForm;
use EngineCore\modules\installation\models\FinishForm;
use EngineCore\modules\installation\Module;
use yii\{
    console\ExitCode, helpers\Console
};
use Yii;

/**
 * Class InstallationController
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class InstallationController extends Controller
{
    
    use MigrationTrait, InstallHelperTrait;
    
    /**
     * {@inheritdoc}
     */
    public $defaultAction = 'install';
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->initialize();
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
            // 初始化环境为`Installation`
            $this->getInstaller()->changeEnvironment('ins');
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
            $this->stdout("====== This operation can only be performed in a development environment. ======\n", Console::FG_YELLOW);
            
            return ExitCode::UNSPECIFIED_ERROR;
        }
        // 检查是否已经安装
        if ($this->getInstaller()->isLocked()) {
            // 安装成功，请不要重复安装
            $this->stdout("====== The installation is successful. Please do not repeat the installation. ======\n", Console::FG_YELLOW);
            
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // 授权协议
        $license = file_get_contents($this->getInstaller()->getLicenseFile());
        if (!$this->confirm(Console::markdownToAnsi($license), true)) {
            $this->stdout("Setup wizard exited.\n", Console::FG_YELLOW);
            
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // 测试数据库连接
        /** @var DatabaseForm $model */
        $model = Ec::createObject(DatabaseForm::class, [
            $this->getInstaller(),
        ], DatabaseForm::class);
        $model->loadDefaultValues();
        $res = $model->testDb();
        if (false === $res['status']) {
            $this->stdout("Test database connection failed.\n", Console::FG_RED);
            $this->stdout("{$res['info']}.\n", Console::FG_RED);
            
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $this->stdout("====== Test database connection successful. ====== \n", Console::FG_YELLOW);
        
        // 验证已选扩展是否满足依赖关系
        if (ExitCode::OK !== $this->validateCheckedExtensions()) {
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // 准备安装的扩展
        $arr = [];
        foreach ($this->getInstaller()->getUnInstallExtension() as $row) {
            /** @var ExtensionInfo $infoInstance */
            foreach ($row as $uniqueName => $infoInstance) {
                if (!isset($arr[$uniqueName])) {
                    $arr[$uniqueName]['version'] = $infoInstance->getConfiguration()->getVersion();
                    $arr[$uniqueName]['app'][] = $infoInstance->getApp();
                } elseif (!in_array($infoInstance->getApp(), $arr[$uniqueName]['app'])) {
                    $arr[$uniqueName]['app'][] = $infoInstance->getApp();
                }
            }
        }
        if (empty($arr)) {
            $this->stdout("\n====== There are no new extensions to install ====== \n\n", Console::FG_YELLOW);
        } else {
            $this->stdout("\n====== The following extensions will be installed ====== \n\n", Console::FG_YELLOW);
            $str = Console::renderColoredString("%y%s%y%n require version %y%s%y%n and install it in the [%y%s%y%n] application.\n");
            foreach ($arr as $uniqueName => $row) {
                $this->stdout(sprintf($str, $uniqueName, $row['version'], implode(', ', $row['app'])));
            }
            
            // 确认是否继续
            if (!$this->confirm("\nWould you like to continue?", true)) {
                $this->stdout("Setup wizard exited.\n", Console::FG_YELLOW);
                
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }
        
        // 执行安装
        /** @var FinishForm $model */
        $model = Ec::createObject(FinishForm::class, [
            $this->getInstaller(),
        ], FinishForm::class);
        if (false === $model->save()) {
            $this->stdout("Installation failed.\n", Console::FG_RED);
            
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        // 安装成功
        $str = "====== Installation is successful. Welcome to use %s. ======\n\n";
        $appName = Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::SITE_TITLE);
        $this->stdout(sprintf($str, $appName ?: Yii::$app->name), Console::FG_YELLOW);
        
        return ExitCode::OK;
    }
    
    /**
     * Refresh the extended cache that needs to be installed.
     */
    public function actionFlushInstallExtension()
    {
        $this->stdout("====== Successfully refreshed the extension cache that needs to be installed ======\n\n", Console::FG_YELLOW);
        $this->getInstaller()->cache()->delete(Module::CACHE_CHECKED_EXTENSION);
    }
    
    /**
     * Reset the installed extension management category
     */
    public function actionResetExtensionCategory()
    {
        $this->stdout("====== Successfully reset the installed extension management category ======\n\n", Console::FG_YELLOW);
        $this->getInstaller()->cache()->delete(Module::CACHE_EXTENSION_CATEGORY);
    }
    
    /**
     * Validate the dependency of the selected extension.
     *
     * @return int
     */
    protected function validateCheckedExtensions()
    {
        $localConfiguration = Ec::$service->getExtension()->getRepository()->getLocalConfiguration();
        if (empty($localConfiguration)) {
            $this->stderr("The following extension is necessary:\n", Console::FG_RED, Console::UNDERLINE);
            
            $str = Console::renderColoredString(" - %y'%s'%y%n requires version %y%s%y%n.\n");
            foreach ($this->getInstaller()->getCheckedExtensions() as $uniqueName => $row) {
                $this->stdout(sprintf($str, $uniqueName, $row['version']));
            }
            $this->stdout("\nEmpty extension, please download the required extension first.\n", Console::FG_YELLOW);
        } else {
            /** @var ExtensionManageForm $model */
            $model = Ec::createObject(ExtensionManageForm::class, [
                $this->getInstaller(),
            ], ExtensionManageForm::class);
            /**
             * 自选扩展，目前有两个途径可以设置该值：
             *
             * 1、config.php配置文件设置默认值。
             * @see \EngineCore\modules\installation\helpers\InstallerHelper::setDefaultExtensions()
             * 2、通过web端自选提交。
             * @see \EngineCore\modules\installation\dispatches\Basic\Common\ExtensionManager::run() 初步自选扩展
             * @see \EngineCore\modules\installation\dispatches\Basic\Common\ExtensionDetail::run() 自动包含被依赖的扩展
             */
            $selfChecked = $this->getInstaller()->getCheckedExtensions();
            $selfChecked = $model->parseExtension($selfChecked);
            
            // 检测需要安装的扩展合法性
            if ($model->load($selfChecked, '') && $model->save()) {
                // 验证扩展是否满足扩展依赖检测
                if (false === $model->getInstaller()->validate()) {
                    $this->stdout("====== Please resolve the following extended dependencies ======\n", Console::FG_YELLOW);
                    // 提示下载扩展
                    $showDownload = function () {
                        $download = Ec::$service->getExtension()->getDependent()->getDownload();
                        if (!empty($download)) {
                            $this->stderr("The following extension is necessary:\n", Console::FG_RED, Console::UNDERLINE);
                            $parentStr = " - 请求主体：%s\n";
                            $versionStr = " - 请求版本：%s\n";
                            foreach ($download as $uniqueName => $row) {
                                $this->stdout($uniqueName . "\n", Console::FG_YELLOW);
                                foreach ($row as $k => $v) {
                                    $this->stdout(sprintf($parentStr, ($v['extensions'] ? implode(' -> ', $v['extensions']) : '安装向导')), Console::FG_YELLOW);
                                    $this->stdout(sprintf($versionStr, $v['requireVersion']), Console::FG_YELLOW);
                                }
                            }
                            $this->stdout("Lack of necessary extension, please download the required extension.\n\n", Console::FG_RED);
                        }
                    };
                    // 提示扩展版本冲突
                    $showConflict = function () {
                        $conflict = Ec::$service->getExtension()->getDependent()->getConflict();
                        if (!empty($conflict)) {
                            $this->stderr("There are version conflicts for the following extensions:\n", Console::FG_RED, Console::UNDERLINE);
                            $str = Console::renderColoredString(" - %y'%s'%y%n requires version %y%s%y%n and local version is %y%s%y%n.\n");
                            foreach ($conflict as $uniqueName => $row) {
                                foreach ($row['items'] as $item) {
                                    $chain = $item['extensions'] ?: ['安装向导'];
                                    $chain[] = $uniqueName;
                                    $this->stdout(sprintf($str, implode(' -> ', $chain), $item['requireVersion'], $row['localVersion']));
                                }
                            }
                            $this->stdout("Version conflict, please solve the conflict problem.\n\n", Console::FG_RED);
                        }
                    };
                    // 提示无限循环依赖
                    $showCircular = function () {
                        $circular = Ec::$service->getExtension()->getDependent()->getCircular();
                        if (!empty($circular)) {
                            $this->stderr("Circular dependency is detected for the following extension:\n", Console::FG_RED, Console::UNDERLINE);
                            foreach ($circular as $uniqueName => $row) {
                                $this->stdout($uniqueName . ': ' . implode(' -> ', $row) . "\n\n", Console::FG_YELLOW);
                            }
                        }
                    };
                    
                    $showDownload();
                    $showConflict();
                    $showCircular();
                } else {
                    return ExitCode::OK;
                }
            } else {
                if ($model->hasErrors()) {
                    $this->stderr(Console::errorSummary($model, [
                            'showAllErrors' => true,
                        ]) . PHP_EOL, Console::FG_RED);
                } else {
                    $this->stderr("====== There are no any extensions can be installed ====== \n\n", Console::FG_RED);
                }
            }
        }
        
        return ExitCode::UNSPECIFIED_ERROR;
    }
    
    /**
     * Delete the install lock file.
     */
    private function deleteLockFile()
    {
        $this->stdout("* Delete the install lock file.\n", Console::FG_GREEN);
        $this->getInstaller()->unLock();
    }
    
    /**
     * Delete the extension configuration files.
     */
    private function deleteConfigFile()
    {
        $envService = Ec::$service->getExtension()->getEnvironment();
        $this->stdout("* Delete the extension configuration files.\n", Console::FG_GREEN);
        $files = $envService->removeConfigFiles();
        if (!empty($files['success'])) {
            $this->stdout(" " . (count($files['success'])) . " configuration files are deleted successfully:\n", Console::FG_YELLOW);
            foreach ($files['success'] as $file) {
                $this->stdout(" {$file}\n");
            }
        }
        if (!empty($files['fail'])) {
            $this->stdout(" " . (count($files['fail'])) . " configuration files are deleted failed:\n", Console::FG_YELLOW);
            foreach ($files['fail'] as $file) {
                $this->stdout(" {$file}\n");
            }
        }
        
        $this->stdout("\n");
    }
    
}