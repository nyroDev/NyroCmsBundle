<?php $view->extend('@NyroDevNyroCms/email/_layout.html.php'); ?>

<?php $view['slots']->set('emailTitle', $subject); ?>

<?php $view['slots']->start('content'); ?>
<?php echo $view->render('@NyroDevNyroCms/email/_title.html.php', [
    'title' => $subject,
]); ?>
<?php echo $view->render('@NyroDevNyroCms/email/_content.html.php', [
    'content' => nl2br($view['translator']->trans('nyrocms.forgot.email.content', [
        '%name%' => $user->getFirstname().' '.$user->getLastName(),
    ])),
]); ?>
<?php echo $view->render('@NyroDevNyroCms/email/_cta.html.php', [
    'url' => $url,
    'text' => $view['translator']->trans('nyrocms.forgot.email.cta'),
]); ?>
<?php echo $view->render('@NyroDevNyroCms/email/_content.html.php', [
    'content' => nl2br($view['translator']->trans('nyrocms.forgot.email.contentFooter', [
        '%name%' => $user->getFirstname().' '.$user->getLastName(),
    ])),
]); ?>
<?php $view['slots']->stop(); ?>