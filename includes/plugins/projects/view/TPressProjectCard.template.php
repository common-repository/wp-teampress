<?php 
// $tpress_project is expected to contain the project (post) to display
global $tpress_project;
?>

<div class="tpress-card tpress-project-card tpress-project-<?php the_ID(); ?>">
	<h3 class="card-title">
		<a href="<?php echo TPressProjectsPlugin::getProjectHomeLink( get_the_ID() ); ?>"><?php the_title(); ?></a>
	</h3>
	
	<div class="card-header">
<!--
		<span class="tag toggle-project-pin <?php echo $tpress_project->is_pinned ? 'tag-on' : 'tag-off'; ?>" 
				data-project-id="<?php the_ID(); ?>" 
				title="<?php echo esc_attr( __( 'Pinned projects appear on your dashboard.', 'tpress' ) ); ?>" >
			<?php _e( 'pinned', 'tpress' ); ?>
		</span>
-->		
		<?php do_action( 'tpress-project-card-header' ); ?>
	</div><!-- div.card-header -->
	
	<div class="card-content">
		<?php do_action( 'tpress-project-card-content' ); ?>
		<br/>
	</div><!-- div.card-content -->
	
	<div class="card-footer">
		<?php the_project_users_summary(); ?>

		<?php do_action( 'tpress-project-card-footer' ); ?>
	</div><!-- div.card-footer -->
</div><!-- div.tpress-card -->