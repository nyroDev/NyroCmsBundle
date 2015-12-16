<?php if ($admin): ?>
	<div data-name="contents_<?php echo $nb ?>_image" class="composableImgCont image1" data-w="<?php echo $config['image']['w'] ?>" data-h="<?php echo $config['image']['h'] ?>">
		<?php echo $view->render('NyroDevNyroCmsBundle:Composer:_image.html.php', array(
			'image'=>$contents['image'],
			'title'=>$row->getTitle(),
			'class'=>'composableImg',
			'config'=>$config['image']
		)) ?>
		<textarea name="contents[<?php echo $nb ?>][image]" id="contents_<?php echo $nb ?>_image"><?php echo $contents['image'] ?></textarea>
	</div>

	<?php if ($handlerInContentsKey === 'text'): ?>
		<?php echo $handlerInContent ?>
	<?php else: ?>
		<div class="text composable composableHtml" data-name="contents_<?php echo $nb ?>_text">
		<?php echo $contents['text'] ?>
		</div>
	<?php endif; ?>
	<textarea name="contents[<?php echo $nb ?>][text]" id="contents_<?php echo $nb ?>_text"><?php echo $contents['text'] ?></textarea>
<?php else: ?>
	<?php echo $view->render('NyroDevNyroCmsBundle:Composer:_image.html.php', array(
		'image'=>$contents['image'],
		'title'=>$row->getTitle(),
		'class'=>'image1',
		'config'=>$config['image']
	)) ?>
	<?php if ($handlerInContentsKey === 'text'): ?> 
		<?php echo $handlerInContent ?>
	<?php elseif (isset($contents['text']) && $contents['text'] && trim(strip_tags($contents['text']))): ?>
		<div class="text"><?php echo $view['nyrodev_image']->resizeImagesInHtml($contents['text'], false, true) ?></div>
	<?php endif; ?>
<?php endif; ?>