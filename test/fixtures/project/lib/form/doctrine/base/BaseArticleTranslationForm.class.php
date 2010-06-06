<?php

/**
 * ArticleTranslation form base class.
 *
 * @method ArticleTranslation getObject() Returns the current form's model object
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 29553 2010-05-20 14:33:00Z Kris.Wallsmith $
 */
abstract class BaseArticleTranslationForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'      => new sfWidgetFormInputHidden(),
      'title'   => new sfWidgetFormInputText(),
      'content' => new sfWidgetFormTextarea(),
      'lang'    => new sfWidgetFormInputHidden(),
      'slug'    => new sfWidgetFormInputText(),
    ));

    $this->setValidators(array(
      'id'      => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'title'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'content' => new sfValidatorString(array('max_length' => 5000, 'required' => false)),
      'lang'    => new sfValidatorChoice(array('choices' => array($this->getObject()->get('lang')), 'empty_value' => $this->getObject()->get('lang'), 'required' => false)),
      'slug'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'ArticleTranslation', 'column' => array('slug', 'lang', 'title')))
    );

    $this->widgetSchema->setNameFormat('article_translation[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ArticleTranslation';
  }

}
