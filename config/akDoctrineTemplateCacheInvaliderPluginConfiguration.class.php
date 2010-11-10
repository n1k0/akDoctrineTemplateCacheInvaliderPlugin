<?php
/**
 * akDoctrineTemplateCacheInvaliderPlugin configuration.
 * 
 * @package     akDoctrineTemplateCacheInvaliderPlugin
 * @subpackage  config
 * @author      Nicolas Perriault <np@akei.com>
 */
class akDoctrineTemplateCacheInvaliderPluginConfiguration extends sfPluginConfiguration
{
  static protected $doctrineListener;
  
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
  
  /**
   * @return akTemplateCacheInvaliderListener|null
   */
  static public function getDoctrineListener()
  {
    return self::$doctrineListener;
  }

  /**
   * @throws akDoctrineTemplateCacheInvaliderException if configuration is invalid
   */
  public function listenToContextLoadFactoriesEvent(sfEvent $event)
  {
    $context = $event->getSubject();

    $configuration = include($context->getConfigCache()->checkConfig('config/doctrine_cache_invalider.yml'));

    if (!is_array($configuration) || !sizeof($configuration))
    {
      return;
    }
    
    self::$doctrineListener = new akTemplateCacheInvaliderListener($this->dispatcher, $context->getViewCacheManager(), $configuration);

    foreach ($configuration as $model => $data)
    {
      try
      {
        $table = Doctrine::getTable($model);
      }
      catch (Doctrine_Exception $e)
      {
        throw new akDoctrineTemplateCacheInvaliderException(sprintf('Couldn\'t retrieve Doctrine table instance for "%s" model: %s',
                                                                    $e->getMessage()));
      }

      $table->addRecordListener(self::$doctrineListener, get_class(self::$doctrineListener));
    }
  }
}
