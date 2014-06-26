<?php
/**
 * CmsBootstrap class file
 * @copyright Copyright (c) 2014 Galament
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\cmsBootstrap;

use yii\base\BootstrapInterface;
use yii\base\Application;
use yii\helpers\ArrayHelper;

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
        $app->setComponents([
            'cms' => [
                'class' => 'bariew\cmsBootstrap\Cms',
            ],
        ]);
        
        $this->app = $app;
        $this->attachModules()
            ->attachMigrations()
            ->attachEvents();
        
        return true;
    }
    /**
     * attaches modules to application from external 
     * composer installed extensions sources
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
            $paramPath = $config['alias'][$alias] . DIRECTORY_SEPARATOR . 'params' . DIRECTORY_SEPARATOR . 'main.php';
            if (file_exists($paramPath)) {
                $modules[$matches[1]]['params'] = require $paramPath;
            }
        }
        \Yii::configure($this->app, compact('modules'));
        return $this;
    }
    /**
     * Attaches advanced module migration controller
     * for migrating from modules root /migrations folder
     * @return \bariew\cmsBootstrap\CmsBootstrap this
     */
    public function attachMigrations()
    {
        $this->app->controllerMap['migrate'] = 'bariew\moduleMigration\ModuleMigration';
        return $this;
    }
    /**
     * Attaches module events from it root _events.php files
     * to app->eventManager
     */
    public function attachEvents()
    {
        $events = [];
        foreach ($this->app->modules as $config) {
            if (isset($config['params']['events'])) {
                $events = ArrayHelper::merge($events, $config['params']['events']);
            } else if (isset($config->params['events'])) {
                $events = ArrayHelper::merge($events, $config->params['events']);
            }
        }
        $this->app->cms->eventManager->attachEvents($events);
    }
}