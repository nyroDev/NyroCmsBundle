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
        <a href="#" class="dragHandle"><?php echo $view['nyrocms_admin']->getIcon('drag', 'composer.svg'); ?></a>
        <a href="#" data-action="moveBottom"><?php echo $view['nyrocms_admin']->getIcon('moveBottom', 'composer.svg'); ?></a>
        <a href="#" data-action="moveTop"><?php echo $view['nyrocms_admin']->getIcon('moveTop', 'composer.svg'); ?></a>
        <a href="#" data-action="moveDown"><?php echo $view['nyrocms_admin']->getIcon('moveDown', 'composer.svg'); ?></a>
        <a href="#" data-action="moveUp"><?php echo $view['nyrocms_admin']->getIcon('moveUp', 'composer.svg'); ?></a>
    </div>
    <div class="actions">
        <a href="#" data-action="delete"><?php echo $view['nyrocms_admin']->getIcon('delete', 'composer.svg'); ?></a>
        <a href="#" data-action="duplicate"><?php echo $view['nyrocms_admin']->getIcon('duplicate', 'composer.svg'); ?></a>
        <a href="#" data-action="edit"><?php echo $view['nyrocms_admin']->getIcon('edit', 'composer.svg'); ?></a>
        <span class="title"></span>
    </div>
</template>

<template class="ui" data-id="multipleFilesNav">
    <a href="#" class="dragHandle"><?php echo $view['nyrocms_admin']->getIcon('drag', 'composer.svg'); ?></a>
    <a href="#" data-action="delete"><?php echo $view['nyrocms_admin']->getIcon('delete', 'composer.svg'); ?></a>
</template>

<template class="ui" data-id="icon">
    <?php echo $view['nyrocms_admin']->getIcon('IDENT', 'composer.svg'); ?>
</template>