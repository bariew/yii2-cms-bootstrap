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
            switch (gettype($config)) {
                case 'object'   : 
                    $basePath = $config->basePath;
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
            $file = $basePath . DIRECTORY_SEPARATOR . '_events.php';
            if (!file_exists($file) || !is_file($file)) {
                continue;
            }
            $events = array_merge($events, include $file);
        }
        $this->app->cms->eventManager->attachEvents($events);
    }
}