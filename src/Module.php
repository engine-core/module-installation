<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation;

use EngineCore\base\Modularity;
use EngineCore\Ec;
use EngineCore\modules\installation\helpers\InstallerHelper;
use Yii;
use yii\base\BootstrapInterface;

/**
 * Class Module
 *
 * @property InstallerHelper $installer
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Module extends Modularity implements BootstrapInterface
{
    
    /**
     * @var string 缓存步骤数据常量
     */
    const CACHE_STEP = 'installation_step_cache';
    
    /**
     * @var string 缓存配置数据常量
     */
    const CACHE_CONFIG = 'installation_config_cache';
    
    public $layout = 'main';
    
    /**
     * @var InstallerHelper
     */
    private $_installer;
    
    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        
        // 加载最新扩展别名，确保能够完全加载本地系统扩展
        foreach (Ec::$service->getExtension()->getRepository()->getFinder()->getAliases() as $ns => $path) {
            Yii::setAlias($ns, $path);
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\web\Application) {
            $app->getUrlManager()->addRules([
                [
                    'class'  => 'yii\web\GroupUrlRule',
                    'prefix' => $this->id,
                    'rules'  => [
                        'welcome'  => 'common/index',
                        '<action>' => 'common/<action>',
                    ],
                ],
            ], false);
        } elseif ($app instanceof \yii\console\Application) {
            $app->controllerMap[$this->id] = [
                'class'  => 'EngineCore\modules\installation\console\CommonController',
                'module' => $this,
            ];
        }
    }
    
    /**
     * 获取安装助手类
     *
     * @return InstallerHelper
     */
    public function getInstaller()
    {
        if (null === $this->_installer) {
            $this->setInstaller(InstallerHelper::class);
        }
        
        return $this->_installer;
    }
    
    /**
     * 设置安装助手类
     *
     * @param string|array|callable $installer
     */
    public function setInstaller($installer)
    {
        $this->_installer = Ec::createObject($installer, [], InstallerHelper::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        if ($this->installer->isLocked && Yii::$app instanceof \yii\web\Application) {
            Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
            Yii::$app->end();
        }
        
        if (!parent::beforeAction($action)) {
            return false;
        }
        
        return true;
    }
    
}