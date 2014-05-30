<?php
/**
 * CmsBootstrap class file
 * @copyright Copyright (c) 2014 Galament
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\cmsBootstrap;

use yii\base\BootstrapInterface;
use yii\base\Application;

/**
 * Bootstrap class initiates external modules.
 * 
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class CmsBootstrap implements BootstrapInterface
{
    /**
     * @var Application current yii app
     */
    protected $app;
    protected $modules = [];
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        // Set app CMS
        $app->setComponents(['cms' => ['class' => 'bariew\cmsBootstrap\Cms']]);
        // Change app migration controller
        $app->controllerMap['migrate'] = 'bariew\moduleMigration\ModuleMigration';
        $this->app = $app;
        
        $this->attachModules()
            ->attachModuleParams();
        \Yii::configure($this->app, ['modules' => $this->modules]);
        return true;
    }
    /**
     * attaches modules to application from external 
     * composer installed extensions sources
     */
    public function attachModules()
    {
        $this->modules = $this->app->modules;
        foreach ($this->app->extensions as $name => $config) {
            $extName = preg_replace('/.*\/(.*)$/', '$1', $name);
            if(!preg_match('/yii2-(.+)-cms-module/', $extName, $matches)){
                continue;
            }
            $alias = key($config['alias']);
            $this->modules[$matches[1]] = [
                'class'     => str_replace(['@', '/'], ['\\', '\\'], $alias) .'\Module',
                'basePath'  => $config['alias'][$alias]
            ];
        }
        return $this;
    }
    /**
     * Attaches module configs from module /config folder
     */
    public function attachModuleParams()
    {
        $s = DIRECTORY_SEPARATOR;
        foreach ($this->modules as $moduleName => $config) {
            switch (gettype($config)) {
                case 'object'   : $basePath = $config->basePath;
                    break;
                case 'array'    : 
                    if (isset($config['basePath'])) {
                        $basePath = $config['basePath'];
                        break;
                    }
                    $config = $config['class'];
                default         : 
                    $basePath = str_replace('\\', '/', preg_replace('/^(.*)\\\(\w+)$/', '@$1', $config));
                    $basePath = \Yii::getAlias($basePath);
            }
            $file = $basePath . $s . 'params' . $s . 'main.php';
            if (!file_exists($file) || !is_file($file)) {
                continue;
            }
            $this->processModuleParams($moduleName, include $file);
        }
    }
    
    public function processModuleParams($moduleName, $params) 
    {
        $this->modules[$moduleName]['params'] = $params;
        if (isset($params['events'])) {
            $this->app->cms->eventManager->attachEvents($params['events']);
        }
    }
}