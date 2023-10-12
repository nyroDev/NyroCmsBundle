<?php if ($admin): ?>
	<div data-name="contents_<?php echo $nb; ?>_image1" class="composableImgCont image1" data-cfg="<?php echo $view->escape(json_encode($config['image1'])); ?>">
		<?php echo $view->render('@NyroDevNyroCms/Composer/_image.html.php', [
		    'image' => $contents['image1'],
		    'title' => $row->getTitle(),
		    'class' => $view['nyrocms_composer']->canChangeMedia($row) ? 'composableImg' : '',
		    'config' => $config['image1'],
		]); ?>
		<textarea name="contents[<?php echo $nb; ?>][image1]" id="contents_<?php echo $nb; ?>_image1"><?php echo $contents['image1']; ?></textarea>
	</div>
	<div data-name="contents_<?php echo $nb; ?>_image2" class="composableImgCont image2" data-cfg="<?php echo $view->escape(json_encode($config['image2'])); ?>">
		<?php echo $view->render('@NyroDevNyroCms/Composer/_image.html.php', [
		    'image' => $contents['image2'],
		    'title' => $row->getTitle(),
		    'class' => $view['nyrocms_composer']->canChangeMedia($row) ? 'composableImg' : '',
		    'config' => $config['image2'],
		]); ?>
		<textarea name="contents[<?php echo $nb; ?>][image2]" id="contents_<?php echo $nb; ?>_image2"><?php echo $contents['image2']; ?></textarea>
	</div>
	<div class="text composable composableHtml" data-name="contents_<?php echo $nb; ?>_text">
	<?php echo $contents['text']; ?>
	</div>
	<textarea name="contents[<?php echo $nb; ?>][text]" id="contents_<?php echo $nb; ?>_text"><?php echo $contents['text']; ?></textarea>
<?php else: ?>
	<div class="image1">
		<?php echo $view->render('@NyroDevNyroCms/Composer/_image.html.php', [
		    'image' => $contents['image1'],
		    'title' => $row->getTitle(),
		    'class' => null,
		    'config' => $config['image1'],
		]); ?>
	</div>
	<div class="image2">
		<?php echo $view->render('@NyroDevNyroCms/Composer/_image.html.php', [
		    'image' => $contents['image2'],
		    'title' => $row->getTitle(),
		    'class' => null,
		    'config' => $config['image2'],
		]); ?>
	</div>
	<?php if (isset($contents['text']) && $contents['text'] && trim(strip_tags($contents['text']))): ?>
		<div class="text"><?php echo $view['nyrodev_image']->resizeImagesInHtml($contents['text'], false, true); ?></div>
	<?php endif; ?>
<?php endif; ?>