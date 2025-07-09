# <?php ?>

<?php echo strip_tags($view['translator']->trans('nyrocms.welcome.email.content', [
    '%name%' => $user->getFirstname().' '.$user->getLastName(),
])); ?>

<?php echo $url; ?>

<?php echo strip_tags($view['translator']->trans('nyrocms.welcome.email.contentFooter', [
    '%name%' => $user->getFirstname().' '.$user->getLastName(),
])); ?>
