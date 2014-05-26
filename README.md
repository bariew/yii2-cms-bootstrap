
CMS bootstrap Yii2 extension
===================
Attaches external modules to application.
External module is a Yii2 common extension, installed with composer, and having
 MVC structure.


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