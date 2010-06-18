<?php use_helper('Text', 'I18N') ?>

<h1><?php echo $article['title'] ?></h1>

<?php echo simple_format_text($article['content']) ?>

<ul>
<?php foreach (
  sfConfig::get('app_cultures_available', array()) as $lang): ?>
  <li>
    <?php echo format_language($lang) ?>
    <?php echo link_to($article['Translation'][$lang]['title'], 
                       'article', array(
      'sf_subject' => $article['Translation'][$lang],
      'sf_culture' => $lang,
    )) ?>
  </li>
<?php endforeach; ?>
</ul>

<h3>Comments</h3>

<ul id="comments">
<?php foreach ($article['Comments'] as $comment): ?>
  <li>
    <p><?php echo $comment['Author']['name'] ?></p>
    <blockquote><?php echo $comment['content'] ?></blockquote>
  </li>
<?php endforeach; ?>
</ul>

<p>
  <?php echo link_to('Back to list', 'article_index', array(
    'sf_culture' => $sf_user->getCulture(),
  )) ?>
</p>