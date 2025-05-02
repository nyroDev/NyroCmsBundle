<nyro-swiper>
    <?php if ($images && is_array($images)): ?>
        <?php foreach($images as $image): ?>
            <img src="<?php echo $image['src']; ?>" alt="<?php echo $image['alt']; ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" loading="lazy" />
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="#" class="btn navPrev" slot="nav" aria-label="prev">prev</a>
    <a href="#" class="btn navNext" slot="nav" aria-label="next">next</a>
</nyro-swiper>