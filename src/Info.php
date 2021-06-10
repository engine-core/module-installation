<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use EngineCore\enums\AppEnum;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\helpers\ArrayHelper;

class Info extends ModularityInfo
{

    protected
        $id = 'installation',
        $name = '安装向导',
        $category = self::CATEGORY_INSTALLATION;

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        $common = [
            'container' => [
                'definitions' => [
                    'EngineCore\modules\installation\helpers\InstallerHelper' => [
                        'defaultExtensions' => [
                            'engine-core/config-system' => [ // 系统核心配置
                                'version' => '*',
                                'app' => [AppEnum::COMMON],
                            ],
                            'engine-core/theme-basic' => [ // 基础主题
                                'version' => '*',
                                'app' => [AppEnum::BACKEND],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $backend = [
            'bootstrap' => [
                $this->getId(),
            ],
            'modules' => [
                $this->getId() => [
                    'class' => 'EngineCore\modules\installation\Module',
                    'on beforeAction' => ['EngineCore\modules\installation\events\SetRepositoryModelEvent', 'setModel'],
                    'as installed' => [
                        'class' => 'EngineCore\modules\installation\behaviors\InstalledBehavior',
                    ],
                    'controllerMap' => [
                        'common' => [
                            'class' => 'EngineCore\modules\installation\controllers\CommonController',
                            'as gotoCurrentStep' => [
                                'class' => 'EngineCore\modules\installation\behaviors\GoToCurrentStepBehavior',
                            ],
                        ],
                    ],
                ],
                'gridview' => [
                    'class' => '\kartik\grid\Module'
                ],
            ],
            'components' => [
                'i18n' => [
                    'translations' => [
                        'ec/modules/installation' => [
                            'class' => 'yii\\i18n\\PhpMessageSource',
                            'sourceLanguage' => 'en-US',
                            'basePath' => '@EngineCore/modules/installation/messages',
                            'fileMap' => [
                                'ec/modules/installation' => 'app.php',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $console = [
            'controllerMap' => [
                $this->getId() => [
                    'class' => 'EngineCore\modules\installation\console\InstallationController',
                    'on beforeAction' => ['EngineCore\modules\installation\events\SetRepositoryModelEvent', 'setModel'],
                ],
            ],
        ];

        return [
            AppEnum::BACKEND => ArrayHelper::merge($common, $backend),
            AppEnum::CONSOLE => ArrayHelper::merge($common, $console),
        ];
    }

}