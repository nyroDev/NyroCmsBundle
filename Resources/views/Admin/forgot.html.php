<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->set('bodyId', 'loginpage'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="formCentered">
		<h1><?php echo $view['nyrodev']->trans('admin.misc.title'); ?></h1>

		<span id="adminLogo"></span>
		
		<?php if (2 == $step): ?>
			<?php if ($notFound): ?>
				<p class="form_errors"><?php echo nl2br($view['nyrodev']->trans('nyrocms.forgot.notFoundKey')); ?></p>
			<?php else: ?>
				<?php if ($sent): ?>
					<p>
						<strong><?php echo nl2br($view['nyrodev']->trans('nyrocms.forgot.saved')); ?></strong>
						<br />
						<a href="<?php echo $view['router']->path('nyrocms_admin_login'); ?>" class="btn btnLight">
							<?php echo $view['nyrocms_admin']->getIcon('reset'); ?>
							<span><?php echo $view['nyrodev']->trans('nyrocms.forgot.back'); ?></span>
						</a>
					</p>
				<?php else: ?>
					<p><?php echo nl2br($view['nyrodev']->trans('nyrocms.forgot.introPassword')); ?></p>

					<?php echo $view['form']->form($form); ?>
				<?php endif; ?>
			<?php endif; ?>
		<?php else: ?>
			<?php if ($sent): ?>
				<p>
					<strong><?php echo nl2br($view['nyrodev']->trans('nyrocms.forgot.sent')); ?></strong>
					<br />
					<a href="<?php echo $view['router']->path('nyrocms_admin_login'); ?>" class="btn btnLight">
						<?php echo $view['nyrocms_admin']->getIcon('reset'); ?>
						<span><?php echo $view['nyrodev']->trans('nyrocms.forgot.back'); ?></span>
					</a>
				</p>
			<?php else: ?>
				<p><?php echo nl2br($view['nyrodev']->trans('nyrocms.forgot.intro')); ?></p>

				<?php if ($notFound): ?>
					<p class="form_errors"><?php echo nl2br($view['nyrodev']->trans('nyrocms.forgot.notFound')); ?></p>
				<?php endif; ?>

				<?php echo $view['form']->form($form); ?>
			<?php endif; ?>
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>