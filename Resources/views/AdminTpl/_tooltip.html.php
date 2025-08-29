<nyro-tooltip halign-default="right">
    <?php echo $view['nyrocms_admin']->getIcon('infos', attrs: ' slot="trigger"'); ?>
    <template>
        <?php if ($editUrl) : ?>
            <a href="<?php echo $editUrl; ?>" class="edit-tooltip"><?php echo $view['translator']->trans('admin.misc.edit'); ?></a><br />
        <?php endif; ?>
        <?php echo $content; ?>
    </template>
</nyro-tooltip>