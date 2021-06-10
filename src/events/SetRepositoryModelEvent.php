<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\events;

use EngineCore\modules\installation\InstallHelperTrait;
use yii\base\Event;

/**
 * 配置扩展仓库模型的事件类
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SetRepositoryModelEvent extends Event
{

    /**
     * @var InstallHelperTrait
     */
    public $sender;

    /**
     * 配置扩展仓库模型
     *
     * @param self $event
     */
    static public function setModel($event)
    {
        if (null !== $info = $event->sender->getInstaller()->getExtensionModuleInfo()) {
            $info->setRepositoryModel();
        }
    }

}