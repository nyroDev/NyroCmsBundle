<template id="closeTpl">
    <a href="#" class="nyroCmsDialogClose" slot="close">
        <?php echo $view['nyrocms_admin']->getIcon('closeCircle'); ?>
    </a>
</template>
<template id="deleteConfirmTitleTpl">
    <p slot="title"><?php echo $view['translator']->trans('admin.misc.deleteConfirm'); ?></p>
</template>
<template id="deleteConfirmContentTpl">
    <nav class="actions" slot="content">
        <a href="#" class="btn cancel">
            <?php echo $view['nyrocms_admin']->getIcon('reset'); ?>
            <span><?php echo $view['translator']->trans('admin.misc.cancel'); ?></span>
        </a>
        <a href="#" class="btn btnDelete confirm">
            <?php echo $view['nyrocms_admin']->getIcon('delete'); ?>
            <span class="confirmTxt"><?php echo $view['translator']->trans('admin.misc.delete'); ?></span>
        </a>
    </nav>
</template>
<template id="iconTpl">
    <?php echo $view['nyrocms_admin']->getIcon('IDENT'); ?>
</template>