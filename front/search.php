<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }


$_q = '';
if(isset($_GET['search'])) $_q = $_GET['search'];

if(!$_q) {
	include_once 'search-form.php';
} else { ?>

<div class="landingmargins">
	<div class="search-result">
		<div class="search-result-container page-search-result">
			<h2>#Pages</h2>
			<div class="result-entries">
				<?php
					$pageTable = new TableHelper('Page');
					$pageTable->filters = array('search');
					$pageData = Page::find( $pageTable->find_params() );

					if($pageData->total == 0) { ?>
					<div class="not-found-notify">
						There are no pages that are matched.
					</div>
					<?php }
					foreach($pageData as $index => $model):
				?>

					<div><a href="<?='files'.'/'.$model['id']?>"><?= h($model->display('name')) ?></a></div>

				<?php endforeach ?>
			</div>
		</div>

		<div class="search-result-container file-search-result">
			<h2>#Files</h2>
			<div class="result-entries">
				<?php
					$fileTable = new TableHelper('File');
					$fileTable->filters = array('search');
					$fileData = File::find( $fileTable->find_params() );
					if($fileData->total == 0) { ?>
					<div class="not-found-notify">
						There are no files that are matched.
					</div>
					<?php }
					foreach($fileData as $index => $model):
				?>

					<div><a href="<?='files'.'/'.$model['id']?>"><?= h($model->display('name')) ?></a></div>

				<?php endforeach ?>
			</div>
		</div>
	</div>
</div>
<?php 
}
?>