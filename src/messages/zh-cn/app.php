<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

return [
    'Installation' => '安装向导',
    // Database Form
    'scheme' => '数据库类型',
    'hostname' => '数据库地址',
    'database' => '数据库名称',
    'port' => '数据库端口',
    'username' => '数据库用户名',
    'password' => '数据库密码',
    'tablePrefix' => '数据库表前缀',
    // Extension Manage Form
    'Please select the extension you want to install.' => '请选择需要安装的扩展。',
    'Extension' => '扩展',
    'The extension belongs to the installation wizard extension category and cannot be installed.'
    => '{extension} 扩展属于安装向导扩展分类，无法进行安装。',
    'Extension management module must implement the interface of extension management installation wizard.'
    => '{extension} 扩展管理模块必须实现扩展管理安装向导接口 `{interface}`。',
    'Backend application environment must be preselected for extension management module.'
    => '{extension} 扩展管理模块必须预选【{backend_app}】应用环境。',
    'Please select the application environment for the extension you want to install.'
    => '请为需要安装的扩展 {extension} 选择应用环境。',
    'At least one extension of the system core configuration needs to be installed.'
    => '至少需要安装一个系统核心配置的扩展。',
    'You can install up to one extension of the extension management category.'
    => '最多可以安装一个扩展管理分类的扩展。',
    'At least one extension of the extension management category needs to be installed.'
    => '至少需要安装一个扩展管理分类的扩展。',
    'You can install at most one extension of backend home page category.'
    => '最多可以安装一个后台主页分类的扩展。',
    'You need to install at least one extension of the backend home page category.'
    => '至少需要安装一个后台主页分类的扩展。',
    'At least one extension of the theme type needs to be installed.'
    => '至少需要安装一个主题类型的扩展。',
    'The extension cannot be installed in the app.' => '{extension} 扩展无法安装在 {app} 应用里。',
    // Set Site Form
    'title' => '网站名称',
    'description' => '网站描述',
    'keyword' => '网站关键词',
    'icp' => '网站备案号',
    'icp example' => '如：沪ICP备12345678号-9',
    // step
    'Welcome' => '欢迎页面',
    'License agreement' => '许可协议',
    'Check installation conditions' => '检查安装条件',
    'Set site' => '网站设置',
    'Set database' => '数据库设置',
    'Extension manager' => '扩展中心',
    'Extension detail' => '扩展详情',
    'Finish' => '完成',
    // operate
    'Unable to jump to an unfinished step. Please complete the current step first.'
    => '无法跳转至未完成的步骤，请先完成当前步骤~',
    'Agree to the license agreement to continue the installation.' => '同意安装协议才能继续安装！',
    'Please satisfy the extension dependency before proceeding to the next step.'
    => '请先满足扩展依赖关系【无限循环、未下载、版本冲突】再执行下一步操作。',
    'Automatically jump to the backend home page.' => '自动跳转到后台首页',
    'Install the extension and automatically complete the database migration required by the extension.'
    => '安装扩展，并自动完成扩展所需的数据库迁移。',
    'According to the installed extension, the menu configuration, system configuration and permission configuration required by the extension are automatically generated.'
    => '根据所安装的扩展，自动生成扩展所需的菜单配置、系统配置、权限配置等。',
    'For security reasons, the current installation wizard module will be automatically uninstalled after completing the installation steps.'
    => '出于安全考虑，当前安装向导模块将在【完成】安装步骤后自动卸载。',
    'The current system environment [Installation - Installation environment] will automatically switch to [Development - Development environment].'
    => '当前系统环境【Installation - 安装环境】将自动切换为【Development - 开发环境】。',
];
