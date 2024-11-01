<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressTask' ) ) :

/**
 * This class is made to provide a set of functions to access the tasks: creation, deletion, update, ...
 */
class TPressTask {

	public static $POST_TYPE = 'tpress_task';
	public static $TAG_TAXONOMY = 'tpress_task_tags';
	
	public static $META_PROJECT_ID 		= 'tpress_project_id';
	public static $META_DUE_DATE 		= 'tpress_due_date';
	public static $META_IS_COMPLETED	= 'tpress_is_completed';
	public static $META_ASSIGNEES		= 'tpress_assignees';
	
	/** Queries ******************************************************************************************************/

	/**
	 * Count the tasks
	 *
	 * @param $status string The post status to count (publish, draft, trash, ...)
	 *
	 * @return int The number of posts corresponding to that status
	 */
    public static function countTasks( $status = 'publish' ) {
		return wp_count_posts( self::$POST_TYPE )->$status;
	}

	/**
	 * Count the tasks that are still opened
	 *
	 * @return int The number of posts corresponding to that status
	 */
    public static function countProjectTasks( $project_id ) {
    	$q = self::findByProject( $project_id );
    	return $q->found_posts;
	}

	/**
	 * Count the tasks that are still opened
	 *
	 * @return int The number of posts corresponding to that status
	 */
    public static function countProjectOpenedTasks( $project_id ) {
    	$q = self::findOpenedByProject( $project_id );
    	return $q->found_posts;
	}
	
	/**
	 * Find all the tasks 
	 *
	 * @param $limit int The maximum number of tasks to fetch (-1 for no limit)
	 *
	 * @return WP_Query the tasks we found
	 */
    public static function findAll( $limit = -1 ) {
		// Use WordPress to bring back the tasks
        $q = new WP_Query( array(
				'post_type' => self::$POST_TYPE,
				'numberposts' => $limit
			) );
			
        return $q;
    }
	
	/**
	 * Get the tasks belonging to a given project
	 *
	 * @param $project_id int The ID of the project
	 * @param $limit int The maximum number of tasks to get
	 *
	 * @return WP_Query The WordPress query object that will bring back the posts we want
	 */
	public static function findByProject( $project_id, $limit = -1 ) {
		$q = new WP_Query( array( 
				'post_type' 	=> self::$POST_TYPE,
				'numberposts' 	=> $limit,
				'meta_query' 	=> array( 
						array(
							'key' 		=> self::$META_PROJECT_ID,
							'value' 	=> $project_id,
							'compare' 	=> '=',
							'type' 		=> 'numeric',
						)
					)
			) );		
		return $q;
	}
	
	/**
	 * Get the tasks belonging to a given project and still opened
	 *
	 * @param $project_id int The ID of the project
	 * @param $limit int The maximum number of tasks to get
	 *
	 * @return WP_Query The WordPress query object that will bring back the posts we want
	 */
	public static function findOpenedByProject( $project_id, $limit = -1 ) {
		$q = new WP_Query( array( 
				'post_type' 	=> self::$POST_TYPE,
				'numberposts' 	=> $limit,
				'meta_query' 	=> array( 
						array(
							'key' 		=> self::$META_PROJECT_ID,
							'value' 	=> $project_id,
							'compare' 	=> '=',
							'type' 		=> 'numeric',
						),
						array(
							'key' 		=> self::$META_IS_COMPLETED,
							'value' 	=> 0,
							'compare' 	=> '=',
							'type' 		=> 'numeric',
						)
					)
			) );		
		return $q;
	}

	/**
	 * Get the task with the given ID 
	 *
	 * @param $task_id int The ID of the task to find
	 *
	 * @return WP_Query The WordPress query object that will bring back the posts we want
	 */
	public static function findById( $task_id ) {
		$q = new WP_Query( $task_id );
		return $q;
	}
	
	/** Properties ***************************************************************************************************/
	
	/**
	 * Read the raw meta array as returned by WordPress' get_post_meta and assign it to object variables
	 *
	 * @param $task The task we want to enrich
	 *
	 * @return WP_Post The task with the meta as object member variables
	 */
	public static function readPostMeta( $task ) {
		if ( null==$task ) return null;
		
		$task->project_id 		= self::getProjectId( $task->ID );
		$task->due_date 		= self::getDueDate( $task->ID );
		$task->is_complete 		= self::isCompleted( $task->ID );
		$task->assignees 		= self::getAssignees( $task->ID );
		
		return $task;
	}
	
	/**
	 * Set whether the task is marked as completed or not
	 *
	 * @param $task_id int The ID of the task we are interested about
	 * @param $assignee int The ID of the project 
	 */
	public static function setProjectId( $task_id, $project_id ) {
		return update_post_meta( $task_id, self::$META_PROJECT_ID, $project_id );
	}
	
