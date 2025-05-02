<template class="<?php echo $type; ?>" title="<?php echo $view->escape($view['translator']->trans('admin.composer.'.$type.'.'.$id.'.title')); ?>" data-id="<?php echo $id; ?>" data-cfg="<?php echo $view->escape(json_encode($cfg)); ?>">
<?php echo $html; ?>
</template>