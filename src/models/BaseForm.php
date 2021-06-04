<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\models;

use EngineCore\base\Model;
use EngineCore\modules\installation\helpers\InstallerHelper;

/**
 * Class BaseForm
 *
 * @property InstallerHelper $installer
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class BaseForm extends Model
{
    
    /**
     * @var InstallerHelper 安装助手类
     */
    private $_installer;
    
    /**
     * BaseForm constructor.
     *
     * @param InstallerHelper $installer
     * @param array           $config
     *
     * @author E-Kevin <e-kevin@qq.com>
     */
    public function __construct(InstallerHelper $installer, array $config = [])
    {
        $this->_installer = $installer;
        parent::__construct($config);
    }
    
    /**
     * 获取安装助手类
     *
     * @return InstallerHelper
     */
    public function getInstaller()
    {
        return $this->_installer;
    }
    
}