	/**
	 * Get the project id to which this task belongs
	 *
	 * @param $task_id int The ID of the task we are interested about
	 *
	 * @return int the ID of the post related to the project
	 */
	public static function getProjectId( $task_id ) {
		return get_post_meta( $task_id, self::$META_PROJECT_ID, true );
	}
	
	/**
	 * Get the date at which this task is due
	 *
	 * @param $task_id int The ID of the task we are interested about
	 *
	 * @return string the due date in the 'yyyymmdd' format
	 */
	public static function getDueDate( $task_id ) {
		$d = get_post_meta( $task_id, self::$META_DUE_DATE, true );
		return ( empty( $d ) ) ? null : $d;
	}
	
	/**
	 * Set the date at which the task shall be completed
	 *
	 * @param $task_id int The ID of the task we are interested about
	 * @param $due_date string the due date in the 'yyyymmdd' format
	 */
	public static function setDueDate( $task_id, $due_date ) {		
		return update_post_meta( $task_id, self::$META_DUE_DATE, $due_date );
	}
	
	/**
	 * Get whether the task is marked as completed or not
	 *
	 * @param $task_id int The ID of the task we are interested about
	 *
	 * @return bool True if the task is marked as completed
	 */
	public static function isCompleted( $task_id ) {
		return 1==get_post_meta( $task_id, self::$META_IS_COMPLETED, true );
	}
	
	/**
	 * Set whether the task is marked as completed or not
	 *
	 * @param $task_id int The ID of the task we are interested about
	 * @param $is_completed bool true if the task is completed
	 */
	public static function setCompleted( $task_id, $is_completed ) {
		return update_post_meta( $task_id, self::$META_IS_COMPLETED, $is_completed ? 1 : 0 );
	}
	
	/**
	 * Get the user to whom the task is assigned
	 *
	 * @param $task_id int The ID of the task we are interested about
	 *
	 * @return int The ID of the user 
	 */
	public static function getAssignees( $task_id ) {
		return self::decodeUsers( get_post_meta( $task_id, self::$META_ASSIGNEES, true ) );
	}
	
	/**
	 * Set the user to whom the task is assigned
	 *
	 * @param $task_id int The ID of the task we are interested about
	 * @param $assignee int The ID of the user 
	 */
	public static function setAssignees( $task_id, $assignees ) {
		return update_post_meta( $task_id, self::$META_ASSIGNEES, self::encodeUsers( $assignees ) );
	}
	
	/**
	 * Decode an array of users/user groups as stored in the meta table. We store users in an array. The array items
	 * are formed by concatenation of the role and the id (separated by |). For instance: 
	 * [ 'tpress_group_project_coworker|12,14,34,54|', 'tpress_group_project_leader|22,13|' ]
	 */
	private static function decodeUsers( $raw ) {
		$users = array();
		
		if ( !isset( $raw ) || empty( $raw ) ) return $users;		
		
		if ( is_array($raw) ) {
			$users = array_filter( explode( '|', $raw[0] ) );
		} else {
			$users = array_filter( explode( '|', $raw ) );
		}
		
		return $users;
	}
	
	/**
	 * Encode an array of users/user groups for storage in the meta table. We expect a dictionnary where the keys are 
	 * user groups and values are arrays of user IDs.
	 */
	private static function encodeUsers( $users ) {				
		$raw = '|' . implode( '|', array_filter( $users ) ) . '|';		
		return $raw;
	}

	/** Creation/update/deletion *************************************************************************************/
	
	/**
	 * Create a task.
	 *
	 * @param $form_data array containing the task properties:
	 *   - title 		(string)
	 *   - content 		(string)
	 *   
	 * @return int The id of the task if successfull. 0 otherwise.
	 */
    public static function create( $form_data ) {
        return self::createOrUpdate( 0, $form_data );
    }

	/**
	 * Update a task.
	 *
	 * @param $existing_task_id int the id of the task to update
	 * @param $form_data array containing the task properties 
	 *
	 * @return int The id of the task if successfull. 0 otherwise.
	 *
	 * @see TPressProject::create
	 */
    public static function update( $existing_task_id, $form_data ) {
        return self::createOrUpdate( $existing_task_id, $form_data );
    }

	/**
	 * Delete a task.
	 *
	 * @param $task_id int the id of the task to delete
	 */
    public static function delete( $task_id, $force = false ) {
        if ( False!=wp_delete_post( $task_id, $force ) ) {
			do_action( 'tpress_task_delete', $task_id );
		}
    }

