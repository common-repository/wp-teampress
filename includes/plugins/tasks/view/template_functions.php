<?php

if ( !function_exists( 'the_project_tasks_block' ) ) :

/**
 * Outputs the task lists of a project. Ideally this should be the most active / urgent tasks.
*/
function the_project_tasks_block( $project, $limit = 10,
		$container_elt = 'ul', $container_class = "project-tasks",
		$item_elt = 'li', $item_class = 'project-task', $link_class = "tpress-task-badge" ) {
	global $user_ID, $post;
		
	$task_query = TPressTask::findByProject( $project->ID, $limit );
	
	if ( $task_query!=null ) {
		
		$out = "<$container_elt class='$container_class'>";
	
		while ( $task_query->have_posts() ) {
			$task_query->the_post();
		
			TPressTask::readPostMeta( $post );
				
			$a_title = esc_attr( get_the_excerpt() );			
			$a_content = get_the_title();
			$link = '';
			
			$out .= "<$item_elt class='$item_class'>";
			$out .= sprintf( "<a href='%s' title='%s' class='$link_class'>%s</a>", $link, $a_title, $a_content );
			
			if ( isset( $post->due_date ) ) {
				$out .= '<span class="due_date"> &rarr; ' . WordPressHelper::convertDateFromMysqlFormat( $post->due_date, get_option( 'date_format' ), true ) . '</span>';
			}
			
			$out .= "</$item_elt>";			
		}
		
		$out .= "</$container_elt>";	
		$out .= '<br class="clear">';

		wp_reset_postdata();
	}
	
	$actions = array();
	
	if ( TPressProject::userCan( $project, $user_ID, 'tpress_create_task' ) ) {
		$actions[] = array(
				'id' 	=> 'add',
				'link' 	=> '',
				'label' => __( 'Add', 'tpress' )
			);
	}

	tpress_block( 'project_tasks',
		__( 'Tasks', 'tpress' ),
		$out,
		TPressTasksPlugin::instance()->getProjectTasksLink( $project->ID ),
		$actions );

} // the_project_tasks_block

endif; // function_exist


if ( !function_exists( 'the_task_tags' ) ) :

/**
 * Outputs a span containing the list of tags corresponding to a task
*/
function the_task_tags( $task, $separator = ", ", $echo = true ) {
	$out = '<span class="tpress_task_tags">';

	$tags = get_the_terms( $task->ID, TPressTask::$TAG_TAXONOMY );
	
	if ( $tags ) {	
		$links = array();
		foreach ( $tags as $tag ) {
			$links[] = $tag->name;
		}
		$out .= join( $separator, $links );
	} else {
		$out .= __( 'No tag', 'tpress' );		
	}
	
	$out .= "</span>";
	
	if ($echo) echo $out;
	
	return $out;
} // the_task_tags

endif; // function_exist


if ( !function_exists( 'the_task_assignees' ) ) :

/**
 * Outputs a span containing the list of users who are assigned to a task
*/
function the_task_assignees( $task, $separator = ", ", $echo = true ) {

	$out = '<span class="tpress_task_assignees">';

	if ( isset( $task->assignees ) && !empty( $task->assignees ) ) {
		$links = array();
		foreach ( $task->assignees as $user_id ) {
			$user = new WP_User( $user_id );		
			$links[] = $user->display_name;
		}	
		$out .= join( $separator, $links );
	} else {
		$out .= __( 'Nobody', 'tpress' );		
	}
	
	$out .= "</span>";
	
	if ($echo) echo $out;
	
	return $out;
} // the_task_assignees

endif; // function_exist