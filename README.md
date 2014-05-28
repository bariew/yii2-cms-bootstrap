
CMS bootstrap Yii2 extension
===================
Attaches external modules to application.
External module is a Yii2 common extension, installed with composer, and having
 MVC structure.
It does few things:
1. Adds external modules to \Yii::$app->modules array.
2. Replaces common migration controller with ModuleMigration controller
which also finds migrations in module /migrations folder and runs them.
3. Adds events from module root folder _events.php and makes them active.
_events.php content example:
```
    <?php
    return [
        'app\models\User' => [
            'afterInsert' => [
                ['app\models\Email', 'userRegistration']
            ],  
        ]
    ];
```

See more description in related packages:
    "bariew/yii2-module-migration-controller"
    "bariew/yii2-event-component"


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bariew/yii2-event-component "*"
```

or add

```
"bariew/yii2-event-component": "*"
```

to the require section of your `composer.json` file.


Usage
-----
```
  You need to name your extension in a way "yii2-{your module name}-cms-module", 
 (e.g. yii2-user-cms-module) to automatically attach it to the application.
```