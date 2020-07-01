<?php if ($logged): ?>
<aside>
	<header>
		<h1>nyroCms</h1>
		<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_security_logout'); ?>" class="button"><?php echo $view['nyrodev']->trans('admin.misc.logout'); ?></a><br />
		<?php echo $view['nyrodev']->trans('admin.misc.admin'); ?>
		<strong><?php echo $view['nyrodev']->trans('admin.misc.site'); ?></strong>
	</header>
	
	<nav>
		<br />
		<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_account'); ?>" class="button"><?php echo $view['nyrodev']->trans('admin.misc.account'); ?></a>
		
		<?php if ($adminPerRoot): ?>
			<div class="selectCont">
				<select class="selectRedirect">
					<?php foreach ($rootContents as $id => $rootContent): ?>
					<option value="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_switch_rootContent', ['id' => $id]); ?>"<?php echo $id == $curRootId ? ' selected="selected"' : ''; ?>>
						<?php echo $rootContent->getTitle(); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>
		
		<?php foreach ($menu as $ident => $mm): ?>
			<h2><?php echo $view['nyrodev']->trans('admin.menu.'.$ident); ?></h2>
			<ul>
			<?php foreach ($mm as $m): ?>
				<li><?php echo '<a href="'.$m['uri'].'" '.($m['active'] ? ' class="active"' : '').(isset($m['_blank']) && $m['_blank'] ? ' target="_blank"' : '').'>'.$m['name'].'</a>'; ?></li>
			<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</nav>
</aside>
<?php endif; ?>