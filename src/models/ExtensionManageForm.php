<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\models;

use EngineCore\Ec;
use EngineCore\enums\AppEnum;
use EngineCore\extension\installation\CoreConfigInterface;
use EngineCore\extension\installation\ExtensionInterface;
use EngineCore\extension\repository\info\ExtensionInfo;
use EngineCore\helpers\ArrayHelper;
use Yii;

/**
 * Class ExtensionManageForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionManageForm extends BaseForm
{

    /**
     * @var array 需要安装的扩展
     */
    public $extension;

    /**
     * @var array 扩展将要被安装到的应用
     */
    public $app;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['extension', 'required', 'message' => Yii::t('ec/modules/installation',
                'Please select the extension you want to install.'),
            ],
            ['extension', 'checkExtension'],
            ['app', 'checkApp'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            'extension', 'app',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'extension' => Yii::t('ec/modules/installation', 'Extension'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load($data, $formName = null)
    {
        // 加载默认需要安装和已经安装的扩展数据
        $scope = $formName === null ? $this->formName() : $formName;
        $disabledExtension = $this->parseExtension($this->getInstaller()->getDisabledExtensions());
        $data = ArrayHelper::merge($data, ($scope === '')
            ? $disabledExtension
            : [$scope => $disabledExtension]
        );
        if (!empty($data)) {
            // 数据去重
            $uniqueFunc = function ($data) {
                $data['extension'] = array_unique($data['extension']);
                foreach ($data['app'] as $uniqueName => $apps) {
                    $data['app'][$uniqueName] = array_unique($apps);
                }

                return $data;
            };
            $data = ($scope === '')
                ? $uniqueFunc($data)
                : [$scope => $uniqueFunc($data[$scope])];
        }

        return parent::load($data, $formName);
    }

    /**
     * 保存
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $configuration = Ec::$service->getExtension()->getRepository()->getFinder()->getConfiguration();
            $disabledExtension = $this->getInstaller()->getDisabledExtensions();
            // 只保存新的自选数据
            $data = [];
            foreach ($this->extension as $uniqueName) {
                // 存在新的自选扩展数据
                if (!isset($disabledExtension[$uniqueName])) {
                    $data[$uniqueName] = [
                        'version' => isset($configuration[$uniqueName]) ? $configuration[$uniqueName]->getVersion() : '*',
                        'app' => $this->app[$uniqueName],
                    ];
                } else {
                    foreach ($this->app[$uniqueName] as $app) {
                        // 存在新的自选扩展应用数据
                        if (!in_array($app, $disabledExtension[$uniqueName]['app'])) {
                            $data[$uniqueName]['version'] = $disabledExtension[$uniqueName]['version'];
                            $data[$uniqueName]['app'][] = $app;
                        }
                    }
                }
            }

            $this->getInstaller()->setCheckedExtensions($data);

            return true;
        }

        return false;
    }

    /**
     * 解析扩展数据，转换为模型内属性格式的数组
     *
     * @param array $extensions
     *
     * @see \EngineCore\services\extension\Dependent::normalize()
     *
     * @return array
     * ```php
     * [
     *  'extension' => [],
     *  'app' => [
     *      {$uniqueName} => [
     *          {$app}
     *      ],
     *  ],
     * ]
     * ```
     */
    public function parseExtension(array $extensions): array
    {
        $data = [];
        foreach ($extensions as $uniqueName => $row) {
            $data['extension'][] = $uniqueName;
            if (isset($row['app'])) {
                $data['app'][$uniqueName] = $row['app'];
            }
        }

        return $data;
    }

    /**
     * 检查需要安装的扩展
     *
     * @param string $attribute
     */
    public function checkExtension($attribute)
    {
        $configuration = Ec::$service->getExtension()->getRepository()->getFinder()->getConfiguration();
        $config = Ec::$service->getExtension()->getRepository()->getLocalConfiguration();
        $categoryExtensionNum = 0; // 扩展管理分类数量
        $typeTheme = 0; // 主题数量
        $backendHomeNum = 0; // 后台主页扩展数量
        $systemConfigNum = 0; // 系统配置扩展数量

        foreach ($this->extension as $key => $uniqueName) {
            // 本地不存在的扩展暂不剔除，用于提示下载扩展
            if (!isset($configuration[$uniqueName])) {
                continue;
            }
            $app = $configuration[$uniqueName]->getApp();
            $app = array_shift($app);
            /** @var ExtensionInfo $instance */
            $instance = $config[$app][$uniqueName];
            switch ($instance->getCategory()) {
                case ExtensionInfo::CATEGORY_INSTALLATION:
                    // 不能安装安装向导分类的扩展
                    $this->addError($attribute, Yii::t('ec/modules/installation',
                        'The extension belongs to the installation wizard extension category and cannot be installed.', [
                            'extension' => $uniqueName,
                        ]));
                    break;
                case ExtensionInfo::CATEGORY_EXTENSION:
                    if (!($instance instanceof ExtensionInterface)) {
                        $this->addError($attribute, Yii::t('ec/modules/installation',
                            'Extension management module must implement the interface of extension management installation wizard.', [
                                'extension' => $uniqueName,
                                'interface' => ExtensionInterface::class,
                            ]));
                    } else {
                        ++$categoryExtensionNum;
                        if (isset($this->app[$uniqueName]) && !in_array(AppEnum::BACKEND, $this->app[$uniqueName])) {
                            $this->addError($attribute, Yii::t('ec/modules/installation',
                                'Backend application environment must be preselected for extension management module.', [
                                    'extension' => $uniqueName,
                                    'backend_app' => AppEnum::value(AppEnum::BACKEND),
                                ]));
                        }
                    }
                    break;
                case ExtensionInfo::CATEGORY_BACKEND_HOME:
                    ++$backendHomeNum;
                    break;
            }
            if (ExtensionInfo::TYPE_THEME === $instance->getType()) {
                ++$typeTheme;
            }
            if ($instance instanceof CoreConfigInterface) {
                ++$systemConfigNum;
            }
            if (!isset($this->app[$uniqueName])) {
                $this->addError($attribute, Yii::t('ec/modules/installation',
                    'Please select the application environment for the extension you want to install.', [
                        'extension' => $uniqueName,
                    ]));
            }
        }
        // 系统核心配置扩展
        if ($systemConfigNum < 1) {
            $this->addError($attribute, Yii::t('ec/modules/installation',
                'At least one extension of the system core configuration needs to be installed.'));
        }

        // 扩展管理分类
        if ($categoryExtensionNum > 1) {
            $this->addError($attribute, Yii::t('ec/modules/installation',
                'You can install up to one extension of the extension management category.'));
        }
        if ($categoryExtensionNum < 1) {
            $this->addError($attribute, Yii::t('ec/modules/installation',
                'At least one extension of the extension management category needs to be installed.'));
        }

        // 后台主页
        if ($backendHomeNum > 1) {
            $this->addError($attribute, Yii::t('ec/modules/installation',
                'You can install at most one extension of backend home page category.'));
        }
        if ($backendHomeNum < 1) {
            $this->addError($attribute, Yii::t('ec/modules/installation',
                'You need to install at least one extension of the backend home page category.'));
        }

        // 主题类型
        if ($typeTheme < 1) {
            $this->addError($attribute, Yii::t('ec/modules/installation',
                'At least one extension of the theme type needs to be installed.'));
        }
    }

    /**
     * 检查扩展能被安装的应用环境
     *
     * @param string $attribute
     */
    public function checkApp($attribute)
    {
        if (!$this->hasErrors()) {
            $configuration = Ec::$service->getExtension()->getRepository()->getFinder()->getConfiguration();
            foreach ($this->app as $uniqueName => $row) {
                // 本地不存在的扩展暂不剔除，用于提示下载扩展
                if (!isset($configuration[$uniqueName])) {
                    continue;
                }
                foreach ($row as $app) {
                    // 剔除没有选择的扩展的应用环境数据
                    if (!in_array($uniqueName, $this->extension)) {
                        unset($this->app[$uniqueName]);
                        continue;
                    }
                    if (!in_array($app, $configuration[$uniqueName]->getApp())) {
                        $app = AppEnum::value($app) ?: $app;
                        $this->addError($attribute, Yii::t('ec/modules/installation',
                            'The extension cannot be installed in the app.', [
                                'extension' => $uniqueName,
                                'app' => $app,
                            ]));
                    }
                }
            }
        }
    }

}