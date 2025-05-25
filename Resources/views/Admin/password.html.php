<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="form">
		<h1><?php echo $view['nyrodev']->trans('admin.menu.password'); ?></h1>
		
		<?php if (isset($password) && $password): ?>
			<?php if (true === $password): ?>
				<p><strong><?php echo nl2br($view['nyrodev']->trans('admin.user.accountPasswordSaved')); ?></strong></p>
			<?php else: ?>
				<?php echo $view['form']->form($password); ?>
			<?php endif; ?>
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>