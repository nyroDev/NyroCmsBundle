<?php if (isset($sent) && $sent): ?>
	<div class="block_text">
		<p><strong><?php echo nl2br($view['nyrodev']->trans('nyrocms.handler.contact.sent')); ?></strong></p>
	</div>
<?php else: ?>
	<?php
    $formView = $view['form']->form($form);
    if ($isAdmin) {
        $formView = str_replace(
            ['<form ', '</form>', 'required="required"'],
            ['<div ', '</div>', ''],
            $formView);
    }
    echo $formView;
    ?>
<?php endif; ?>