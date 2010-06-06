<?php

if (!isset($_SERVER['SYMFONY']))
{
  $_SERVER['SYMFONY'] = '/Users/niko/Sites/vendor/symfony/1.4/lib';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->setPlugins(array(
      'sfDoctrinePlugin',
      'akDoctrineTemplateCacheInvaliderPlugin',
    ));
    $this->setPluginPath('akDoctrineTemplateCacheInvaliderPlugin', dirname(__FILE__).'/../../../..');
  }
}
