<?php

/**
 * akDoctrineTemplateCacheInvaliderPlugin configuration.
 * 
 * @package     akDoctrineTemplateCacheInvaliderPlugin
 * @subpackage  config
 * @author      Nicolas Perriault <np@akei.com>
 * @version     SVN: $Id: PluginConfiguration.class.php 17207 2009-04-10 15:36:26Z Kris.Wallsmith $
 */
class akDoctrineTemplateCacheInvaliderPluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // enable Doctrine listener only if needed
    if (sfConfig::get('app_akDoctrineTemplateCacheInvaliderPlugin_enabled_listener'))
    {
      $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactoriesEvent'));
    }
  }

  public function listenToContextLoadFactoriesEvent(sfEvent $event)
  {
    $context = $event->getSubject();

    $configuration = include($context->getConfigCache()->checkConfig('config/doctrine_cache_invalider.yml'));

    if (is_array($configuration) and sizeof($configuration))
    {
      foreach ($configuration as $model => $data)
      {
        if (($table = Doctrine::getTable($model)) and $table instanceof Doctrine_Table)
        {
          $table->addRecordListener(new akTemplateCacheInvaliderListener(
            $this->dispatcher, $context->getViewCacheManager(), $configuration
          ), 'akTemplateCacheInvaliderListener');
        }
      }
    }
  }
}
