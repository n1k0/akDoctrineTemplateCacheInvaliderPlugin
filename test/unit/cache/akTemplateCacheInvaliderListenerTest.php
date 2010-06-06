<?php
include dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(5, new lime_output_color());

class FakeDoctrineRecord extends sfDoctrineRecord
{
  public $slug, $other_slug;
}

$record = new FakeDoctrineRecord();
$record->slug = 'my-slug';
$record->other_slug = 'my-other-slug';

class FakeViewCacheManager extends sfViewCacheManager
{
}

$fakeViewCacheManager = new FakeViewCacheManager(sfContext::getInstance(), new sfNoCache(), array(
  'foo' => 'bar',
));

$listener = new akTemplateCacheInvaliderListener($configuration->getEventDispatcher(), $fakeViewCacheManager);

// ->computeRecordCacheUri()
$t->diag('->computeRecordCacheUri()');
$t->is($listener->computeRecordCacheUri($record, null), null, '->computeRecordCacheUri() returns nothing if cache uri is invalid');
$t->is($listener->computeRecordCacheUri($record, 'foo?bar=baz'), 'foo?bar=baz', '->computeRecordCacheUri() replaces nothing if no field placeholder is set');
$t->is($listener->computeRecordCacheUri($record, 'foo?bar=baz&slug=%slug%'), 'foo?bar=baz&slug=my-slug', '->computeRecordCacheUri() replaces a field placeholder by record column values');
$t->is($listener->computeRecordCacheUri($record, 'foo?bar=baz&slug=%slug%&other_slug=%other_slug%'), 'foo?bar=baz&slug=my-slug&other_slug=my-other-slug', '->computeRecordCacheUri() replaces multiple field placeholders by their column values');
$t->is($listener->computeRecordCacheUri($record, 'foo?bar=baz&slug=%unexistent%'), 'foo?bar=baz&slug=*', '->computeRecordCacheUri() replaces field placeholders by * if no match');