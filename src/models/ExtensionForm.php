<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\models;

/**
 * Class ExtensionForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class ExtensionForm extends BaseForm
{
    
    /**
     * @var array 需要安装的扩展
     * @see \EngineCore\modules\installation\helpers\InstallerHelper::$defaultExtensions
     */
    public $extension;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['extension', 'required'],
            ['extension', 'checkDownload'],
            ['extension', 'checkConflict'],
            ['extension', 'checkCircular'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return [
            'extension',
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'extension' => '需要安装的扩展',
        ];
    }
    
    /**
     * 加载默认值
     */
    public function loadDefaultValues()
    {
        $this->getInstaller()->setExtensions($this->extension ?: []);
        $this->extension = $this->getInstaller()->getExtensions();
    }
    
    /**
     * 保存
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            return $this->getInstaller()->save();
        }
        
        return false;
    }
    
    /**
     * 检查是否需要下载扩展
     *
     * @param string $attribute
     */
    public function checkDownload($attribute)
    {
        $data = $this->getInstaller()->getDependenciesStatus();
        if (!empty($data['download'])) {
            $info = "请下载以下扩展：<br/>";
            $i = 1;
            $count = count($data['download']);
            foreach ($data['download'] as $extension) {
                $info .= ($count > 1 ? $i . ') ' : '') . $extension . "<br/>";
                $i++;
            }
            $this->addError($attribute, $info);
        }
    }
    
    /**
     * 检查是否存在扩展版本冲突
     *
     * @param string $attribute
     */
    public function checkConflict($attribute)
    {
        $data = $this->getInstaller()->getDependenciesStatus();
        if (!empty($data['conflict'])) {
            $info = "请解决以下扩展版本冲突问题：<br/>";
            $i = 1;
            $count = count($data['conflict']);
            foreach ($data['conflict'] as $uniqueName => $item) {
                $info .= ($count > 1 ? $i . ') ' : '') .
                    "{$uniqueName} 当前版本为：{$item['localVersion']}，和以下扩展存在版本冲突：<br/>";
                foreach ($item['requireVersion'] as $uName => $requireVersion) {
                    $info .= " - '" . $uName . "' 需要版本为 '{$requireVersion}'。<br/>";
                    $i++;
                }
            }
            $this->addError($attribute, $info);
        }
    }
    
    /**
     * 检查是否存在死循环
     *
     * @param string $attribute
     */
    public function checkCircular($attribute)
    {
        $data = $this->getInstaller()->getDependenciesStatus();
        if (!empty($data['circular'])) {
            $info = "以下扩展被检测出无限循环依赖关系：<br/>";
            $i = 1;
            $count = count($data['circular']);
            foreach ($data['circular'] as $uniqueName => $config) {
                $info .= ($count > 1 ? $i . ') ' : '') . $uniqueName . ' <=> ' . $config . "<br/>";
                $i++;
            }
            $this->addError($attribute, $info);
        }
    }
    
}