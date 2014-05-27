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
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        $this->app = $app;
        $this->attachModules()
            ->attachMigrations();
        
        return true;
    }
    /**
     * finds and creates app event manager from its settings
     */
    public function attachModules()
    {
        $modules = $this->app->modules;
        foreach ($this->app->extensions as $name => $config) {
            $extName = preg_replace('/.*\/(.*)$/', '$1', $name);
            if(!preg_match('/yii2-(.+)-cms-module/', $extName, $matches)){
                continue;
            }
            $alias = key($config['alias']);
            $modules[$matches[1]] = [
                'class'     => str_replace(['@', '/'], ['\\', '\\'], $alias) .'\Module',
                'basePath'  => $config['alias'][$alias]
            ];
        }
        \Yii::configure($this->app, compact('modules'));
        return $this;
    }
    
    public function attachMigrations()
    {
        //$this->app->controllerMap['migrate'] = 'bariew\moduleMigration\ModuleMigration';
    }
}