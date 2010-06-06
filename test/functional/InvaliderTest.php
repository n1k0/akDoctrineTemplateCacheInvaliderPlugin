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

$browser->test()->info('Updating first article');
$firstArticle = Doctrine::getTable('Article')->doSelectForSlug(array(
  'slug' => 'my-first-article',
));
$firstArticle->title = 'My first article, modified';
$firstArticle->save($conn);

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

$conn->rollback();