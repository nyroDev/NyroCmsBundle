<p><?php echo nl2br($view['translator']->trans('admin.composer.convertToTemplate.done')); ?></p>
<br />
<nav class="actions">
    <a href="#" class="btn btnClose closeDialog">
        <?php echo $view['nyrodev_icon']->getIcon('close'); ?>
        <span class="confirmTxt"><?php echo $view['translator']->trans('admin.composer.convertToTemplate.close'); ?></span>
    </a>
</nav>