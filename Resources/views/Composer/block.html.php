<div class="composerBlock block_<?php echo $nb ?> block_<?php echo $block['type'] ?><?php echo $customClass ? ' '.$customClass : '' ?>"<?php echo $admin ? ' data-nb="'.$nb.'"' : ''?><?php echo $customAttrs ? ' '.$customAttrs : '' ?>>
	<?php
	$handlerIndicator = NyroDev\NyroCmsBundle\Handler\AbstractHandler::TEMPLATE_INDICATOR;
	$handlerInContentsKey = array_search($handlerIndicator , $block['contents'], true);
	$handlerInContent = $handlerInContentsKey ? $view->render($view['nyrocms_composer']->getBlockTemplate($row, 'handler'), array(
		'nb'=>$nb,
		'row'=>$row,
		'config'=>$view['nyrocms_composer']->getBlockConfig($row, 'handler'),
		'handlerContent'=>$handlerContent,
		'admin'=>$admin,
		'isWrapped'=>true
	)): null;
	?>
	<?php if ($admin): ?>
		<input type="hidden" name="contentsKey[]" value="<?php echo $nb ?>" />
		<input type="hidden" name="contentsType[<?php echo $nb ?>]" value="<?php echo $block['type'] ?>" />
		<div class="composerButtons">
			<?php if ($block['type'] != 'handler' && !$handlerInContentsKey): ?>
				<a href="#" class="composerDelete"><?php echo $view['nyrocms_admin']->getIcon('delete').'<span> '.$view['nyrodev']->trans('admin.composer.action.delete') ?></span></a>
			<?php endif; ?>
			<a href="#" class="composerDrag"><?php echo $view['nyrocms_admin']->getIcon('drag').'<span> '.$view['nyrodev']->trans('admin.composer.action.drag') ?></span></a>
		</div>
	<?php endif; ?>
	<?php echo $view->render($view['nyrocms_composer']->getBlockTemplate($row, $block['type']), array(
		'nb'=>$nb,
		'row'=>$row,
		'config'=>$view['nyrocms_composer']->getBlockConfig($row, $block['type']),
		'handlerContent'=>$handlerContent,
		'handlerIndicator'=>$handlerIndicator,
		'handlerInContentsKey'=>$handlerInContentsKey,
		'handlerInContent'=>$handlerInContent,
		'contents'=>$block['contents'],
		'admin'=>$admin
	)) ?>
</div>