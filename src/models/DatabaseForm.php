<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\models;

use ekevin\dsn\Dsn;
use Yii;
use yii\db\Connection;
use yii\db\Exception;

/**
 * Class DatabaseForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DatabaseForm extends BaseForm
{
    
    public $username;
    
    public $password;
    
    public $hostname = 'localhost';
    
    public $port = '3306';
    
    public $database;
    
    public $scheme;
    
    public $tablePrefix = '';
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // scheme rules
            'schemeRequire'   => ['scheme', 'required'],
            // hostname rules
            'hostnameRequire' => ['hostname', 'required'],
            'checkDb'         => ['hostname', 'checkDb'],
            // database rules
            'databaseRequire' => ['database', 'required'],
            // port rules
            'portRequire'     => ['port', 'required'],
            // username rules
            'usernameRequire' => ['username', 'required'],
            // password rules
            'password'        => ['password', 'safe'],
            // tablePrefix rules
            'tablePrefix'     => ['tablePrefix', 'safe'],
        ];
    }
    
    public function checkDb()
    {
        $dsn = Dsn::build([
            'scheme'   => $this->scheme,
            'hostname' => $this->hostname,
            'port'     => $this->port,
            'dbname'   => $this->database,
        ])->dsn;
        // 创建测试数据库链接
        Yii::$app->set('testDb', [
            'class'    => Connection::class,
            'dsn'      => $dsn,
            'username' => $this->username,
            'password' => $this->password,
            'charset'  => 'utf8',
        ]);
        
        try {
            Yii::$app->get('testDb')->open();
        } catch (Exception $e) {
            switch ($e->getCode()) {
                case 1049:
                    $this->addError('database', $e->getMessage());
                    break;
                case 1045:
                    $this->addError('password', $e->getMessage());
                    break;
                case 2002:
                    $this->addError('hostname', $e->getMessage());
                    break;
                default:
                    $this->addError('hostname', $e->getMessage());
                    break;
            }
        }
    }
    
    /**
     * 加载默认值
     */
    public function loadDefaultValues()
    {
        if (!empty($data = $this->installer->getConfig('db'))) {
            $this->setAttributes($data);
        } else {
            $definitions = Yii::$app->getComponents();
            
            if (isset($definitions['db']) && isset($definitions['db']['dsn'])) {
                $dsn = Dsn::parse($definitions['db']['dsn']);
                $this->scheme = $dsn->getScheme();
                $this->hostname = $dsn->getHost();
                $this->port = $dsn->getPort();
                $this->database = $dsn->getDatabase();
                $this->username = $definitions['db']['username'];
                $this->password = $definitions['db']['password'];
                $this->tablePrefix = $definitions['db']['tablePrefix'];
            } else {
                $this->scheme = 'mysql';
                $this->hostname = 'localhost';
                $this->port = 3306;
                $this->username = 'root';
            }
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'scheme'      => '数据库类型',
            'hostname'    => '数据库地址',
            'database'    => '数据库名称',
            'port'        => '数据库端口',
            'username'    => '数据库用户名',
            'password'    => '数据库密码',
            'tablePrefix' => '数据库表前缀',
        ];
    }
    
    /**
     * 保存
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $this->installer->setConfig('db', $this->toArray());
            
            return true;
        }
        
        return false;
    }
    
}