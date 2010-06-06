<?php

/**
 * content actions.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage content
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class contentActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->articles = Doctrine::getTable('Article')
      ->createQuery('a')
      ->leftJoin('a.Translation at INDEXBY at.lang')
      ->execute()
    ;
  }
  
  public function executeArticle(sfWebRequest $request)
  {
    $this->article = $this->getRoute()->getObject();
  }
}
