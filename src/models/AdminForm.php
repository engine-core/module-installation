<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\models;

use EngineCore\base\Model;
use Yii;

/**
 * Class AdminForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class AdminForm extends Model
{
    
    use ModelTrait;
    
    public $email;
    
    public $username;
    
    public $password;
    
    public $passwordConfirm;
    
    const CACHE_KEY = "install-admin-form";
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['password', 'username'], 'string', 'max' => 128],
            ['email', 'email'],
            // password_confirm
            ['passwordConfirm', 'required'],
            ['passwordConfirm', 'compare', 'compareAttribute' => 'password'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username'        => 'username',
            'password'        => 'Password',
            'passwordConfirm' => 'Password Confirm',
            'email'           => 'Email',
        ];
    }
    
    /**
     * 加载默认值
     */
    public function loadDefaultValues()
    {
        $data = Yii::$app->getCache()->get(self::CACHE_KEY);
        if ($data) {
            $this->setAttributes($data);
        }
    }
    
    /**
     * 保存
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            return Yii::$app->getCache()->set(self::CACHE_KEY, $this->toArray());
        }
        
        return false;
    }
    
}