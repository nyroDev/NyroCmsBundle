<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<article>
		<h1><?php echo $parent->getTitle(); ?></h1>
		
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

		<?php if ($canRootComposer && $view['nyrocms_admin']->canAdminContent($parent)): ?>
			<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', ['type' => 'Content', 'id' => $parent->getId()]); ?>" id="composerHomeEdit" target="_blank" title="<?php echo $view['nyrodev']->trans('admin.composer.title'); ?>">
				<?php echo $view['nyrocms_admin']->getIcon('pencil'); ?>
				<?php echo $view['nyrodev']->trans('admin.composer.rootEdit'); ?>
			</a>
		<?php endif; ?>

		<form action="" method="post" id="contentTree">
			<a href="#" class="expandAll"><?php echo $view['nyrodev']->trans('admin.content.expandAll'); ?></a>
			<a href="#" class="reduceAll"><?php echo $view['nyrodev']->trans('admin.content.reduceAll'); ?></a><br />
			<div class="listButtons">
				<?php if ($candDirectAdd): ?>
				<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_data_content_add', ['pid' => $parent->getId()]); ?>" class="button add">
					<?php echo $view['nyrocms_admin']->getIcon('add'); ?>
					<?php echo $view['translator']->trans('admin.misc.add'); ?>
				</a>
				<?php endif; ?>
				<button type="submit" class="button"><?php echo $view['nyrodev']->trans('admin.misc.send'); ?></button>
			</div>
			<?php echo $view['actions']->render(new Symfony\Component\HttpKernel\Controller\ControllerReference('NyroDev\NyroCmsBundle\Controller\AdminDataController::contentTreeSub', [
			    'parent' => $parent,
			])); ?>
			<div class="listButtons">
				<?php if ($candDirectAdd): ?>
				<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_data_content_add', ['pid' => $parent->getId()]); ?>" class="button add">
					<?php echo $view['nyrocms_admin']->getIcon('add'); ?>
					<?php echo $view['translator']->trans('admin.misc.add'); ?>
				</a>
				<?php endif; ?>
				<button type="submit" class="button"><?php echo $view['nyrodev']->trans('admin.misc.send'); ?></button>
			</div>
		</form>
	</article>
<?php $view['slots']->stop(); ?>