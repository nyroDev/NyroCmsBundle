# <?php ?>

<?php echo strip_tags($view['translator']->trans('nyrocms.changedPassword.email.content', [
    '%name%' => $user->getFirstname().' '.$user->getLastName(),
])); ?>
