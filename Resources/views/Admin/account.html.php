<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="form">
		<h1><?php echo $view['nyrodev']->trans('admin.user.accountFields'); ?></h1>

		<?php if (isset($fields) && $fields): ?>
			<?php if (true === $fields): ?>
				<p><strong><?php echo nl2br($view['nyrodev']->trans('admin.user.accountFieldsSaved')); ?></strong></p>
			<?php else: ?>
				<?php echo $view['form']->form($fields); ?>
			<?php endif; ?>
			<hr />
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>