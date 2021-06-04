<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\controllers;

use EngineCore\modules\installation\StepTrait;
use EngineCore\web\Controller;

/**
 * Class CommonController
 *
 * @property \EngineCore\modules\installation\Module $module
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class CommonController extends Controller
{
    
    use StepTrait;
    
    protected $defaultDispatchMap = ['index', 'set-db', 'set-site', 'license-agreement', 'check-env', 'set-admin',
        'extension-manager', 'extension-detail', 'finish'];
    
}