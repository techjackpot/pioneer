<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_permission('pages_view');

$table = new TableHelper('Page');
$table->filters = array('search');

$data = Page::find( $table->find_params() );

$pageTitle = 'CMS Pages';
?>
<?php if(!is_paginator()): ?>
<div class="table-header">
	<h2 class="page-title"><?= $pageTitle ?></h2>
	<p>
		<a class="btn" href="<?= admin_url('pages', true) ?>"><?=icon('plus')?> Add Page</a>
	</p>
	<?= $table->filters() ?>
</div>
<div class="activity-wall-wrap">
	<div class="activity-wall-title"><?= $table->activity_wall_title() ?></div>
	<div class="activity-wall"><?php ChangeLog::display_model_all($table->model) ?></div>
</div>
<div class="data-table">
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?= $table->header('name') ?></th>
				<th><?= $table->header('parent_id') ?></th>
				<th><?= $table->header('sort_id') ?></th>
				<th class="span3"><?= $table->header('updated_at') ?></th>
				<th class="actions"></th>
			</tr>
		</thead>
		<tbody>
<?php endif; ?>
<?php foreach($data as $index => $model): ?>
			<tr>
				<td><a href="<?=admin_url('pages')?>/edit/<?=$model['id']?>"><?= h($model->display('name')) ?></a></td>
				<td><?= h($model->display('parent_id')) ?></td>
				<td><?= h($model->display('sort_id')) ?></td>
				<td class="span3"><?= h($model->display('updated_at')) ?></td>
				<td class="actions"><?= $table->actions($model) ?></td>
			</tr>
<?php endforeach ?>
<?php if(!is_paginator()): ?>
<?php $table->no_records_message($data->total(), 6) ?>
			<tr class="spacer"><td colspan="6"></td></tr>
		</tbody>
	</table>
</div>
<?= $table->ajax_pagination( $data->total() ) ?>
<?php endif; ?>