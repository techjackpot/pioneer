<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

?>
<div class="page-hero projects">
    <h1>Projects</h1>
	<div class="darkoverlay"></div>
</div>
<div class="page-content">
	<div class="padd">
		<div class="projgallery">
			<h2>Pioneer Products are used in some amazing places Around the world.</h2>
			<?php $projects = Project::find(); ?>
			<div class="projectshome">
				<?php foreach ($projects as $project) {
					echo $project->project_preview();
				} ?>
			</div>
		</div>
	
	</div>
	<div class="maderighthere">
		<div class="america">Made. Right. Here.</div>
	</div>
	
</div>
<?php ?>