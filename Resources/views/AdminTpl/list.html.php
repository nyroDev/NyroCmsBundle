<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<?php $prefix = isset($prefix) && $prefix ? $prefix : 'admin'; ?>
	<article id="<?php echo $name; ?>" class="list">
		<h1><?php echo isset($title) ? $title : $view['translator']->trans($prefix.'.'.$name.'.viewTitle'); ?></h1>
		
		<?php
        $introKey = $prefix.'.'.$name.'.introList';
        $intro = $view['translator']->trans($introKey);
        if ($intro && $intro != $introKey) {
            echo '<p class="intro">'.$intro.'</p>';
        }
        ?>
		
		<?php if ($filter): ?>
			<a href="#filter" class="switcher filterSwitcher"><?php echo $view['translator']->trans('admin.misc.filter'); ?></a>
			<div id="filter">
				<?php echo $view['form']->form($filter); ?>
				<a href="<?php echo $pager->getUrl(1, false, array_merge($routePrm, ['clearFilter' => 1])); ?>" class="clearFilter"><?php echo $view['translator']->trans('admin.misc.clearFilter'); ?></a>
			</div>
		<?php endif; ?>
		
		<?php if (!isset($noAdd) || !$noAdd || (isset($moreGlobalActions) && is_array($moreGlobalActions) && count($moreGlobalActions))): ?>
			<div class="listButtons">
				<?php if (isset($moreGlobalActions) && is_array($moreGlobalActions) && count($moreGlobalActions)): ?>
					<?php foreach ($moreGlobalActions as $k => $action): ?>
						<a href="<?php echo $view['nyrodev']->generateUrl($action['route'], (isset($action['routePrm']) ? $action['routePrm'] : [])); ?>" class="button <?php echo $k; ?> <?php echo isset($action['class']) ? $action['class'] : null; ?>" <?php echo isset($action['attrs']) ? $action['attrs'] : null; ?>>
							<?php if (isset($action['icon']) && $action['icon']): ?>
								<?php echo $view['nyrocms_admin']->getIcon($action['icon']); ?>
							<?php endif; ?>
							<?php echo $action['name']; ?>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if (!isset($noAdd) || !$noAdd): ?>
					<a href="<?php echo $view['nyrodev']->generateUrl($route.'_add', isset($routePrmAdd) ? $routePrmAdd : []); ?>" class="button add">
						<?php echo $view['nyrocms_admin']->getIcon('add'); ?>
						<?php echo $view['translator']->trans('admin.misc.add'); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		
		<?php if (count($results)): ?>
			<table class="data">
				<thead>
					<tr>
					<?php foreach ($fields as $field): ?>
						<?php
                        $label = $view['translator']->trans($prefix.'.'.$name.'.'.$field);
                        $prm = $routePrm;
                        $prm['page'] = 1;
                        $prm['sort'] = $field;
                        $linkAsc = $view['nyrodev']->generateUrl($routeName, array_merge($prm, ['order' => 'asc']));
                        $linkDesc = $view['nyrodev']->generateUrl($routeName, array_merge($prm, ['order' => 'desc']));
                        $current = isset($routePrm['sort']) && $routePrm['sort'] == $field ? $routePrm['order'] : false;
                        ?>
						<th><?php
                        echo $label;
                        echo '<a href="'.$linkAsc.'" class="listSort listSortAsc'.('asc' === $current ? ' active' : '').'" title="'.$view['translator']->trans('admin.misc.sortAsc').'">↓</a>';
                        echo '<a href="'.$linkDesc.'" class="listSort listSortDesc'.('desc' === $current ? ' active' : '').'" title="'.$view['translator']->trans('admin.misc.sortDesc').'">↑</a>';
                        ?></th>
					<?php endforeach; ?>
						<?php if (!isset($noActions) || !$noActions): ?>
						<th class="actions"><?php echo $view['translator']->trans('admin.misc.actions'); ?></th>
						<?php endif; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($results as $r): ?>
					<tr>
						<?php foreach ($fields as $f): ?>
							<td><?php
                                $fct = 'get'.ucfirst($f);
                                $val = $r->{$fct}();
                                if (is_object($val)) {
                                    if ($val instanceof \DateTime) {
                                        $val = strftime($view['translator']->trans(isset($dateFormats) && isset($dateFormats[$f]) ? $dateFormats[$f] : 'date.short'), $val->getTimestamp());
                                    }
                                }
                                echo nl2br($val);
                                ?></td>
						<?php endforeach; ?>
						<?php if (!isset($noActions) || !$noActions): ?>
						<td class="actions">
							<?php if (isset($moreActions) && is_array($moreActions)): ?>
								<?php foreach ($moreActions as $k => $action): ?>
									<a href="<?php echo $view['nyrodev']->generateUrl($action['route'], array_merge((isset($action['routePrm']) ? $action['routePrm'] : []), ['id' => $r->getId()])); ?>" class="<?php echo $k; ?>"<?php echo isset($action['_blank']) && $action['_blank'] ? ' target="_blank"' : ''; ?>>
										<?php echo $action['name']; ?>
									</a>
								<?php endforeach; ?>
							<?php endif; ?>
							<?php if (!isset($noEdit) || !$noEdit): ?>
								<a href="<?php echo $view['nyrodev']->generateUrl($route.'_edit', array_merge(isset($routePrmEdit) ? $routePrmEdit : [], ['id' => $r->getId()])); ?>" class="edit" title="<?php echo $view['translator']->trans('admin.misc.edit'); ?>">
									<?php echo $view['nyrocms_admin']->getIcon('edit'); ?>
								</a>
							<?php endif; ?>
							<?php if (!isset($noDelete) || !$noDelete): ?>
								<a href="<?php echo $view['nyrodev']->generateUrl($route.'_delete', array_merge(isset($routePrmDelete) ? $routePrmDelete : [], ['id' => $r->getId()])); ?>" class="delete" title="<?php echo $view['translator']->trans('admin.misc.delete'); ?>">
									<?php echo $view['nyrocms_admin']->getIcon('delete'); ?>
								</a>
							<?php endif; ?>
						</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				<tbody>
			</table>
			<br />
			<?php if ($pager->hasToPaginate()): ?>
				<div class="pagination">
					<?php if ($pager->hasPrevious()): ?>
						<a href="<?php echo $pager->getPreviousUrl(); ?>" class="prev"><?php echo $view['translator']->trans('admin.pager.prev'); ?></a>
					<?php endif; ?>
					<?php foreach ($pager->getPagesIndex() as $i => $page): ?>
						<?php if ($page[1]): ?>
							<strong><?php echo $i; ?></strong>
						<?php else: ?>
							<a href="<?php echo $page[0]; ?>"><?php echo $i; ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ($pager->hasNext()): ?>
						<a href="<?php echo $pager->getNextUrl(); ?>" class="next"><?php echo $view['translator']->trans('admin.pager.next'); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		<?php else: ?>
			<p><?php echo $view['translator']->trans('admin.misc.noResults'); ?></p>
		<?php endif; ?>
	</article>
<?php $view['slots']->stop(); ?>