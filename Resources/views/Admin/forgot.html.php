<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->set('bodyId', 'loginpage'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="formCentered">
		<h1><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.title')); ?></h1>

		<span id="adminLogo"></span>
		
		<?php if (2 == $step): ?>
			<?php if ($notFound): ?>
				<p class="form_errors"><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.notFoundKey')); ?></p>
			<?php else: ?>
				<?php if ($sent): ?>
					<p>
						<strong><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.saved')); ?></strong>
						<br />
						<a href="<?php echo $view['router']->path('nyrocms_admin_login'); ?>" class="btn btnLight">
							<?php echo $view['nyrocms_admin']->getIcon('reset'); ?>
							<span><?php echo $view['nyrodev']->trans('nyrocms.'.$trKey.'.back'); ?></span>
						</a>
					</p>
				<?php else: ?>
					<p><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.introPassword')); ?></p>

					<?php echo $view['form']->form($form); ?>
				<?php endif; ?>
			<?php endif; ?>
		<?php else: ?>
			<?php if ($sent): ?>
				<p>
					<strong><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.sent')); ?></strong>
					<br />
					<a href="<?php echo $view['router']->path('nyrocms_admin_login'); ?>" class="btn btnLight">
						<?php echo $view['nyrocms_admin']->getIcon('reset'); ?>
						<span><?php echo $view['nyrodev']->trans('nyrocms.'.$trKey.'.back'); ?></span>
					</a>
				</p>
			<?php else: ?>
				<p><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.intro')); ?></p>

				<?php if ($notFound): ?>
					<p class="form_errors"><?php echo nl2br($view['nyrodev']->trans('nyrocms.'.$trKey.'.notFound')); ?></p>
				<?php endif; ?>

				<?php echo $view['form']->form($form); ?>
			<?php endif; ?>
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>