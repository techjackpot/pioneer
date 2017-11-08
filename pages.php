<?php if (!defined('INDEX')) { require dirname(__FILE__).'/../../index.php'; exit; }

for ($p=0; $p<=10; $p++)
{
	if (!empty($path[$p]))
	{
	$newpath = $path[$p];
	$parentpath = $path[$p-1];
	$fullpath .= "/".$path[$p];
	$pathid = $p;
	}
}

$parent = sqla("SELECT `id` FROM pages WHERE uri = ".sanitize($parentpath)."");
if ($parent)
$whereparent = " and `parent_id` = ".sanitize($parent['id'])."";

$page = sqla("SELECT * FROM pages WHERE uri = ".sanitize($newpath).$whereparent);

$pageTitle = $page['menu_title'];
?>

					<!-- programs left start -->
                    <div class="programs-left">
                    	<div class="programs-image">
                        	<img src="<?=($page['image'] ? h($page['image']) : '/var/uploads/BCIU_become_member.png')?>" width="429" height="328" alt="img">
                        </div>
                        <?php
						display_submenu($page['id'], $parent['id'], $pathid); ?>
                        <div class="clear"></div>
                        <div id="left-quotes">
                            <p class="quote"></p>
                            <p class="name"></p>
                            <p class="title"></p>
                        </div>
                    </div>
                    <!-- programs left end -->
                    
                    <!-- Main right start -->
                    <div class="programs-right">
                    	<h2><?=h($page['name'])?></h2>
                        <?=cms_content($page['id'])?>
						<p>&nbsp;</p>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <!-- Main right end -->

<?php ?>