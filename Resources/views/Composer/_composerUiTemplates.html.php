<template class="ui" data-id="confirm">
    <h1><?php echo $view['translator']->trans('confirmQuestion', [], 'nyroComposer'); ?></h1>
    <nav class="actions">
        <a href="#" part="nyroComposerBtn" class="cancel"><?php echo $view['translator']->trans('cancel', [], 'nyroComposer'); ?></a>
        <a href="#" part="nyroComposerBtn" class="confirm"><?php echo $view['translator']->trans('confirm', [], 'nyroComposer'); ?></a>
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