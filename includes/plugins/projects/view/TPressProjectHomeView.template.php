<?php 
// $project_query is expected to contain the project (post) to display
global $project_query, $post;

if ( $project_query!=null ) :
while ( $project_query->have_posts() ) : $project_query->the_post();
	global $tpress_project;
	
	$tpress_project = TPressProject::readPostMeta( $post );
?>

<div class="wrap tpress-project tpress-project-single">
	<div id="icon-tpress-dashboard" class="icon32"><br></div>
    <h2><?php echo sprintf( __("TeamPress &raquo; %s", "tpress"), get_the_title() ); ?></h2>
	<br/>
	
	<?php TeamPress::printMessages(); ?>
	
	<div id="tpress-project-main">
		<?php do_action( 'tpress_begin_project_main', $post ); ?>
				
		<?php the_project_description(); ?>
		
		<?php the_project_statistics(); ?>
	
		<?php do_action( 'tpress_end_project_main', $post ); ?>
	</div>
	
	<br class="clear">
	
	<div id="tpress-project-blocks">
		<?php do_action( 'tpress_begin_project_blocks', $post ); ?>
		
		<?php the_project_users_block(); ?>
		
		<?php do_action( 'tpress_end_project_blocks', $post ); ?>
	</div>
	
</div> <!-- div.wrap -->


<?php

include( 'TPressProjectDetailsDialog.template.php' );
include( 'TPressProjectUsersDialog.template.php' );

	$tpress_project = null;

endwhile; // Loop
endif; // $project_query!=null