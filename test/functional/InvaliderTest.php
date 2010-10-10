<?php

$app = 'frontend';
include dirname(__FILE__).'/../bootstrap/functional.php';

new sfDatabaseManager($configuration);

$task = new sfDoctrineBuildTask($configuration->getEventDispatcher(), new sfFormatter());
$task->run(array(), array('sql', 'db', 'and-load', 'no-confirmation', 'application' => $app));

$conn = Doctrine::getConnectionByTableName('Article');
$conn->beginTransaction();

$browser = new sfTestFunctional(new sfBrowser());

$browser->
  get('/en/articles')->
  with('request')->begin()->
    isParameter('module', 'content')->
    isParameter('action', 'index')->
    isParameter('sf_culture', 'en')->
  end()->
  with('response')->begin()->
    checkElement('h1', '/Articles/')->
    checkElement('ul li', 2)->
    checkElement('ul li', '/My first article/')->
    checkElement('ul li', '/My second article/', array('position' => 1))->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()->
  click('My first article')->
  with('response')->begin()->
    checkElement('h1', '/My first article/')->
    checkElement('ul#comments li', 2)->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()
;

$browser->test()->info('Updating first article with php code - cache invalidation is disabled on frontend');
$firstArticle = Doctrine::getTable('Article')->doSelectForSlug(array(
  'slug' => 'my-first-article',
));
$firstArticle->title = 'My first article, cache invalidation is disabled';
$firstArticle->save($conn);

$browser->
  get('/en/articles')->
  with('response')->begin()->
    checkElement('h1', '/Articles/')->
    checkElement('ul li', 2)->
    checkElement('ul li', '/My first article/')->
    checkElement('ul li:contains("cache invalidation is disabled")', false)->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()
;

$browser->test()->info('Updating first article with php code - switch to backend');
sfContext::switchTo('backend');
$firstArticle = Doctrine::getTable('Article')->doSelectForSlug(array(
  'slug' => 'my-first-article',
));
$firstArticle->title = 'My first article, modified';
$firstArticle->save($conn);

sfContext::switchTo('frontend');

$browser->
  get('/en/articles')->
  with('response')->begin()->
    checkElement('h1', '/Articles/')->
    checkElement('ul li', 2)->
    checkElement('ul li', '/My first article, modified/')->
    checkElement('ul li', '/My second article/', array('position' => 1))->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()->
  click('My first article, modified')->
  with('response')->begin()->
    checkElement('h1', '/My first article, modified/')->
    checkElement('ul#comments li', 2)->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()
;

$browser->test()->info('Updating first article from another app (with caching system disabled) - switch to backend');
sfContext::switchTo('backend');
$configuration->loadHelpers('Partial');

$backendBrowser = new sfTestFunctional(new sfBrowser());
$backendBrowser->
  get('/article/1/edit')->
  with('request')->begin()->
    isParameter('module', 'article')->
    isParameter('action', 'edit')->
  end()->


  setField('article[en][title]', 'My first article, edited')->
  click('Save')->
  followRedirect()->

  with('response')->begin()->
    checkElement('div.notice', 1)->
  end()
;

$browser->
  get('/en/article/'.$firstArticle->slug.'/view')->
  with('response')->begin()->
    checkElement('h1', '/My first article, edited/')->
    checkElement('ul#comments li', 2)->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()
;

$conn->rollback();