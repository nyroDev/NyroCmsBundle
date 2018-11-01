<?php $view->extend('NyroDevNyroCmsBundle:Admin:_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="form">
		<h2><?php echo $view['nyrodev']->trans('admin.misc.account'); ?></h2>

		<?php if (isset($fields) && $fields): ?>
			<h1><?php echo $view['nyrodev']->trans('admin.user.accountFields'); ?></h1>
			<?php if (true === $fields): ?>
				<p><strong><?php echo nl2br($view['nyrodev']->trans('admin.user.accountFieldsSaved')); ?></strong></p>
			<?php else: ?>
				<?php echo $view['form']->form($fields); ?>
			<?php endif; ?>
			<hr />
		<?php endif; ?>
		
		<?php if (isset($password) && $password): ?>
			<h1><?php echo $view['nyrodev']->trans('admin.user.accountPassword'); ?></h1>
			<?php if (true === $password): ?>
				<p><strong><?php echo nl2br($view['nyrodev']->trans('admin.user.accountPasswordSaved')); ?></strong></p>
			<?php else: ?>
				<?php echo $view['form']->form($password); ?>
			<?php endif; ?>
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>