<?php 
global $tpress_project, $tpress_task, $user_ID;

if ( isset( $tpress_task ) && isset( $tpress_task->ID ) ) {
	$is_creation = false;
	$task_id = $tpress_task->ID; 
	$task_name = get_the_title(); 
	$task_due_date = WordPressHelper::convertDateFromMysqlFormat( $tpress_task->due_date, _x( 'd/m/Y', 'Date Picker format (PHP)', 'tpress' ), false ); 
	$task_description = get_the_content(); 
	$form_action = esc_attr( '' );
	$dialog_title = esc_attr( __( 'Edit task', 'tpress' ) );
} else {
	$is_creation = true;
	$form_action = esc_attr( '' );
	$dialog_title = esc_attr( __( 'Create a new task', 'tpress' ) );
}

if (   ( $is_creation && TPressProject::userCan( $tpress_project, $user_ID, 'tpress_create_task' ) ) 
	|| ( !$is_creation && TPressProject::userCan( $tpress_project, $user_ID, 'tpress_update_task' ) ) ) : ?>

<div id="tpress_task_details_dialog" title="<?php echo $dialog_title; ?>">
    <form id="tpress_task_details_form" 
    	action="<?php echo $form_action; ?>" 
    	method="post" 
    	class="tpress_form tpress_task_details_form">

        <?php wp_nonce_field( $is_creation ? 'tpress_task_create' : 'tpress_task_update' ); ?>

<?php 	if ( !$is_creation ) : ?>
		<input id="task-id" name="task-id" type="hidden" value="<?php echo $task_id; ?>" />
<?php 	endif; ?>
        
        <ul>
        
<?php 	if ( isset( $tpress_project ) ) : ?>
<?php 		// TODO Allow selection of the project if $tpress_project is not set ?>         		        
        	<li>
        		<h3><?php _e( 'Project', 'tpress' ); ?></h3>
        		<input id="project" name="project" type="hidden" value="<?php echo esc_attr( $tpress_project->ID ); ?>" />        		
				<p><?php echo get_the_title( $tpress_project ); ?></p>
        	</li>
<?php 	endif; ?>

        	<li>
        		<h3><?php _e( 'Details', 'tpress' ); ?></h3>
        		<input id="task_name" name="task_name" type="text" 
	        			placeholder="<?php echo esc_attr( __( 'The name of the task', 'tpress' ) ); ?>" 
	        			<?php if ( !$is_creation ) echo 'value="' . esc_attr( $task_name ) . '"'; ?> 
	        			size="45" class="required" />
        	</li>
        	<li>
        		<textarea id="task_description" name="task_description" 
        				placeholder="<?php echo esc_attr( __( 'Some details about the task (optional)', 'tpress' ) ); ?>"
        				cols="50" rows="7"><?php if ( !$is_creation ) echo $task_description; ?></textarea>
        	</li>      
        	<li>
        		<h3><?php _e( 'Due date', 'tpress' ); ?></h3>
        		<input id="due_date" name="due_date" type="text" autocomplete="off" class="datepicker" 
        				placeholder="<?php esc_attr_e( 'Latest date at which the task should be completed', 'tpress' ); ?>" 
        				value="<?php if ( !$is_creation ) echo esc_attr( $task_due_date ); ?>" />
        	</li>     
        	<li>
        		<h3><?php _e( 'Assignees', 'tpress' ); ?></h3>
        		<?php WordPressHelper::getSelectUserDropdown( 
							'assignees', 
        					__( 'The task will be assigned to these people', 'tpress' ),
        					TPressProject::getUsersFlat( $tpress_project ) ); ?>
        	</li>      
        </ul>

        <div class="submit">
            <input type="submit" id="<?php echo $is_creation ? 'create_task' : 'update_task'; ?>" 
            		name="<?php echo $is_creation ? 'create_task' : 'update_task'; ?>"  
            		value="<?php echo esc_attr( $is_creation ? __( 'Create', 'tpress' ) : __( 'Update', 'tpress' ) ); ?>" 
            		class="button-primary" />
        </div>
    </form>
</div>

<script type="text/javascript">
<!--
    jQuery(function($) {
   		$( "#tpress_task_details_dialog" ).dialog({
  			autoOpen: false,
            modal: true,
            dialogClass: 'tpress-dialog',
            width: 600,
            height: 540,
            position: ['middle', 100]
        });
        
   		$('.datepicker').datepicker( { dateFormat: "<?php echo _x( 'dd/mm/yy', 'Date Picker format (JS)', 'tpress' ) ?>" } );
   		
   		$("#tpress_task_details_form").validate();
   		
   		$("#tpress_block_project_tasks a.block-action-add").click(function() {
   			$( "#tpress_task_details_dialog" ).dialog( 'open' );
   			return false;
   		});

   		$("#tpress_task_details_form select#project").select2({ width: '100%' });
   		$("#tpress_task_details_form select#assignees").select2({ width: '100%', allowClear: true });
    });
//-->
</script>

<?php endif;