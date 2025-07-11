# <?php echo $subject; ?>

<?php echo strip_tags($view['translator']->trans('nyrocms.forgot.email.content', [
    '%name%' => $user->getFirstname().' '.$user->getLastName(),
])); ?>

<?php echo $url; ?>

<?php echo strip_tags($view['translator']->trans('nyrocms.forgot.email.contentFooter', [
    '%name%' => $user->getFirstname().' '.$user->getLastName(),
])); ?>
