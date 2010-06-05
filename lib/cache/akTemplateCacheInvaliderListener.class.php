<?php

class akTemplateCacheInvaliderListener extends Doctrine_Record_Listener
{
  protected 
    $configuration    = array(),
    $dispatcher       = null,
    $viewCacheManager = null;
  
  public function __construct(sfEventDispatcher $dispatcher, sfViewCacheManager $viewCacheManager = null, array $configuration = array())
  {
    if (null === $viewCacheManager || !count($configuration))
    {
      return;
    }
    
    $this->dispatcher = $dispatcher;
    $this->viewCacheManager = $viewCacheManager;
    $this->configuration = $configuration;
  }
  
  public function postSave(Doctrine_Event $event)
  {
    $record = $event->getInvoker();
    
    if (array_key_exists($model = get_class($record), $this->configuration))
    {
      $this->processCacheInvalidation($record, $this->configuration[$model]);
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
      if (!count($applications = array($applications)))
      {
        continue;
      }
      
      if (count($ruleCacheUris = $this->computeRecordCacheUris($record, $cacheUri)))
      {
        foreach (array_unique($ruleCacheUris) as $cacheUri)
        {
          foreach ($applications as $application)
          {
            $this->purgeCacheUri($cacheUri, $application);
          }
        }
      }
    }
  }
  
  public function computeRecordCacheUris(Doctrine_Record $record, $cacheUri)
  {
    $cacheUris = array($this->computeRecordCacheUri($record, $cacheUri));
    
    if ($record->getTable()->hasRelation('Translation'))
    {
      foreach ($record->Translation as $translation)
      {
        $cacheUris[] = $this->computeRecordCacheUri($translation, $cacheUri);
      }
    }
    
    return $cacheUris;
  }
  
  public function computeRecordCacheUri(Doctrine_Record $record, $cacheUri)
  {
    if (!is_string($cacheUri))
    {
      return;
    }
    
    if (preg_match_all('/%([a-z_]+)%/si', $cacheUri, $m) && isset($m[1]))
    {
      $placeHolders = $m[0];
      
      $replaceValues = array();
      
      foreach ($m[1] as $field)
      {
        try
        {
          $replaceValues[] = $record->$field;
        }
        catch (Exception $e)
        {
            $replaceValues[] = '*';
        }
      }
      
      $cacheUri = str_replace($placeHolders, $replaceValues, $cacheUri);
    }
    
    return $cacheUri;
  }
  
  // TODO: what about $hosts? could be local configuration set by yaml
  public function purgeCacheUri($cacheUri, $targetApplication = null, $hosts = '*')
  {
    $currentApplication = sfConfig::get('sf_app');

    if ($switchRequired = !is_null($targetApplication) && $targetApplication !== $currentApplication)
    {
      sfContext::switchTo($targetApplication);

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
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array($error, 'priority' => sfLogger::ERR)));
    }
  }
}
