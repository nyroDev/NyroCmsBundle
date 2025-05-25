<?php
$route = 'nyrocms_admin_data_content';
$contents = $view['nyrocms_admin']->getTreeChildren($parent, true);
?>
<ul class="tree <?php echo $view['nyrocms_admin']->canAdminContent($parent) ? 'treeEditable' : 'treeNonEditable'; ?>">
	<?php foreach ($contents as $content): ?>
	<li class="node node_<?php echo $content->getState(); ?>">
		<input type="hidden" name="tree[]" value="<?php echo $content->getId(); ?>" />
		<input type="hidden" name="treeLevel[<?php echo $content->getId(); ?>]" value="<?php echo $content->getLevel(); ?>" />
		<input type="hidden" name="treeChanged[<?php echo $content->getId(); ?>]" value="0" />

		<?php
        $canEdit = $view['nyrocms_admin']->canAdminContent($content);
	    $maxLevel = $view['nyrocms_admin']->getContentMaxLevel($content);
	    $curCanHavSub = $view['nyrocms_admin']->canHaveSub($content);
	    ?>

		<input type="checkbox" id="expandToggle_<?php echo $content->getId(); ?>" class="expandToggle" />
		<span class="nodeCont">
			<a href="#" class="drag <?php echo $canEdit ? 'dragHandle' : 'disabled'; ?>" title="<?php echo $view['nyrodev']->trans('admin.content.drag'); ?>">
				<?php echo $view['nyrocms_admin']->getIcon('drag'); ?>
			</a>
			<strong><?php echo $content->getTitle(); ?></strong>
			<span class="flexSpacer"></span>
			<?php if ($curCanHavSub): ?>
				<label for="expandToggle_<?php echo $content->getId(); ?>" class="toggleSub">
					<?php echo $view['nyrocms_admin']->getIcon('chevron'); ?>
				</label>
			<?php endif; ?>

			<input type="checkbox" id="menuToggle_<?php echo $content->getId(); ?>" class="menuToggle" />
			<label for="menuToggle_<?php echo $content->getId(); ?>" class="menuToggleLabel">
				<?php echo $view['nyrocms_admin']->getIcon('dots'); ?>
			</label>
			<nav class="menuNode">
				<a href="<?php echo $view['nyrocms']->getUrlFor($content, true, ['_locale' => $view['nyrocms']->getDefaultLocale($content)]); ?>" target="_blank">
					<?php echo $view['nyrocms_admin']->getIcon('show'); ?>
					<?php echo $view['nyrodev']->trans('admin.misc.watch'); ?>
				</a>
				<?php if ($canEdit): ?>
					<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', ['type' => 'Content', 'id' => $content->getId()]); ?>" target="_blank">
						<?php echo $view['nyrocms_admin']->getIcon('composer'); ?>
						<?php echo $view['nyrodev']->trans('admin.composer.title'); ?>
					</a>
					<a href="<?php echo $view['nyrodev']->generateUrl($route.'_edit', ['id' => $content->getId()]); ?>">
						<?php echo $view['nyrocms_admin']->getIcon('edit'); ?>
						<?php echo $view['translator']->trans('admin.misc.edit'); ?>
					</a>
					<?php if ($curCanHavSub): ?>
						<a href="<?php echo $view['nyrodev']->generateUrl($route.'_add', ['pid' => $content->getId()]); ?>">
							<?php echo $view['nyrocms_admin']->getIcon('treeAdd'); ?>
							<?php echo $view['translator']->trans('admin.misc.add'); ?>
						</a>
					<?php endif; ?>
					<?php if ($content->getContentHandler() && $content->getContentHandler()->getHasAdmin()): ?>
						<?php $handler = $view['nyrocms']->getHandler($content->getContentHandler()); ?>
						<?php if ($handler->hasAdminTreeLink()): ?>
							<a href="<?php echo $view['nyrodev']->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm()); ?>">
								<?php echo $view['nyrocms_admin']->getIcon('misc'); ?>
								<?php echo $view['nyrodev']->trans('admin.misc.handlerContents'); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<a href="<?php echo $view['nyrodev']->generateUrl($route.'_delete', ['id' => $content->getId()]); ?>" class="delete">
						<?php echo $view['nyrocms_admin']->getIcon('delete'); ?>
						<?php echo $view['translator']->trans('admin.misc.delete'); ?>
					</a>
				<?php endif; ?>
			</nav>
		</span>
		
		<?php if ($curCanHavSub): ?>
			<?php echo $view->render('@NyroDevNyroCms/AdminData/contentTreeSub.html.php', [
			    'parent' => $content,
			]); ?>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
	<?php if ($view['nyrocms_admin']->canHaveSub($parent)): ?>
		<li class="node empty">
			<?php echo $view['nyrodev']->trans('admin.content.emptyTree'); ?>
		</li>
	<?php endif; ?>
</ul>