<h1>Articles</h1>

<ul>
<?php foreach ($articles as $article): ?>
  <li>
    <?php echo link_to($article['Translation'][$sf_user->getCulture()]['title'], 'article', (object) $article['Translation'][$sf_user->getCulture()]->getRawValue()) ?>
  </li>
<?php endforeach; ?>
</ul>