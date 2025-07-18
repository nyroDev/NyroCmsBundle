<nyro-swiper>
    <?php if ($images && is_array($images)): ?>
        <?php foreach ($images as $image): ?>
            <img src="<?php echo $view['router']->path('nyrodev_assets_resize', [
                'dims' => '1200x1200',
                'path' => substr($image['src'], 1),
            ]); ?>" alt="<?php echo $image['alt']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" loading="lazy" />
        <?php endforeach; ?>
    <?php endif; ?>

    <?php echo $view->render('@NyroDevNyroCms/Composer/item/_slideshowNav.html.php', [
        'row' => $row,
        'images' => $images,
    ]); ?>
</nyro-swiper>