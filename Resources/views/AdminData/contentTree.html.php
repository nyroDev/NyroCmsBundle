<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article>
		<?php echo $view->render('@NyroDevNyroCms/AdminTpl/breadcrumbs.html.php', [
		    'title' => $parent->getTitle(),
		]); ?>
		<h1>
			<?php echo $view['nyrocms_admin']->getIcon('tree'); ?>
			<?php echo $parent->getTitle(); ?>
		</h1>
		
		<?php
            $introKeys = [
                'admin.content.intro_'.$parent->getHandler(),
                'admin.content.intro',
            ];
foreach ($introKeys as $introKey) {
    $intro = $view['translator']->trans($introKey);
    if ($intro && $intro != $introKey) {
        echo '<p class="intro">'.nl2br($intro).'</p>';
        break;
    }
}
?>

		<form action="" method="post" id="contentTree">
			<nav class="toolbar">
				<?php if ($canRootComposer && $view['nyrocms_admin']->canAdmin($parent)): ?>
					<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', ['type' => 'Content', 'id' => $parent->getId()]); ?>" target="_blank" class="btn btnGrey">
						<?php echo $view['nyrocms_admin']->getIcon('composer'); ?>
						<span><?php echo $view['nyrodev']->trans('admin.composer.rootEdit'); ?></span>
					</a>
				<?php endif; ?>

				<a href="#" class="btn btnGrey expandAll">
					<?php echo $view['nyrocms_admin']->getIcon('treeExpand'); ?>
					<span><?php echo $view['nyrodev']->trans('admin.content.expandAll'); ?></span>
				</a>
				<a href="#" class="btn btnGrey reduceAll">
					<?php echo $view['nyrocms_admin']->getIcon('treeReduce'); ?>
					<span><?php echo $view['nyrodev']->trans('admin.content.reduceAll'); ?></span>
				</a>

				<a href="" class="btn cancel">
					<?php echo $view['nyrocms_admin']->getIcon('reset'); ?>
					<span><?php echo $view['translator']->trans('admin.misc.cancel'); ?></span>
				</a>
				<?php if ($candDirectAdd): ?>
					<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_data_content_add', ['pid' => $parent->getId()]); ?>" class="btn add">
						<?php echo $view['nyrocms_admin']->getIcon('add'); ?>
						<span><?php echo $view['translator']->trans('admin.misc.add'); ?></span>
					</a>
				<?php endif; ?>
				<button type="submit" class="button disabled">
					<?php echo $view['nyrocms_admin']->getIcon('save'); ?>
					<span><?php echo $view['nyrodev']->trans('admin.misc.send'); ?></span>
				</button>
			</nav>

			<?php echo $view->render('@NyroDevNyroCms/AdminData/contentTreeSub.html.php', [
			    'parent' => $parent,
			]); ?>
		</form>
	</article>
<?php $view['slots']->stop(); ?>