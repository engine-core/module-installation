<?php
/**
 * @link      https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use EngineCore\Ec;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class StepTrait
 *
 * @property array  $stepFlow    安装步骤流程
 * @property string $nextStep    下一步
 * @property string $prevStep    前一步
 * @property array  $step        安装步骤详情数据，只读
 * @property string $currentStep 获取当前第一个未完成的步骤，只读
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
trait StepTrait
{
    
    /**
     * @var array 缓存安装步骤详情
     */
    private $_stepCache;
    
    /**
     * 获取安装步骤流程
     *
     * @return array
     */
    public function getStepFlow()
    {
        return [
            'index'             => [
                'label' => Yii::t('ec/modules/installation', 'Welcome'),
                'url'   => [$this->id . '/index'],
            ],
            'license-agreement' => [
                'label' => Yii::t('ec/modules/installation', 'License agreement'),
                'url'   => [$this->id . '/license-agreement'],
            ],
            'check-env'         => [
                'label' => Yii::t('ec/modules/installation', 'Check installation conditions'),
                'url'   => [$this->id . '/check-env'],
            ],
            'set-site'          => [
                'label' => Yii::t('ec/modules/installation', 'Set site'),
                'url'   => [$this->id . '/set-site'],
            ],
            'set-db'            => [
                'label' => Yii::t('ec/modules/installation', 'Set database'),
                'url'   => [$this->id . '/set-db'],
            ],
            'extension-manager' => [
                'label' => Yii::t('ec/modules/installation', 'Extension manager'),
                'url'   => [$this->id . '/extension-manager'],
            ],
            'extension-detail'  => [
                'label' => Yii::t('ec/modules/installation', 'Extension detail'),
                'url'   => [$this->id . '/extension-detail'],
            ],
            'finish'            => [
                'label' => Yii::t('ec/modules/installation', 'Finish'),
                'url'   => [$this->id . '/finish'],
            ],
        ];
    }
    
    /**
     * 获取下一个安装步骤
     *
     * @param string $step 当前步骤
     *
     * @return array|string
     */
    public function getNextStep($step = null)
    {
        $next = false;
        $array = $this->getStepFlow();
        $curr_key = $step ?: $this->action->id;
        do {
            $tmp_key = key($array);
            $res = next($array);
        } while (($tmp_key != $curr_key) && $res);
        if ($res) {
            $next = key($array);
        }
        
        return $next ? $next : '';
    }
    
    /**
     * 获取前一个安装步骤
     *
     * @param string $step 当前步骤
     *
     * @return array|string
     */
    public function getPrevStep($step = null)
    {
        $array = $this->getStepFlow();
        $curr_key = $step ?: $this->action->id;
        end($array);
        $prev = key($array);
        do {
            $tmp_key = key($array);
            $res = prev($array);
        } while (($tmp_key != $curr_key) && $res);
        if ($res) {
            $prev = key($array);
        }
        
        return $prev ? $prev : '';
    }
    
    /**
     * 获取安装步骤详情
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getStep()
    {
        if (null === $this->_stepCache) {
            $this->_stepCache = Ec::$service->getSystem()->getCache()->getOrSet(Module::CACHE_STEP, function () {
                return array_fill_keys(array_keys($this->getStepFlow()), false);
            });
        }
        
        return $this->_stepCache;
    }
    
    /**
     * 重置安装步骤数据
     */
    public function resetStep()
    {
        $this->_stepCache = null;
        Ec::$service->getSystem()->getCache()->getComponent()->delete(Module::CACHE_STEP);
    }
    
    /**
     * 获取当前第一个未完成的步骤
     *
     * @return string
     */
    public function getCurrentStep()
    {
        return array_search(false, $this->getStep(), true);
    }
    
    /**
     * 指定`step`步骤是否已经完成
     *
     * @param string $step
     *
     * @return bool
     */
    public function isFinishedStep($step): bool
    {
        return $this->step[$step] ?? false;
    }
    
    /**
     * 标记`step`步骤已经完成
     *
     * @param array|string $step
     */
    public function finishStep($step)
    {
        foreach ((array)$step as $value) {
            if (isset($this->step[$value])) {
                $this->_stepCache[$value] = true;
            }
        }
        Ec::$service->getSystem()->getCache()->getComponent()->set(Module::CACHE_STEP, $this->_stepCache);
    }
    
    /**
     * 标记`step`步骤为未完成
     *
     * @param array|string $step
     */
    public function disableStep($step)
    {
        foreach ((array)$step as $value) {
            if (isset($this->step[$value])) {
                $this->_stepCache[$value] = false;
            }
        }
        Ec::$service->getSystem()->getCache()->getComponent()->set(Module::CACHE_STEP, $this->_stepCache);
    }
    
}