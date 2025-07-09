<?php $view->extend('@NyroDevNyroCms/email/_layout.html.php'); ?>

<?php $view['slots']->set('emailTitle', $subject); ?>

<?php $view['slots']->start('content'); ?>
<?php echo $view->render('@NyroDevNyroCms/email/_title.html.php', [
    'title' => $subject,
]); ?>
<?php echo $view->render('@NyroDevNyroCms/email/_content.html.php', [
    'content' => nl2br($content),
]); ?>
<?php $view['slots']->stop(); ?>