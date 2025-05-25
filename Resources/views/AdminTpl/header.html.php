<input type="checkbox" id="switch_adminMenu" />
<header>
	<h1>
		<?php echo $view['nyrodev']->trans('admin.misc.admin'); ?>
		<strong><?php echo $view['nyrodev']->trans('admin.misc.site'); ?></strong>
	</h1>
	<?php if ($logged): ?>
		<label for="switch_adminMenu">Menu</label>
	<?php endif; ?>
</header>
<?php if ($logged): ?>
	<aside id="adminNav">
		<?php echo $view->render($menu->getTemplate(), ['menu' => $menu]); ?>
	</aside>
<?php endif; ?>