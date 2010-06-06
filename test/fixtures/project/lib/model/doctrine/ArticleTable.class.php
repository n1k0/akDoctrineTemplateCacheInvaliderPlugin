<?php
class ArticleTable extends Doctrine_Table
{
  public static function getInstance()
  {
    return Doctrine_Core::getTable('Article');
  }
  
  public function doSelectForSlug(array $parameters = array())
  {
    $id = Doctrine::getTable('ArticleTranslation')
      ->createQuery('t')
      ->select('t.id')
      ->where('t.slug = ?', $parameters['slug'])
      ->execute(array(), Doctrine_Core::HYDRATE_SINGLE_SCALAR)
    ;
    
    return $this->createQuery('a')
      ->leftJoin('a.Translation at INDEXBY at.lang')
      ->where('a.id = ?', $id)
      ->fetchOne()
    ;
  }
}