<template id="closeTpl">
    <a href="#" class="btn btnSmall nyroCmsDialogClose" slot="close">
        <?php echo $view['nyrocms_admin']->getIcon('close'); ?>
    </a>
</template>
<template id="deleteConfirmTpl">
    <div slot="content">
        <p><?php echo $view['translator']->trans('admin.misc.deleteConfirm'); ?></p>
        <nav class="actions">
            <a href="#" class="btn cancel"><?php echo $view['translator']->trans('admin.misc.cancel'); ?></a>
            <a href="#" class="btn btnDelete confirm"><?php echo $view['translator']->trans('admin.misc.delete'); ?></a>
        </nav>
    </div>
</template>