<?php
require_once("inc/include.php");
$title = $cfg['fp_title']; // Site title
include("inc/header.php"); // Html-header
?>
		<div id="padded">
			<h2><?php echo $cfg['site_name']; ?></h2>
			<h3><?php echo $cfg['fp_text']; ?></h3>
		</div>
				
		
		<div id="fp_content">
		<?php
		
		include("scripts/fp_content.php");
		
		?>
		</div>
	</div>
<?php
include("inc/footer.php"); // Html-footer
?>
