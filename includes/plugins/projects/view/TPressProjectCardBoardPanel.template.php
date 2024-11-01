<?php 
// $panel_query is expected to contain the projects (post) to display
global $panel_query, $panel_title;
?>

<div class="tpress-panel">

<?php if ( isset( $panel_title ) ) : ?>
<h3 class="tpress-panel-title"><?php echo $panel_title; ?></h3>
<?php endif; ?>

<?php 
global $post, $user_ID;
$count = 0;
if ( $panel_query!=null ) :
	while ( $panel_query->have_posts() ) : 
		$panel_query->the_post(); 

		global $tpress_project;
		$tpress_project = TPressProject::readPostMeta( $post );

		// Only list projects that a user is allowed to see (either registered on that project or with the global 
		// capability to view all projects
		if ( !( current_user_can( 'tpress_view_all_projects' ) || TPressProject::isUserRegistered( $post, $user_ID ) ) ) {
			continue;
		}
		
		include 'TPressProjectCard.template.php';
		
		$tpress_project = null;
		
		$count++;
	endwhile;
endif;

if ( $count==0 ) :
	echo __( 'Aucun project', 'tpress' );
endif;

wp_reset_query(); // reset the query 

?>

</div>