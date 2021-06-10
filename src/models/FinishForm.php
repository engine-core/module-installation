<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\models;

use EngineCore\Ec;
use Yii;
use yii\console\Application;
use yii\helpers\Console;

/**
 * Class FinishForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class FinishForm extends BaseForm
{

    /**
     * 保存，必须在执行前确保已经进行了依赖关系检测，否则会因没有经过检测而导致没有有效的可安装扩展，
     * 最后无法正确创建配置文件
     * @see \EngineCore\services\Extension\Dependent::validate()
     *
     * @return bool
     */
    public function save(): bool
    {
        if ($this->getInstaller()->save()) {
            // 创建扩展配置文件，只生成已经安装扩展的配置文件
            $this->flushConfigFiles();

            // 刷新缓存
            Ec::$service->getExtension()->getRepository()->clearCache();
            Ec::$service->getExtension()->getDependent()->clearCache();

            // 创建安装锁定文件
            if ($this->getInstaller()->lock()) {
                // 初始化环境为`Development`
                $this->getInstaller()->changeEnvironment();

                return true;
            }
        }

        return false;
    }

    /**
     * 创建扩展配置文件，只生成已经安装扩展的配置文件
     * @see \EngineCore\console\controllers\ExtensionController::actionFlushConfigFiles()
     */
    protected function flushConfigFiles()
    {
        if (Yii::$app instanceof Application) {
            $consoleController = Yii::$app->controller;
            $consoleController->stdout("====== Creating the extension config files ======\n", Console::FG_YELLOW);
            $files = Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
            if (!empty($files['success'])) {
                $consoleController->stdout(" " . (count($files['success'])) . " configuration files are created successfully:\n", Console::FG_YELLOW);
                foreach ($files['success'] as $file) {
                    $consoleController->stdout(" {$file}\n");
                }
            }
            if (!empty($files['fail'])) {
                $consoleController->stdout(" " . (count($files['fail'])) . " configuration files are created failed:\n", Console::FG_YELLOW);
                foreach ($files['fail'] as $file) {
                    $consoleController->stdout(" {$file}\n");
                }
            }
            $consoleController->stdout("\n");
            if (!Ec::$service->getExtension()->getRepository()->hasModel()) {
                $consoleController->stdout("The extension model class is not set.\n\n", Console::FG_RED);
            }
        } else {
            Ec::$service->getExtension()->getEnvironment()->flushConfigFiles();
        }
    }

}