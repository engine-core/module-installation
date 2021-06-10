<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use EngineCore\base\Modularity;
use yii\base\BootstrapInterface;

/**
 * Class Module
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class Module extends Modularity implements BootstrapInterface
{

    use InstallHelperTrait;

    /**
     * @var string 缓存步骤数据常量
     */
    const CACHE_STEP = 'installation_step_cache';

    /**
     * @var string 缓存已经选择的扩展常量
     */
    const CACHE_CHECKED_EXTENSION = 'checked_extensions';

    /**
     * @var string 缓存已经安装的扩展管理分类的扩展名
     */
    const CACHE_EXTENSION_CATEGORY = 'extension_category';

    public $layout = 'main';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->initialize();
    }

    /**
     * {@inheritDoc}
     */
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            [
                'class' => 'yii\web\GroupUrlRule',
                'prefix' => $this->id,
                'rules' => [
                    'welcome' => 'common/index',
                    '<action>' => 'common/<action>',
                ],
            ],
        ], false);
    }

}