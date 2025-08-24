<input type="checkbox" id="switch_<?php echo $menu->getComputedId(); ?>" class="nyrodev-menu-before-childs" <?php echo $menu->isActiveOrChildActive() ? 'checked' : ''; ?>/>
<label for="switch_<?php echo $menu->getComputedId(); ?>" class="btn btnSmall">
    <?php echo $view['nyrocms_admin']->getIcon('dots'); ?>
</label>