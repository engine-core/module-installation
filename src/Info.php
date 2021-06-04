<?php
/**
 * @link      https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use EngineCore\enums\AppEnum;
use EngineCore\extension\repository\info\ModularityInfo;
use EngineCore\extension\setting\SettingProviderInterface;
use EngineCore\helpers\ArrayHelper;
use EngineCore\modules\installation\events\SetRepositoryModelEvent;

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
                            // 系统配置扩展
                            'engine-core/config-system' => [ // 系统核心配置
                                'version' => '*',
                                'app'     => [AppEnum::COMMON],
                            ],
                        ],
                    ],
                ],
            ],
            'params'    => [
                SettingProviderInterface::SETTING_KEY => [
                    SettingProviderInterface::DEFAULT_THEME => [ // 设置默认主题
                        'value' => 'engine-core/theme-basic',
                    ],
                ],
            ],
        ];
        $backend = [
            'bootstrap'  => [
                $this->getId(),
            ],
            'modules'    => [
                $this->getId() => [
                    'class'           => 'EngineCore\modules\installation\Module',
                    'on beforeAction' => [
                        new SetRepositoryModelEvent(),
                        'setModel',
                    ],
                    'as installed'    => [
                        'class' => 'EngineCore\modules\installation\behaviors\InstalledBehavior',
                    ],
                    'controllerMap'   => [
                        'common' => [
                            'class'              => 'EngineCore\modules\installation\controllers\CommonController',
                            'as gotoCurrentStep' => [
                                'class' => 'EngineCore\modules\installation\behaviors\GoToCurrentStepBehavior',
                            ],
                        ],
                    ],
                ],
                'gridview'     => [
                    'class' => '\kartik\grid\Module'
                    // enter optional module parameters below - only if you need to
                    // use your own export download action or custom translation
                    // message source
                    // 'downloadAction' => 'gridview/export/download',
                    // 'i18n' => []
                ],
            ],
            'components' => [
                'i18n' => [
                    'translations' => [
                        'ec/modules/installation' => [
                            'class'          => 'yii\\i18n\\PhpMessageSource',
                            'sourceLanguage' => 'en-US',
                            'basePath'       => '@EngineCore/modules/installation/messages',
                            'fileMap'        => [
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
                    'class'           => 'EngineCore\modules\installation\console\InstallationController',
                    'on beforeAction' => [
                        new SetRepositoryModelEvent(),
                        'setModel',
                    ],
                ],
            ],
        ];
        
        return [
            AppEnum::BACKEND => ArrayHelper::merge($common, $backend),
            AppEnum::CONSOLE => ArrayHelper::merge($common, $console),
        ];
    }
    
}