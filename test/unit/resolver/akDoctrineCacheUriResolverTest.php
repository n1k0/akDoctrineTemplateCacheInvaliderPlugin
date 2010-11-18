<?php
include dirname(__FILE__).'/../../bootstrap/functional.php';
require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

$t = new lime_test(42, new lime_output_color());

// ->checkCulture()
$t->diag('->checkCulture()');
$t->is(resolver(article(), 'foo?sf_culture=en')->checkCulture(), 'en', '->checkCulture() detects culture');
$t->is(resolver(article(), 'foo?sf_culture=fr_FR')->checkCulture(), 'fr_FR', '->checkCulture() detects culture in ISO format');
$t->is(resolver(article(), 'foo?sf_culture=pt_BR&bar=baz')->checkCulture(), 'pt_BR', '->checkCulture() detects culture when other parameters exists');
$t->is(resolver(article(), 'foo?bar=baz&sf_culture=pt_BR')->checkCulture(), 'pt_BR', '->checkCulture() detects culture whatever order is');
$t->is(resolver(article(), 'foo?sf_culture=*')->checkCulture(), null, '->checkCulture() does not detect any culture if none provided');

// ->checkPlaceholders()
$t->diag('->checkPlaceholders()');
$t->is(resolver(article(), 'foo?slug=yop&toto=boo')->checkPlaceholders(), array(), '->checkPlaceholders() finds no placeholder if no match');
$t->is(resolver(article(), 'foo?slug=%slug%')->checkPlaceholders(), array('slug'), '->checkPlaceholders() finds a single placeholder');
$t->is(resolver(article(), 'foo?slug=%slug%&foo=%bar%')->checkPlaceholders(), array('slug', 'bar'), '->checkPlaceholders() finds multiple placeholders');
$t->is(resolver(article(), 'foo?slug=coucou&foo=%bar%')->checkPlaceholders(), array('bar'), '->checkPlaceholders() finds placeholders when mixed');
$t->is(resolver(article(), 'foo?slug=%Article.slug%')->checkPlaceholders(), array('Article.slug'), '->checkPlaceholders() finds relation placeholders');

// ->checkTranslations()
$t->diag('->checkTranslations()');

// ->getRelatedFields()
$t->diag('->getRelatedFields()');
$t->is(resolver(comment(array('en')), 'foo?slug=%Article.slug%')->getRelatedFields(), array('%Article.slug%' => array(0 => 'en-slug')), '->getRelatedFields() retrieves related field for default language');
$t->is(resolver(comment(array('fr')), 'foo?slug=%Article.slug%')->getRelatedFields(), array('%Article.slug%' => array(0 => 'fr-slug')), '->getRelatedFields() retrieves related field for another language');
$t->is(resolver(comment(array('en', 'fr')), 'foo?slug=%Article.slug%')->getRelatedFields(), array('%Article.slug%' => array(0 => 'en-slug', 1 => 'fr-slug')), '->getRelatedFields() retrieves related field for multiple languages');
$t->is(resolver(comment(array('en', 'fr')), 'foo?sf_culture=fr&slug=%Article.slug%')->getRelatedFields(), array('%Article.slug%' => array(0 => 'fr-slug')), '->getRelatedFields() can limit retrieved fields for multiple languages');

// ->fetchRelatedValues()
$t->diag('->fetchRelatedValues()');
$t->is(resolver(article(array('en')), 'foo?slug=%slug%')->fetchRelatedValues(article(array('en')), 'slug'), array('en-slug'), '->fetchRelatedValues() fetches expected value for default language');
$t->is(resolver(article(array('fr')), 'foo?slug=%slug%')->fetchRelatedValues(article(array('fr')), 'slug'), array('fr-slug'), '->fetchRelatedValues() fetches expected value for another language');
$t->is(resolver(comment(array('en')), 'foo?slug=%Article.slug%')->fetchRelatedValues(article(array('en')), 'slug'), array('en-slug'), '->fetchRelatedValues() fetches expected related value for default language');
$t->is(resolver(comment(array('fr')), 'foo?slug=%Article.slug%')->fetchRelatedValues(article(array('fr')), 'slug'), array('fr-slug'), '->fetchRelatedValues() fetches expected related value for another language');
$t->is(resolver(article(array('en', 'fr')), 'foo?slug=%Article.slug%')->fetchRelatedValues(article(array('en', 'fr')), 'slug'), array('en-slug', 'fr-slug'), '->fetchRelatedValues() fetches expected value for multiple languages');
$t->is(resolver(comment(array('en')), 'foo?author=%Author.name%')->fetchRelatedValues(author('niko'), 'name'), array('niko'), '->fetchRelatedValues() fetches expected value for a model without i18n behaviour');
$t->is(resolver(article(array('en', 'fr')), 'foo?slug=%Article.slug%&author=%Author.name%')->fetchRelatedValues(article(array('en', 'fr')), 'slug'), array('en-slug', 'fr-slug'), '->fetchRelatedValues() fetches multiple expected values for multiple languages');
$t->is(resolver(author('niko'), 'foo?slug=%name%')->fetchRelatedValues(author('niko'), 'name'), array('niko'), '->fetchRelatedValues() fetches expected value for a flat record');
$t->is(resolver(author('niko'), 'foo?slug=%blah%')->fetchRelatedValues(author('niko'), 'blah'), array('*'), '->fetchRelatedValues() fallback to starred value on non-existant property');
# complex hydratation graph
$niko = Doctrine::getTable('Author')->createQuery('a')->leftJoin('a.Articles ar')->leftJoin('ar.Comments c')->fetchOne();
$t->is(resolver($niko, 'foo?slug=%name%')->fetchRelatedValues(author('niko'), 'name'), array('niko'), '->fetchRelatedValues() fetches expected value for a flat record');
$t->is(resolver($niko, 'foo?slug=%blah%')->fetchRelatedValues(author('niko'), 'blah'), array('*'), '->fetchRelatedValues() fallback to starred value on non-existant property');
# pk resolver
$testArticle = article(array('en'));
$t->is(resolver($testArticle, 'foo?id=%id%')->fetchRelatedValues($testArticle, 'id'), array(), '->fetchRelatedValues() does not fetch value for PK when record is not saved');
try {
  $testArticle->save();
} catch (Doctrine_Connection_Sqlite_Exception $e) {
  Doctrine::getTable('Article')->createQuery()->delete(); // I do love my job. Na, just kidding.
}
$result = resolver($testArticle, 'foo?id=%id%')->fetchRelatedValues($testArticle, 'id');
$t->ok(is_numeric(array_pop($result)), '->fetchRelatedValues() fetches value for PK when record is saved');
$testArticle->delete(); // surprisingly, this won't work, so the "hack" above

