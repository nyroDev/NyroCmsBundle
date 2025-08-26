<?php $prefix = isset($prefix) && $prefix ? $prefix : 'admin'; ?>
<?php if (!$view['nyrodev']->isAjax()): ?>
<?php $view->extend('@NyroDevNyroCms/Admin/_layout.html.php'); ?>

<?php $view['slots']->start('content'); ?>
	<?php echo $view->render('@NyroDevNyroCms/AdminTpl/breadcrumbs.html.php', [
	    'title' => isset($title) ? $title : $view['translator']->trans($prefix.'.'.$name.'.viewTitle'),
	]); ?>
<?php endif; ?>
	<article id="<?php echo $name; ?>" class="list">
		<h1>
			<?php echo isset($title) ? $title : $view['translator']->trans($prefix.'.'.$name.'.viewTitle'); ?>
			<?php echo $view['nyrocms_tooltip']->renderIdent(isset($tooltipIdent) ? $tooltipIdent : $name.'/list'); ?>
		</h1>

		<?php
        $introKey = $prefix.'.'.$name.'.introList';
$intro = $view['translator']->trans($introKey);
if ($intro && $intro != $introKey) {
    echo '<p class="intro">'.$intro.'</p>';
}
?>
		
		<?php if (!isset($noAdd) || !$noAdd || (isset($moreGlobalActions) && is_array($moreGlobalActions) && count($moreGlobalActions))): ?>
			<nav class="listButtons">
				<?php if (isset($moreGlobalActions) && is_array($moreGlobalActions) && count($moreGlobalActions)): ?>
					<?php foreach ($moreGlobalActions as $k => $action): ?>
						<a href="<?php echo $view['nyrodev']->generateUrl($action['route'], isset($action['routePrm']) ? $action['routePrm'] : []); ?>" class="btn <?php echo $k; ?> <?php echo isset($action['class']) ? $action['class'] : null; ?>" <?php echo isset($action['attrs']) ? $action['attrs'] : null; ?>>
							<?php if (isset($action['icon']) && $action['icon']): ?>
								<?php echo $view['nyrocms_admin']->getIcon($action['icon']); ?>
							<?php endif; ?>
							<span><?php echo $action['name']; ?></span>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php if (!isset($noAdd) || !$noAdd): ?>
					<a href="<?php echo $view['nyrodev']->generateUrl($route.'_add', isset($routePrmAdd) ? $routePrmAdd : []); ?>" class="btn add">
						<?php echo $view['nyrocms_admin']->getIcon('addCircle'); ?>
						<span><?php echo $view['translator']->trans('admin.misc.add'); ?></span>
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<?php if ($filter): ?>
			<div class="filter<?php echo $filterFilled ? ' filterFilled' : ''; ?>">
				<input type="checkbox" id="filterSwitch_<?php echo $name; ?>" value="1" <?php echo $filterFilled ? 'checked' : ''; ?> />
				<label for="filterSwitch_<?php echo $name; ?>">
					<?php echo $view['nyrocms_admin']->getIcon('filter'); ?>
					<?php echo $view['translator']->trans('admin.misc.filters'); ?>
					<span class="flexSpacer"></span>
					<?php echo $view['nyrocms_admin']->getIcon('chevron'); ?>
				</label>
				<?php echo $view['form']->form($filter); ?>
			</div>
		<?php endif; ?>
		
		<?php if (count($results)): ?>
			<table class="data">
				<thead>
					<tr>
					<?php foreach ($fields as $field): ?>
						<?php
                        $label = $view['translator']->trans($prefix.'.'.$name.'.'.$field);
					    if ($label === $prefix.'.'.$name.'.'.$field) {
					        $label = $view['translator']->trans('admin._global.'.$field);
					    }
					    $prm = $routePrm;
					    $prm['page'] = 1;
					    $prm['sort'] = $field;
					    $linkAsc = $view['nyrodev']->generateUrl($routeName, array_merge($prm, ['order' => 'asc']));
					    $linkDesc = $view['nyrodev']->generateUrl($routeName, array_merge($prm, ['order' => 'desc']));
					    $current = isset($routePrm['sort']) && $routePrm['sort'] == $field ? $routePrm['order'] : false;
					    ?>
						<th><?php
					    echo $label;
					    if (!isset($unsortableFields) || !is_array($unsortableFields) || !in_array($field, $unsortableFields)) {
					        echo '<a href="'.$linkAsc.'" class="listSort listSortAsc'.('asc' === $current ? ' active' : '').'" title="'.$view['translator']->trans('admin.misc.sortAsc').'">↓</a>';
					        echo '<a href="'.$linkDesc.'" class="listSort listSortDesc'.('desc' === $current ? ' active' : '').'" title="'.$view['translator']->trans('admin.misc.sortDesc').'">↑</a>';
					    }
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
						<?php
                    $curResultMenu = $resultMenuApply($resultMenu, $r);
				    $first = true;
				    $canEdit = $curResultMenu->hasChild('edit');

				    foreach ($fields as $f): ?>
							<td><?php
				            $fct = 'get'.ucfirst($f);
				        $val = $r->{$fct}();
				        if (is_object($val)) {
				            if ($val instanceof DateTimeInterface) {
				                $val = strftime($view['translator']->trans(isset($dateFormats) && isset($dateFormats[$f]) ? $dateFormats[$f] : 'date.short'), $val->getTimestamp());
				            } elseif ($val instanceof Doctrine\Common\Collections\Collection) {
				                $tmpVal = [];
				                foreach ($val as $v) {
				                    $tmpVal[] = $v.'';
				                }
				                $val = implode(', ', $tmpVal);
				            }
				        } elseif (isset($choices) && is_array($choices) && isset($choices[$f]) && isset($choices[$f][$val])) {
				            $val = $choices[$f][$val];
				        } elseif (isset($formatter) && is_array($formatter) && isset($formatter[$f])) {
				            $val = $formatter[$f]($r);
				        }
				        if ($first) {
				            if ($curResultMenu->hasChild('edit')) {
				                $urlEdit = $view['router']->path($curResultMenu->getChild('edit')->route, $curResultMenu->getChild('edit')->getRoutePrm());
				                echo '<a href="'.$urlEdit.'" class="editLink">'.nl2br($val).'</a>';
				            } else {
				                echo nl2br($val);
				            }
				            $first = false;
				        } else {
				            echo nl2br($val);
				        }
				        if (isset($moreVal) && is_array($moreVal) && isset($moreVal[$f])) {
				            echo $moreVal[$f]($r);
				        }
				        ?></td>
						<?php endforeach; ?>
						<?php if (!isset($noActions) || !$noActions): ?>
						<td class="actions">
							<?php
				            echo $view->render($curResultMenu->getTemplate(), ['menu' => $curResultMenu]);
						    ?>
						</td>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				<tbody>
			</table>
			<?php if ($pager->hasToPaginate()): ?>
				<br />
				<nav class="pagination">
					<?php /* <a href="<?php echo $pager->hasPrevious() ? $pager->getFirstUrl() : '#'; ?>" class="prev first" title="<?php echo $view['translator']->trans('admin.pager.first'); ?>"><?php echo $view['nyrocms_admin']->getIcon('doubleChevron'); ?></a> */ ?>
					<a href="<?php echo $pager->hasPrevious() ? $pager->getPreviousUrl() : '#'; ?>" class="btn btnLightWhite prev" title="<?php echo $view['translator']->trans('admin.pager.prev'); ?>">
						<?php echo $view['nyrocms_admin']->getIcon('chevron'); ?>
					</a>
					<span>
						<?php foreach ($pager->getPagesIndex() as $i => $page): ?>
							<?php if ($page[1]): ?>
								<strong><?php echo $i; ?></strong>
							<?php else: ?>
								<a href="<?php echo $page[0]; ?>" class="btn btnLightWhite"><?php echo $i; ?></a>
							<?php endif; ?>
						<?php endforeach; ?>
					</span>
					<a href="<?php echo $pager->hasNext() ? $pager->getNextUrl() : '#'; ?>" class="btn btnLightWhite next" title="<?php echo $view['translator']->trans('admin.pager.next'); ?>">
						<?php echo $view['nyrocms_admin']->getIcon('chevron'); ?>
					</a>
					<?php /* <a href="<?php echo $pager->hasNext() ? $pager->getLastUrl() : '#'; ?>" class="next last" title="<?php echo $view['translator']->trans('admin.pager.last'); ?>"><?php echo $view['nyrocms_admin']->getIcon('doubleChevron'); ?></a> */ ?>
				</nav>
			<?php endif; ?>

		<?php else: ?>
			<p><?php echo $view['translator']->trans('admin.misc.noResults'); ?></p>
		<?php endif; ?>
	</article>
<?php if (!$view['nyrodev']->isAjax()): ?>
<?php $view['slots']->stop(); ?>
<?php endif; ?>