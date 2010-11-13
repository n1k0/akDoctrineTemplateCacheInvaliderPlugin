<?php

$app = 'frontend';
include dirname(__FILE__).'/../bootstrap/functional.php';

new sfDatabaseManager($configuration);

$task = new sfDoctrineBuildTask($configuration->getEventDispatcher(), new sfFormatter());
$task->run(array(), array('sql', 'db', 'and-load', 'no-confirmation', 'application' => $app));

$conn = Doctrine::getConnectionByTableName('Author');
$conn->beginTransaction();

$browser = new sfTestFunctional(new sfBrowser());

$browser->test()->info('Updating first author');
$firstAuthor = Doctrine::getTable('Author')->findOneBySlug('niko');

$browser->
  get('/author/'.$firstAuthor->slug.'/view')->
  with('response')->begin()->
    checkElement('h1', '/niko/')->
    checkElement('ul#comments li', 1)->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()
;

$browser->getContext(true)->switchTo('backend');
$firstAuthor->name = 'n1k0';
$firstAuthor->save($conn);

$browser->
  get('/author/'.$firstAuthor->slug.'/view')->
  with('response')->begin()->
    checkElement('h1', '/n1k0/')->
    checkElement('ul#comments li', 1)->
  end()->
  with('view_cache')->begin()->
    isCached(true, false)->
  end()
;

$conn->rollback();