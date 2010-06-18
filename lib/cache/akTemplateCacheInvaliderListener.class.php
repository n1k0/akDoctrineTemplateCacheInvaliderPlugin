<?php

class akTemplateCacheInvaliderListener extends Doctrine_Record_Listener
{
  static protected
    $processed        = array();
  
  protected 
    $configuration    = array(),
    $dispatcher       = null,
    $viewCacheManager = null;
  
  public function __construct(sfEventDispatcher $dispatcher, sfViewCacheManager $viewCacheManager = null, array $configuration = array())
  {
    if (!count($configuration))
    {
      return;
    }
    
    $this->dispatcher = $dispatcher;
    $this->viewCacheManager = $viewCacheManager;
    $this->configuration = $configuration;
    $this->clearProcessed();
  }
  
  public function clearProcessed()
  {
    self::$processed = array();
  }
  
  public function isRecordProcessed(Doctrine_Record $record)
  {
    return in_array($this->getRecordFingerprint($record), self::$processed);
  }
  
  public function setRecordProcessed(Doctrine_Record $record)
  {
    if (!$this->isRecordProcessed($record))
    {
      self::$processed[] = $this->getRecordFingerprint($record);
    }
  }
  
  public function getRecordFingerprint(Doctrine_Record $record)
  {
    return spl_object_hash($record);
  }
  
  public function postSave(Doctrine_Event $event)
  {
    $record = $event->getInvoker();
    
    if (array_key_exists($model = get_class($record), $this->configuration) && !$this->isRecordProcessed($record))
    {
      $this->processCacheInvalidation($record, $this->configuration[$model]);
      
      $this->setRecordProcessed($record);
      
      $event->skipOperation();
    }
  }
  
  public function processCacheInvalidation(Doctrine_Record $record, array $rules = null)
  {
    if (is_null($rules) || !is_array($rules) || !count($rules))
    {
      return;
    }
    
    if (!isset($rules['uris']) || !is_array($rules['uris']))
    {
      return;
    }
    
    foreach ($rules['uris'] as $cacheUri => $applications)
    {
      if (is_null($applications))
      {
        $applications = sfConfig::get('sf_app');
      }
      
      $applications = array($applications);
      
      $resolver = new akDoctrineCacheUriResolver($record, $cacheUri);
      
      foreach ($resolver->computeUris() as $cacheUri)
      {
        foreach ($applications as $application)
        {
          $this->purgeCacheUri($cacheUri, $application);
        }
      }
    }
  }
  
  // TODO: what about $hosts? could be local configuration set by yaml
  public function purgeCacheUri($cacheUri, $targetApplication = null, $hosts = '*')
  {
    $currentApplication = sfConfig::get('sf_app');
    
    if ($switchRequired = !is_null($targetApplication) && $targetApplication !== $currentApplication)
    {
      try
      {
        sfContext::switchTo($targetApplication);
      }
      catch (Exception $e)
      {
        $this->logError(sprintf('Impossible to invalidate template cache from "%s" application context "%s" (%s: %s)', $targetApplication, get_class($e), $e->getMessage()));
        
        return;
      }
      
      $viewCacheManager = sfContext::getInstance()->getViewCacheManager();
    }
    else
    {
      $viewCacheManager = $this->viewCacheManager;
    }

    if (!$viewCacheManager instanceof sfViewCacheManager)
    {
      return;
    }
    
    $error = null;

    try
    {
      $viewCacheManager->remove($cacheUri, $hosts);
    }
    catch (Exception $e)
    {
      if (sfConfig::get('sf_logging_enabled'))
      {
        $error = sprintf('Problem during cache invalidation process for uri "%s": %s', $cacheUri, $e->getMessage());
      }
    }

    if ($switchRequired)
    {
      sfContext::switchTo($currentApplication);
    }

    if (!is_null($error))
    {
      $this->logError($error);
    }
  }
  
  public function logError($message, $priority = sfLogger::ERR)
  {
    $this->dispatcher->notify(new sfEvent($this, 'application.log', array($message, 'priority' => $priority)));
  }
}
