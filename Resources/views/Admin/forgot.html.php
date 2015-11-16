<?php $view->extend('NyroDevNyroCmsBundle:Admin:_layout.html.php') ?>

<?php $view['slots']->set('bodyId', 'loginpage') ?>

<?php $view['slots']->start('content') ?>
	<article>
		<h1><?php echo $view['nyrodev']->trans('admin.misc.title') ?></h1>
		<div class="formCentered">

			<span id="adminLogo"><?php echo $view['nyrodev']->trans('public.header.headline') ?></span>
			
			<?php if ($step == 2): ?>
				<?php if ($notFound): ?>
					<p class="form_errors"><?php echo nl2br($view['nyrodev']->trans('public.forgot.notFoundKey')) ?></p>
				<?php else: ?>
					<?php if ($sent): ?>
						<p><strong><?php echo nl2br($view['nyrodev']->trans('public.forgot.saved')) ?></strong></p>
						<a href="<?php echo $view['router']->generate('nyrocms_admin_login') ?>" class="forgotLink"><?php echo $view['nyrodev']->trans('public.forgot.back') ?></a>
					<?php else: ?>
						<p><?php echo nl2br($view['nyrodev']->trans('public.forgot.introPassword')) ?></p>

						<?php echo $view['form']->form($form) ?>
						
						<a href="<?php echo $view['router']->generate('nyrocms_admin_login') ?>" class="forgotLink"><?php echo $view['nyrodev']->trans('public.forgot.cancel') ?></a>
					<?php endif; ?>
				<?php endif; ?>
			<?php else: ?>
				<?php if ($sent): ?>
					<p><strong><?php echo nl2br($view['nyrodev']->trans('public.forgot.sent')) ?></strong></p>
				<?php else: ?>
					<p><?php echo nl2br($view['nyrodev']->trans('public.forgot.intro')) ?></p>

					<?php if ($notFound): ?>
						<p class="form_errors"><?php echo nl2br($view['nyrodev']->trans('public.forgot.notFound')) ?></p>
					<?php endif; ?>

					<?php echo $view['form']->form($form) ?>

					<a href="<?php echo $view['router']->generate('nyrocms_admin_login') ?>" class="forgotLink"><?php echo $view['nyrodev']->trans('public.forgot.cancel') ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</article>
<?php $view['slots']->stop() ?>