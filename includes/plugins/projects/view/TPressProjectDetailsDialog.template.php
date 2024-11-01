<?php 
global $tpress_project, $user_ID;

if ( isset( $tpress_project ) && isset( $tpress_project->ID ) ) {
	$is_creation = false;
	$tpress_project_id = $tpress_project->ID; 
	$tpress_project_name = get_the_title(); 
	$tpress_project_description = get_the_content(); 
	$form_action = esc_attr( TPressProjectsPlugin::instance()->getProjectHomeLink( $tpress_project->ID ) );
	$dialog_title = esc_attr( __( 'Edit project details', 'tpress' ) );
} else {
	$is_creation = true;
	$form_action = esc_attr( TPressProjectsPlugin::instance()->getCreateProjectLink() );
	$dialog_title = esc_attr( __( 'Create a new project', 'tpress' ) );
}

if (   ( $is_creation && current_user_can( 'tpress_manage_projects' ) ) 
	|| ( !$is_creation && TPressProject::userCan( $tpress_project, $user_ID, 'tpress_edit_project_details' ) ) ) : ?>

<div id="tpress_project_details_dialog" title="<?php echo $dialog_title; ?>">
    <form id="tpress_project_details_form" 
    		action="<?php echo $form_action; ?>" 
    		method="post" 
    		class="tpress_form tpress_project_details_form">

        <?php wp_nonce_field( $is_creation ? 'tpress_project_create' : 'tpress_project_update' ); ?>

<?php 	if ( !$is_creation ) : ?>
		<input id="project-id" name="project-id" type="hidden" value="<?php echo $tpress_project_id; ?>" />
<?php 	endif; ?>
        
        <ul>
        	<li>
        		<h3><?php _e( 'General information', 'tpress' ); ?></h3>
        		<input id="project_name" name="project_name" type="text" 
	        			placeholder="<?php echo esc_attr( __( 'The name of the project', 'tpress' ) ); ?>" 
	        			<?php if ( !$is_creation ) echo 'value="' . esc_attr( $tpress_project_name ) . '"'; ?> 
	        			size="45" class="required" />
        	</li>
        	<li>
        		<textarea id="project_description" name="project_description" 
        				placeholder="<?php echo esc_attr( __( 'Some details about the project (optional)', 'tpress' ) ); ?>"
        				cols="50" rows="7"><?php if ( !$is_creation ) echo $tpress_project_description; ?></textarea>
        	</li>    
        	<li>
        		<h3><?php _e( 'Leaders', 'tpress' ); ?></h3>
        		<?php WordPressHelper::getSelectUserDropdown( 
							'project-leaders', 
        					__( 'People managing the project', 'tpress' ),
        					get_users() ); ?>
        	</li>       
        </ul>

        <div class="submit">
            <input type="submit" id="<?php echo $is_creation ? 'create_project' : 'update_project'; ?>" 
            		name="<?php echo $is_creation ? 'create_project' : 'update_project'; ?>"  
            		value="<?php echo esc_attr( $is_creation ? __( 'Create', 'tpress' ) : __( 'Update', 'tpress' ) ); ?>" 
            		class="button-primary" />
        </div>
    </form>
</div>

<script type="text/javascript">
<!--
    jQuery(function($) {
   		$( "#tpress_project_details_dialog" ).dialog({
  			autoOpen: false,
            modal: true,
            dialogClass: 'tpress-dialog',
            width: 600,
            height: 540,
            position:['middle', 100]
        });

   		$("#tpress_project_details_form").validate();

   		$("li.new-project>a").click(function() {
   			$( "#tpress_project_details_dialog" ).dialog( 'open' );
   			return false;
   		});
   		
   		$("#tpress_block_project_content a.block-action-edit").click(function() {
   			$( "#tpress_project_details_dialog" ).dialog( 'open' );
   			return false;
   		});

   		$("#tpress_project_details_form select").select2({ width: '100%' });
    });
//-->
</script>

<?php endif;