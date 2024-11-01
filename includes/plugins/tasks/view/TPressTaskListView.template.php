<?php 
// $task_query is expected to contain the project (post) to display
global $task_query, $project_query, $post;

global $tpress_project;
if ( $project_query!=null ) :
while ( $project_query->have_posts() ) : $project_query->the_post();	
	$tpress_project = TPressProject::readPostMeta( $post );
endwhile; // Loop
else :
	$tpress_project = null;
endif; // $task_query!=null

wp_reset_postdata();
?>

<div class="wrap tpress-tasks">
	<div id="icon-tpress-dashboard" class="icon32"><br></div>

<?php if ( $tpress_project!=null ) : ?>	
	<h2><?php echo sprintf( __("TeamPress &raquo; %s &raquo; Tasks", "tpress"), get_the_title( $tpress_project ) ); ?></h2>
<?php else : ?>
	<h2><?php echo sprintf( __("TeamPress &raquo; Tasks", "tpress") ); ?></h2>
<?php endif; ?>
	<br/>
	
	<?php TeamPress::printMessages(); ?>

    <form id="movies-filter" method="get">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
        
        	<div class="columns-2">        	
<?php 
	if ( $task_query!=null ) {
		$wp_list_table = new TPressTaskListTable( $task_query );
		$wp_list_table->prepare_items();
		$wp_list_table->display();
	}
?>
				<?php do_meta_boxes('tpress-task-list', 'below', null); ?>
			</div>
			<div class="metabox-container-side">
				<?php do_meta_boxes('tpress-task-list', 'side', null); ?>
			</div>
		
	</form>
</ul>
	
</div> <!-- div.wrap -->