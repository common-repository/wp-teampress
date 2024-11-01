<?php 
global $tpress_project, $user_ID;

$tpress_project_id 	= $tpress_project->ID;
$form_action 	= esc_attr( TPressProjectsPlugin::instance()->getProjectHomeLink( $tpress_project_id ) );
$dialog_title 	= __( 'Choose people involved in the project', 'tpress' );
$tpress_project_users 	= TPressProject::getUsers( $tpress_project_id );
$all_users 		= get_users();

if ( TPressProject::userCan( $tpress_project, $user_ID, 'tpress_manage_project_users' ) ) : ?>

<div id="tpress_project_users_dialog" title="<?php echo $dialog_title; ?>">
    <form id="tpress_project_users_form" 
    		action="<?php echo $form_action; ?>" 
    		method="post" 
    		class="tpress_form tpress_project_users_form">

        <?php wp_nonce_field( 'tpress_project_manage_users' ); ?>
		<input id="project-id" name="project-id" type="hidden" value="<?php echo $tpress_project_id; ?>" />
        
        <ul>
        	<li>
        		<h3><?php _e( 'Leaders', 'tpress' ); ?></h3>
        		<?php WordPressHelper::getSelectUserDropdown( 
							'project-leaders', 
        					__( 'People managing the project', 'tpress' ),  
        					$all_users,
        					$tpress_project_users[ TPressProjectsPlugin::$ROLE_PROJECT_LEADER ] ); ?>
        	</li>   
        	<li>
        		<h3><?php _e( 'Co-workers', 'tpress' ); ?></h3>
        		<?php WordPressHelper::getSelectUserDropdown( 
							'project-coworkers', 
        					__( 'People working on the project', 'tpress' ), 
        					$all_users,
        					$tpress_project_users[ TPressProjectsPlugin::$ROLE_PROJECT_COWORKER ] ); ?>
        	</li>  
        	<li>
        		<h3><?php _e( 'Clients', 'tpress' ); ?></h3>
        		<?php WordPressHelper::getSelectUserDropdown( 
							'project-clients', 
        					__( 'People who are expecting the project', 'tpress' ),  
        					$all_users,
        					$tpress_project_users[ TPressProjectsPlugin::$ROLE_PROJECT_CLIENT ] ); ?>
        	</li>       
        </ul>

        <div class="submit">
            <input type="submit" id="update_users" 
            		name="update_users"  
            		value="<?php echo esc_attr( __( 'Update', 'tpress' ) ); ?>" 
            		class="button-primary" />
        </div>
    </form>
</div>

<script type="text/javascript">
<!--
    jQuery(function($) {
   		$( "#tpress_project_users_dialog" ).dialog({
  			autoOpen: false,
            modal: true,
            dialogClass: 'tpress-dialog',
            width: 600,
            height: 530,
            position:['middle', 100]
        });

   		$("#tpress_project_users_form").validate();

   		$("#tpress_block_project_users a.block-action-manage").click(function() {
   			$( "#tpress_project_users_dialog" ).dialog( 'open' );
   			return false;
   		});

   		$("#tpress_project_users_form select").select2({ width: '100%' });
    });
//-->
</script>

<?php endif;