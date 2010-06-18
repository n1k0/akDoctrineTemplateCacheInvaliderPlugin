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
    $this->dispatcher->connect('context.load_factories', array($this, 'listenToContextLoadFactoriesEvent'));
  }

  public function listenToContextLoadFactoriesEvent(sfEvent $event)
  {
    $context = $event->getSubject();

    $configuration = include($context->getConfigCache()->checkConfig('config/doctrine_cache_invalider.yml'));

    Doctrine_Manager::getInstance()->addRecordListener(new akTemplateCacheInvaliderListener(
      $this->dispatcher, $context->getViewCacheManager(), $configuration
    ));
  }
}
