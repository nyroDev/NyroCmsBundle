<ul class="tree<?php echo $canEditParent ? ' treeEditable' : ' treeNonEditable' ?>">
	<?php foreach ($contents as $content): ?>
	<li class="node node_<?php echo $content->getState() ?>">
		<input type="hidden" name="tree[]" value="<?php echo $content->getId() ?>" />
		<input type="hidden" name="treeLevel[<?php echo $content->getId() ?>]" value="<?php echo $content->getLevel() ?>" />
		<input type="hidden" name="treeChanged[<?php echo $content->getId() ?>]" value="0" />
		
		<?php
        $canEdit = $view['nyrocms_admin']->canAdminContent($content);
        $curCanHavSub = $view['nyrocms_admin']->canHaveSub($content);
        ?>
		
		<span class="nodeCont">
			<span>
				<a href="#" class="expand"><?php echo $view['nyrocms_admin']->getIcon('expand') ?></a>
				<a href="#" class="reduce"><?php echo $view['nyrocms_admin']->getIcon('reduce') ?></a>
				<strong><?php echo $content->getTitle() ?></strong>
				<?php if ($canEdit): ?>
					<?php if ($content->getContentHandler() && $content->getContentHandler()->getHasAdmin()): ?>
						<?php $handler = $view['nyrocms']->getHandler($content->getContentHandler()) ?>
						<?php if ($handler->hasAdminTreeLink()): ?>
							<a href="<?php echo $view['nyrodev']->generateUrl($handler->getAdminRouteName(), $handler->getAdminRoutePrm()) ?>" class="handlerContents" title="<?php echo $view['nyrodev']->trans('admin.misc.handlerContents') ?>">
								<?php echo $view['nyrocms_admin']->getIcon('arrow') ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<a href="<?php echo $view['nyrodev']->generateUrl('nyrocms_admin_composer', array('type' => 'Content', 'id' => $content->getId())) ?>" class="edit" target="_blank" title="<?php echo $view['nyrodev']->trans('admin.composer.title') ?>">
						<?php echo $view['nyrocms_admin']->getIcon('pencil') ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo $view['nyrocms']->getUrlFor($content, true, array('_locale' => $view['nyrocms']->getDefaultLocale($content))) ?>" target="_blank" title="<?php echo $view['nyrodev']->trans('admin.misc.watch') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('eye') ?>
				</a>
			</span>
			<?php if ($canEdit === true): ?>
				<a href="#" class="move" title="<?php echo $view['nyrodev']->trans('admin.content.drag') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('drag') ?>
				</a>
				<a href="<?php echo $view['nyrodev']->generateUrl($route.'_edit', array('id' => $content->getId())) ?>" class="edit" title="<?php echo $view['translator']->trans('admin.misc.edit') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('edit') ?>
				</a>
			<?php else: ?>
				<a href="#" class="moveDisabled disabled" title="<?php echo $view['nyrodev']->trans('admin.content.drag') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('drag') ?>
				</a>
				<a href="#" class="editDisabled disabled" title="<?php echo $view['translator']->trans('admin.misc.edit') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('edit') ?>
				</a>
			<?php endif; ?>
			<?php if ($canEdit && $curCanHavSub): ?>
				<a href="<?php echo $view['nyrodev']->generateUrl($route.'_add', array('pid' => $content->getId())) ?>" class="addNode" title="<?php echo $view['translator']->trans('admin.misc.add') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('add') ?>
				</a>
			<?php else: ?>
				<a href="#" class="addNodeDisabled disabled" title="<?php echo $view['translator']->trans('admin.misc.add') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('add') ?>
				</a>
			<?php endif; ?>
			<?php if ($canEdit === true): ?>
				<a href="<?php echo $view['nyrodev']->generateUrl($route.'_delete', array('id' => $content->getId())) ?>" class="delete" title="<?php echo $view['translator']->trans('admin.misc.delete') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('delete') ?>
				</a>
			<?php else: ?>
				<a href="#" class="deleteDisabled disabled" title="<?php echo $view['translator']->trans('admin.misc.delete') ?>">
					<?php echo $view['nyrocms_admin']->getIcon('delete') ?>
				</a>
			<?php endif; ?>
		</span>
		
		<?php if ($curCanHavSub): ?>
			<?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('NyroDevNyroCmsBundle:AdminData:contentTreeSub', array('parent' => $content))) ?>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
	<?php if ($canHaveSub): ?>
		<li class="node empty">
			<?php echo $view['nyrodev']->trans('admin.content.emptyTree') ?>
		</li>
	<?php endif; ?>
</ul>