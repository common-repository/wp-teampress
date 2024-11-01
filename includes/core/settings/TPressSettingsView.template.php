<?php
$all_tabs = $this->getRegisteredTabs();
?>

<div class="wrap">
	<div id="icon-tpress-dashboard" class="icon32"><br></div>
    <h2><?php _e('TeamPress &raquo; Settings', 'tpress'); ?></h2>
	<br/>
	
	<?php TeamPress::printMessages(); ?>
	
	<form action="" method="post">
	
		<div id="slider">    
			<ul id="tabs">
<?php 		foreach ( $all_tabs as $slug => $tab ) : ?>
				<li><a href="#<?php echo esc_attr( $tab->getSlug() ); ?>">
						<?php echo $tab->getTabTitle(); ?>
					</a></li>
<?php 		endforeach; ?>
			</ul>

		<?php wp_nonce_field('tpress_save_settings') ?> 

<?php 		foreach ( $all_tabs as $slug => $tab ) : ?>
				<div id="<?php echo esc_attr( $tab->getSlug() ); ?>">   
					<h2><?php echo $tab->getTitle(); ?></h2>
					<br>
					<?php $tab->renderTabContent(); ?>
				</div> 
<?php 		endforeach; ?>
	
		</div> <!-- #slider -->
		
		<table class="form-table">
			<tr>
				<td><input type="submit" name="tpress_submit_settings" value="<?php _e("Save Settings", "tpress"); ?>" class="button-primary" /></td>
			</tr>
		</table>
	</form>
</div> <!-- .wrap -->

<script type="text/javascript">
    jQuery(function() {
        jQuery('#slider').tabs( { 
				fxFade: true, 
				fxSpeed: 'fast' 
			} );  
	});
</script>