<nav id="breadcrumbs">
    <a href="<?php echo $view['router']->path('nyrocms_admin_homepage'); ?>" rel="home">
        <?php echo $view['nyrocms_admin']->getIcon('home'); ?>
        <span><?php echo $view['translator']->trans('admin.menu.home'); ?></span>
    </a>
    /
    <?php if (isset($links) && is_array($links)) : ?>
        <?php foreach ($links as $link): ?>
            <a href="<?php echo $link['url']; ?>"><?php echo $link['label']; ?></a> /
        <?php endforeach; ?>
    <?php endif; ?>
    <strong><?php echo $title; ?></strong>
</nav>