	/**
	 * Create or update a task.
	 *
	 * @param $task_id int the id of the task to update or 0 if we are creating one
	 * @param $form_data array containing the task properties 
	 *
	 * @return int The id of the task if successfull. 0 otherwise.
	 *
	 * @see TPressProject::create
	 */
    private static function createOrUpdate( $existing_task_id, $form_data ) {
		if ( empty( $form_data ) ) return 0;
		
        $is_update = ( $existing_task_id!=0 ) ? true : false;

		// Update or create the WordPress post object
        $data = array(
            'post_title' 	=> $form_data['task_name'],
            'post_content' 	=> $form_data['task_description'],
            'post_type' 	=> self::$POST_TYPE,
            'post_status' 	=> 'publish'
        );

        if ( $is_update ) {
            $data['ID'] = $existing_task_id;
            $task_id = wp_update_post( $data );
        } else {
            $task_id = wp_insert_post( $data );
        }

        if ( $task_id ) {
			// Update the post meta data
			if ( isset( $form_data[ 'project' ] ) ) self::setProjectId( $task_id, $form_data[ 'project' ] );
			
			if ( isset( $form_data[ 'assignees' ] ) ) self::setAssignees( $task_id, $form_data[ 'assignees' ] );
			
			if ( isset( $form_data[ 'due_date' ] ) ) self::setDueDate( $task_id, $form_data[ 'due_date' ] );
			
			if ( isset( $form_data[ 'completed' ] ) ) self::setCompleted( $task_id, $form_data[ 'completed' ]==1 );
			else self::setCompleted( $task_id, false );
			
			// Give a change to plugins to do something about our task
            if ( $is_update ) {
                do_action( 'tpress_task_update', $task_id, $form_data );
            } else {
                do_action( 'tpress_task_create', $task_id, $form_data );
            }
        }

        return $task_id;
    }
	
	/** Custom post type setup ***************************************************************************************/
	
	/**
	 * Setup the post type
	 */
	public static function registerPostType() {
		// Task tags taxonomy
		$tag_taxonomy = array();
		$tag_taxonomy['labels'] = array(
				'name'               => _x( 'Tags', 'TPress Task Tags', 'tpress' ),
				'singular_name'      => _x( 'Tag', 'TPress Task Tags', 'tpress' )
			); 
		
		register_taxonomy( self::$TAG_TAXONOMY, self::$POST_TYPE, $tag_taxonomy );
		

		// Register Task post type
		$post_type = array();

		// Task labels
		$post_type['labels'] = array(
			'name'               => __( 'TeamPress &raquo; Tasks', 	'tpress' ),
			'menu_name'          => __( 'Tasks',                   		'tpress' ),
			'singular_name'      => __( 'Task',                    		'tpress' ),
			'all_items'          => __( 'All Tasks',               		'tpress' ),
			'add_new'            => __( 'New Task',                		'tpress' ),
			'add_new_item'       => __( 'Create New Task',         		'tpress' ),
			'edit'               => __( 'Edit',                   		'tpress' ),
			'edit_item'          => __( 'Edit Task',               		'tpress' ),
			'new_item'           => __( 'New Task',                		'tpress' ),
			'view'               => __( 'View Task',               		'tpress' ),
			'view_item'          => __( 'View Task',               		'tpress' ),
			'search_items'       => __( 'Search Tasks',            		'tpress' ),
			'not_found'          => __( 'No tasks found',          		'tpress' ),
			'not_found_in_trash' => __( 'No tasks found in Trash', 		'tpress' ),
			'parent_item_colon'  => __( 'Parent Task:',            		'tpress' )
		);

		// Task supports
		$post_type['supports'] = array(
			'title', 'editor', 'comments'
		);

		// Task rewrite
		$post_type['rewrite'] = array(
			'slug'       => self::getTaskPostTypeSlug(),
			'with_front' => false
		);

		register_post_type(
				self::$POST_TYPE,
				apply_filters('tpress_task_register_post_type', array(
						'label' 				=> __( 'Tasks', 'tpress' ),
						'description'         	=> __( 'A Task in the TeamPress sense', 'tpress' ),
						'labels'              	=> $post_type['labels'],
						'public'              	=> true,
						'exclude_from_search' 	=> true,
						'publicly_queryable' 	=> true,
						'show_ui'             	=> current_user_can( 'tpress_manage_tasks' ),
						'show_in_menu'			=> 'tpress-home',
						'hierarchical'			=> false,
						'supports'            	=> $post_type['supports'],
						'has_archive'			=> false,
						'rewrite'             	=> $post_type['rewrite']
					) )
			);
	}
	
	/** Private Methods **********************************************************************************************/
	
	private static function getTaskPostTypeSlug() {
		return apply_filters( 'tpress_task_post_type_slug', 'task' );
	}
	
}

endif; // class_exists