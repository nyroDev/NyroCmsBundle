<?php if ($admin): ?>
	<div data-name="contents_<?php echo $nb; ?>_image" class="composableImgCont image1" data-cfg="<?php echo $view->escape(json_encode($config['image'])); ?>">
		<?php echo $view->render('@NyroDevNyroCms/Composer/_image.html.php', array(
            'image' => $contents['image'],
            'title' => $row->getTitle(),
            'class' => $view['nyrocms_composer']->canChangeMedia($row) ? 'composableImg' : '',
            'config' => $config['image'],
        )); ?>
		<textarea name="contents[<?php echo $nb; ?>][image]" id="contents_<?php echo $nb; ?>_image"><?php echo $contents['image']; ?></textarea>
	</div>
<?php else: ?>
	<?php echo $view->render('@NyroDevNyroCms/Composer/_image.html.php', array(
        'image' => $contents['image'],
        'title' => $row->getTitle(),
        'class' => 'image1',
        'config' => $config['image'],
    )); ?>
<?php endif; ?>