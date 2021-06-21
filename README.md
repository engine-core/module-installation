# module-installation

包含web端和console端的安装向导模块。模块提供项目安装、卸载等操作。

## 使用方法：

### 有两种方式使用该扩展，如果是开发者自建的应用项目，按照以下步骤完成即可。

一、`@common/config/main-local.php`文件里添加以下配置：

```php
use EngineCore\extension\setting\SettingProviderInterface;

return [
    'container' => [
        'definitions' => [
            // 初始化安装助手类
            'EngineCore\modules\installation\helpers\InstallerHelper' => [
                /**
                 * 默认需要安装的扩展，格式参见
                 * @see \EngineCore\modules\installation\helpers\InstallerHelper::setDefaultExtensions()
                 */
                'defaultExtensions' => [
                    // https://github.com/engine-core/module-extension
                    'engine-core/module-extension' => [ // 用于管理系统扩展，建议下载安装
                        'app' => 'backend',
                    ],
                    // https://github.com/engine-core/controller-backend-site
                    'engine-core/controller-backend-site' => [ // 后台首页
                        'app' => 'backend',
                    ],
                ],
            ],
            // 默认采用文件方式存储系统设置数据
            'SettingProvider' => [
                'class' => 'EngineCore\extension\setting\FileProvider',
            ],
            // 默认采用文件方式存储系统菜单数据
            'MenuProvider' => [
                'class' => 'EngineCore\extension\menu\FileProvider',
            ],
        ],
    ],
    'params'     => [
        // 系统设置数据
        SettingProviderInterface::SETTING_KEY => [
            // 系统默认主题
            SettingProviderInterface::DEFAULT_THEME => [
                // https://github.com/engine-core/theme-basic
                'value' => 'engine-core/theme-basic',
                'extra' => 'engine-core/theme-basic:engine-core/theme-basic',
            ],
        ],
    ],
    // 数据库配置
    'components' => [
        'db' => [
            'class'               => 'yii\db\Connection',
            'dsn'                 => 'mysql:host=localhost;dbname=inOne',
            'username'            => 'root',
            'password'            => '',
            'charset'             => 'utf8',
            'tablePrefix'         => 'io_',
            'enableSchemaCache'   => false, // 关闭数据库元数据缓存
            'schemaCacheDuration' => 3600, // 数据库元数据缓存持续时间
        ],
    ],
];
```

二、`@backend/config/main-local.php`添加以下配置：
```php
    'controllerMap' => [
        /**
         * 安装向导控制器所需的其它配置会自动加载
         *
         * 已有的配置数据参见：
         * @see \EngineCore\modules\installation\Info::getConfig()
         *
         * 其他配置的自动加载是通过调用`\EngineCore\Ec::$service->getExtension()->entity()->loadConfig()`方法实现
         * @see \EngineCore\extension\entity\ExtensionEntity::loadConfig()
         * @see \EngineCore\modules\installation\console\InstallationController::init()
         */
        'installation' => [
            'class' => 'EngineCore\modules\installation\console\InstallationController',
        ],
    ],
```

三、`@console/config/mian-local.php`添加以下配置：
```php
    'controllerMap' => [
        /**
         * 安装向导控制器所需的其它配置会自动加载
         *
         * 已有的配置数据参见：
         * @see \EngineCore\modules\installation\Info::getConfig()
         *
         * 其他配置的自动加载是通过调用`\EngineCore\Ec::$service->getExtension()->entity()->loadConfig()`方法实现
         * @see \EngineCore\extension\entity\ExtensionEntity::loadConfig()
         * @see \EngineCore\modules\installation\console\InstallationController::init()
         */
        'installation' => [
            'class' => 'EngineCore\modules\installation\console\InstallationController',
        ],
    ],
```

四、在相应的应用里执行以下操作：
- `backend`应用：地址栏输入安装向导路由地址：`/installation`开始安装向导。
- `console`应用：命令行执行命令`./yii installation`开始安装向导。


### 如果开发者使用的是`inone`应用项目模板，仅需在命令行初始化安装环境即可，执行如下命令：
```bash
php init --env=Installation --overwrite=y
```
接着按照上述的第四个步骤执行安装向导即可。