<?php
/**
 * CmsBootstrap class file
 * @copyright Copyright (c) 2014 Galament
 * @license http://www.yiiframework.com/license/
 */

namespace bariew\cmsBootstrap;

use Yii;
use bariew\eventManager\EventBootstrap;
use yii\base\BootstrapInterface;
use yii\base\Application;
use yii\base\Event;
use yii\bootstrap\NavBar;
use yii\helpers\ArrayHelper;
use yii\web\View;

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
        $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
            $app->getView()->on(View::EVENT_BEGIN_BODY, [$this, 'renderMenu']);
        });
        $this->app = $app;
        $this->attachModules()
            ->attachMigrations()
            ->attachEvents()
            ->setThemePaths();
        
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
            $moduleName = $matches[1];
            $alias = key($config['alias']);
            $basePath = $config['alias'][$alias];
            $paramPath = $basePath . DIRECTORY_SEPARATOR . 'params' . DIRECTORY_SEPARATOR . 'main.php';
            $params =  file_exists($paramPath) ? require $paramPath : [];
            $params['moduleAlias'] = $alias;
            $modules[$moduleName] = [
                'class'     => str_replace(['@', '/'], ['\\', '\\'], $alias) .'\Module',
                'basePath'  => $basePath,
                'params'    => $params,
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
            if (isset($config['params']['events'])) {
                $events = ArrayHelper::merge($events, $config['params']['events']);
            } else if (isset($config->params['events'])) {
                $events = ArrayHelper::merge($events, $config->params['events']);
            }
        }
        EventBootstrap::getEventManager(\Yii::$app)->attachEvents($events);
        return $this;
    }

    /**
     *
     * @return \self $this self instance.
     */
    protected function setThemePaths()
    {
        $modules = $this->app->modules;
        if(!isset(\Yii::$app->view->theme->pathMap['@app/modules'])){
            return;
        }
        $modulesPath = \Yii::$app->view->theme->pathMap['@app/modules'];
        $paths = [];
        foreach ($modules as $name => $config) {
            if (!isset($config['params']['moduleAlias'])) {
                continue;
            }
            $alias = $config['params']['moduleAlias'];
            $paths[$alias . "/views"] = $modulesPath . DIRECTORY_SEPARATOR . $name;
            $paths[$alias . "/widgets/views"] = $modulesPath . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'widgets';
        }
        \Yii::configure(\Yii::$app->view->theme, ['pathMap' => array_merge(\Yii::$app->view->theme->pathMap, $paths)]);
        return $this;
    }

    public function renderMenu(Event $event)
    {
        if (Yii::$app->getRequest()->getIsAjax()) {
            return;
        }
        NavBar::begin([
            'brandLabel' => 'Home',
            'brandUrl' => Yii::$app->homeUrl,
            'options' => [
                'class' => 'navbar-inverse navbar-fixed-top',
            ],
        ]);
        if (isset(\Yii::$app->i18n->widget)) {
            echo "<div class='btn pull-right'>".Yii::$app->i18n->widget."</div>";
        }
        echo Menu::widget([
            'options' => ['class' => 'navbar-nav navbar-right']
        ]);

        NavBar::end();
    }
}