<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->set('bodyId', 'loginpage'); ?>

<?php $view['slots']->start('content'); ?>
	<article class="formCentered">
		<h1><?php echo $view['nyrodev']->trans('admin.misc.title'); ?></h1>
		<span id="adminLogo"></span>
		
		<p><?php echo nl2br($view['nyrodev']->trans('admin.misc.loginIntro')); ?></p>
		
		<?php if ($error): ?>
			<p class="form_errors"><?php
            $errorMsg = $view['nyrodev']->trans('login.errors.'.$error->getMessage());
		    if (0 === strpos($errorMsg, 'login.errors.')) {
		        $errorMsg = $view['nyrodev']->trans('login.errors.unknown');
		    }
		    echo $errorMsg;
		    ?></p>
		<?php endif; ?>

		<form action="<?php echo $view['router']->path('nyrocms_admin_security_check'); ?>" method="post">
			<div class="form_row form_row_email form_required">
				<label for="username"><?php echo $view['nyrodev']->trans('admin.user.email'); ?> <span class="formIndicator">*</span></label>
				<div class="iconWidget emailWidget">
					<?php echo $view['nyrocms_admin']->getIcon('email'); ?>
					<input type="email" id="username" name="_username" value="<?php echo $last_username; ?>" required placeholder="<?php echo $view['nyrodev']->trans('admin.user.email'); ?>" />
				</div>
			</div>

			<div class="form_row form_row_password form_required">
				<label for="password"><?php echo $view['nyrodev']->trans('admin.user.password'); ?> <span class="formIndicator">*</span></label>
				<div class="iconWidget emailWidget">
					<?php echo $view['nyrocms_admin']->getIcon('password'); ?>
					<nyro-password type="password" id="password" name="_password" required placeholder="<?php echo $view['nyrodev']->trans('admin.user.password'); ?>">
						<span slot="show"><?php echo $view['nyrocms_admin']->getIcon('hide'); ?></span>
						<span slot="hide"><?php echo $view['nyrocms_admin']->getIcon('show'); ?></span>
					</nyro-password>
				</div>
				<span class="forgotCont">
					<a href="<?php echo $view['router']->path('nyrocms_admin_forgot'); ?>" class="forgotLink"><?php echo $view['nyrodev']->trans('nyrocms.forgot.link'); ?></a>
				</span>
			</div>
			
			<input type="hidden" name="_csrf_token" value="<?php echo $view['form']->csrfToken('authenticate'); ?>" />

			<div class="form_button">
				<button type="submit">
					<?php echo $view['nyrocms_admin']->getIcon('send'); ?>
					<span><?php echo $view['nyrodev']->trans('admin.misc.login'); ?></span>
				</button>
			</div>
		</form>
	</article>
<?php $view['slots']->stop(); ?>