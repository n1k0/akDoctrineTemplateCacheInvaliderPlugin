<?php
include dirname(__FILE__).'/../bootstrap/functional.php';
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

new sfDatabaseManager($configuration);

$t = new lime_test(4, new lime_output_color());

$t->info('Setting up frontend sfContext...');

sfContext::createInstance(ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true));

// frontend
$t->info(sprintf('Frontend: enabled listener: %s', sfConfig::get('app_akDoctrineTemplateCacheInvaliderPlugin_enabled_listener') ? 'true' : 'false'));

$articleListener = Doctrine_Core::getTable('Article')->getRecordListener();
$t->isnt(get_class($articleListener), 'Doctrine_Record_Listener_Chain', 'Article Table has no listener on frontend');

// backend
sfContext::switchTo('backend');
$t->info(sprintf('Backend: enabled listener: %s', sfConfig::get('app_akDoctrineTemplateCacheInvaliderPlugin_enabled_listener') ? 'true' : 'false'));

$articleListener = Doctrine_Core::getTable('Article')->getRecordListener();
$t->is(get_class($articleListener), 'Doctrine_Record_Listener_Chain', 'Article Table has some listeners.');
$t->isnt($articleListener->get('akTemplateCacheInvaliderListener'), null, 'Article Table has a listener named has "akTemplateCacheInvaliderListener".');
$t->is(get_class($articleListener->get('akTemplateCacheInvaliderListener')), 'akTemplateCacheInvaliderListener', 'Article Table listener named "akTemplateCacheInvaliderListener" is good.');
