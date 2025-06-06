<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="form">
		<?php echo $view->render('@NyroDevNyroCms/AdminTpl/breadcrumbs.html.php', [
		    'title' => $view['translator']->trans('admin.user.accountFields'),
		]); ?>

		<h1><?php echo $view['nyrodev']->trans('admin.user.accountFields'); ?></h1>

		<?php if (isset($fields) && $fields): ?>
			<?php if (true === $fields): ?>
				<p><strong><?php echo nl2br($view['nyrodev']->trans('admin.user.accountFieldsSaved')); ?></strong></p>
			<?php else: ?>
				<?php echo $view['form']->form($fields); ?>
			<?php endif; ?>
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>