<p><?php echo nl2br($view['translator']->trans('admin.composer.convertToTemplate.done')); ?></p>
<br />
<nav class="actions">
    <a href="#" class="btn btnClose closeDialog">
        <?php echo $view['nyrocms_admin']->getIcon('close'); ?>
        <span class="confirmTxt"><?php echo $view['translator']->trans('admin.composer.convertToTemplate.close'); ?></span>
    </a>
</nav>