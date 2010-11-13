<?php use_helper('Text', 'I18N') ?>

<h1><?php echo $author['name'] ?></h1>

<ul id="comments">
  <?php foreach ($comments as $comment): ?>
    <li>
      <blockquote><?php echo $comment['content'] ?></blockquote>
    </li>
  <?php endforeach ?>
</ul>