// ->hasTranslation()
$t->diag('->hasTranslation()');
$a = article(array('en'));
$t->is(resolver($a, 'foo')->hasTranslation($a, 'en'), true, '->hasTranslation() checks if record has given translation');
$t->is(resolver($a, 'foo')->hasTranslation($a, 'fr'), false, '->hasTranslation() checks if record has given translation');
$a = article(array('fr'));
$t->is(resolver($a, 'foo')->hasTranslation($a, 'en'), false, '->hasTranslation() checks if record has not given translation');
$t->is(resolver($a, 'foo')->hasTranslation($a, 'fr'), true, '->hasTranslation() checks if record has not given translation');

// ->computeUris()
$t->diag('->computeUris()');
$t->is(resolver(article(array('en')), 'foo?slug=%slug%')->computeUris(), array('foo?slug=en-slug'), '->computeUris() computes i18n cache uri including field value in default language');
$t->is(resolver(article(array('fr')), 'foo?slug=%slug%')->computeUris(), array('foo?slug=fr-slug'), '->computeUris() computes i18n cache uri including field value in another language');
$t->is(resolver(article(array('en')), 'foo?slug=%slug%&author=%Author.name%')->computeUris(), array('foo?slug=en-slug&author=henry'), '->computeUris() computes cache uri including several fields from relations');
$t->is(resolver(article(array('en', 'fr')), 'foo?slug=%slug%')->computeUris(), array('foo?slug=en-slug', 'foo?slug=fr-slug'), '->computeUris() computes i18n cache uris including field value with multiple language');
$t->is(resolver(article(array('en', 'fr')), 'foo?sf_culture=en&slug=%slug%')->computeUris(), array('foo?sf_culture=en&slug=en-slug'), '->computeUris() computes i18n cache uris when sf_culture is set to default language');
$t->is(resolver(article(array('en', 'fr')), 'foo?sf_culture=fr&slug=%slug%')->computeUris(), array('foo?sf_culture=fr&slug=fr-slug'), '->computeUris() computes i18n cache uris when sf_culture is set to another language');
$t->is(resolver(article(array('en', 'fr')), 'foo?sf_culture=*&slug=%slug%')->computeUris(), array(0 => 'foo?sf_culture=*&slug=en-slug', 1 => 'foo?sf_culture=*&slug=fr-slug'), '->computeUris() computes i18n cache uris when sf_culture is set to any language');
$t->is(resolver(article(array('en', 'fr')), 'foo?slug=%slug%&author=%Author.name%')->computeUris(), array(0 => 'foo?slug=en-slug&author=henry', 1 => 'foo?slug=fr-slug&author=henry'), '->computeUris() computes cache uri including several fields from relations');
$t->is(resolver(comment(array('en')), 'foo?slug=%Article.slug%')->computeUris(), array('foo?slug=en-slug'), '->computeUris() computes i18n cache uri with a foreign relation value in default language');
$t->is(resolver(comment(array('fr')), 'foo?slug=%Article.slug%')->computeUris(), array('foo?slug=fr-slug'), '->computeUris() computes i18n cache uri with a foreign relation value in another language');
$t->is(resolver(comment(array('en', 'fr')), 'foo?slug=%Article.slug%')->computeUris(), array('foo?slug=en-slug', 'foo?slug=fr-slug'), '->computeUris() computes i18n cache uri with a foreign relation value in multiple language');

// Test Helpers
function article(array $langs = array())
{
  $article = new Article();
  $article->setAuthor(author('henry'));
  foreach ($langs as $lang)
  {
    $article->Translation[$lang]->fromArray(array(
      'title' => 'Title in '.$lang,
      'slug'  => $lang.'-slug',
    ));
  }
  return $article;
}
function comment(array $articleLangs = array())
{
  $comment = new Comment();
  $comment->setAuthor(author('niko'));
  $comment->content = 'Booh.';
  $comment->setArticle(article($articleLangs));
  return $comment;
}
function author($name)
{
  $author = new Author();
  $author->name = $name;
  return $author;
}

function resolver($record, $cacheUri)
{
  return new akDoctrineCacheUriResolver($record, $cacheUri);
}