<template class="ui" data-id="confirm">
    <p><?php echo $view['translator']->trans('confirmQuestion', [], 'nyroComposer'); ?></p>
    <nav class="actions">
        <a href="#" part="nyroComposerBtn nyroComposerBtnCancel" class="cancel"><?php echo $view['translator']->trans('cancel', [], 'nyroComposer'); ?></a>
        <a href="#" part="nyroComposerBtn nyroComposerBtnDelete" class="confirm"><?php echo $view['translator']->trans('delete', [], 'nyroComposer'); ?></a>
    </nav>
</template>

<template class="ui" data-id="btn_select">
    <a href="#" class="selectHandle nyroComposerBtn nyroComposerBtnUi nyroComposerBtnUiSelect" part="nyroComposerBtn nyroComposerBtnUi nyroComposerBtnUiSelect">sel</a>
</template>

<template class="ui" data-id="btn_drag">
    <a href="#" class="dragHandle nyroComposerBtn nyroComposerBtnUi nyroComposerBtnUiDrag" part="nyroComposerBtn nyroComposerBtnUi nyroComposerBtnUiDrag">drag</a>
</template>

<template class="ui" data-id="btn_delete">
    <a href="#" class="deleteHandle nyroComposerBtn nyroComposerBtnUi nyroComposerBtnUiDelete" part="nyroComposerBtn nyroComposerBtnUi nyroComposerBtnUiDelete">del</a>
</template>

<template class="ui" data-id="elementNav">
    <div class="sort">
        <a href="#" class="dragHandle"><?php echo $view['nyrodev_icon']->getIcon('drag'); ?></a>
        <a href="#" data-action="moveBottom"><?php echo $view['nyrodev_icon']->getIcon('moveBottom'); ?></a>
        <a href="#" data-action="moveTop"><?php echo $view['nyrodev_icon']->getIcon('moveTop'); ?></a>
        <a href="#" data-action="moveDown"><?php echo $view['nyrodev_icon']->getIcon('moveDown'); ?></a>
        <a href="#" data-action="moveUp"><?php echo $view['nyrodev_icon']->getIcon('moveUp'); ?></a>
    </div>
    <div class="actions">
        <a href="#" data-action="delete"><?php echo $view['nyrodev_icon']->getIcon('delete'); ?></a>
        <a href="#" data-action="duplicate"><?php echo $view['nyrodev_icon']->getIcon('duplicate'); ?></a>
        <a href="#" data-action="edit"><?php echo $view['nyrodev_icon']->getIcon('edit'); ?></a>
        <span class="title"></span>
    </div>
</template>

<template class="ui" data-id="multipleFilesNav">
    <a href="#" class="dragHandle"><?php echo $view['nyrodev_icon']->getIcon('drag'); ?></a>
    <a href="#" data-action="delete"><?php echo $view['nyrodev_icon']->getIcon('delete'); ?></a>
</template>

<template class="ui" data-id="icon">
    <?php echo $view['nyrodev_icon']->getIcon('IDENT'); ?>
</template>

<template class="ui" data-id="closeTpl">
    <a href="#" class="nyroCmsDialogClose" slot="close">
        <?php echo $view['nyrodev_icon']->getIcon('closeCircle'); ?>
    </a>
</template>