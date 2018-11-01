<?php $view->extend('NyroDevNyroCmsBundle:Admin:_layout.html.php'); ?>

<?php $view['slots']->set('bodyId', 'loginpage'); ?>

<?php $view['slots']->start('content'); ?>
	<article>
		<h1><?php echo $view['nyrodev']->trans('admin.misc.title'); ?></h1>
		<div class="formCentered">

			<span id="adminLogo"><?php echo $view['nyrodev']->trans('admin.misc.headline'); ?></span>
			
			<p><?php echo nl2br($view['nyrodev']->trans('admin.misc.loginIntro')); ?></p>
			
			<?php if ($error): ?>
				<p class="form_errors"><?php echo $view['nyrodev']->trans('login.errors.'.$error->getMessage()); ?></p>
			<?php endif; ?>

			<form action="<?php echo $view['router']->path('nyrocms_admin_security_check'); ?>" method="post">
				<label for="username"><?php echo $view['nyrodev']->trans('admin.user.email'); ?> :</label>
				<input type="email" id="username" name="_username" value="<?php echo $last_username; ?>" required="required" placeholder="<?php echo $view['nyrodev']->trans('admin.user.email'); ?>" />

				<label for="password"><?php echo $view['nyrodev']->trans('admin.user.password'); ?> :</label>
				<input type="password" id="password" name="_password" required="required" placeholder="<?php echo $view['nyrodev']->trans('admin.user.password'); ?>" />
				
				<button type="submit"><?php echo $view['nyrodev']->trans('admin.misc.login'); ?></button>
			</form>
			<a href="<?php echo $view['router']->path('nyrocms_admin_forgot'); ?>" class="forgotLink"><?php echo $view['nyrodev']->trans('nyrocms.forgot.link'); ?></a>
		</div>
	</article>
<?php $view['slots']->stop(); ?>