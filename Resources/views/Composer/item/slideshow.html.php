<nyro-swiper>
    <?php if ($images && is_array($images)): ?>
        <?php foreach ($images as $image): ?>
            <img <?php echo $view->render('@NyroDevNyroCms/Composer/item/_imageAttrs.html.php', [
                'src' => $image['src'],
                'widthContainer' => $widthContainer,
            ]); ?> alt="<?php echo $image['alt']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" loading="lazy" />
        <?php endforeach; ?>
    <?php endif; ?>

    <?php echo $view->render('@NyroDevNyroCms/Composer/item/_slideshowNav.html.php', [
        'row' => $row,
        'images' => $images,
    ]); ?>
</nyro-swiper>