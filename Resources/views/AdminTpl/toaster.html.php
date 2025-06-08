<?php if ($view['session']->hasFlash('toaster')): ?>
    <nyro-toaster-stack>
        <?php foreach ($view['session']->getFlash('toaster') as $toaster): ?>
            <nyro-toaster <?php echo is_array($toaster) && isset($toaster['attrs']) ? $toaster['attrs'] : ''; ?>>
                <a href="#" class="close"><?php echo $view['nyrocms_composer']->getIcon('close'); ?></a>
                <?php echo is_array($toaster) ? $toaster['content'] : $toaster; ?>
            </nyro-toaster>
        <?php endforeach; ?>
    </nyro-toaster-stack>
<?php endif; ?>