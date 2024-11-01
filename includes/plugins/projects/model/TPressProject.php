<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressProject' ) ) :

/**
 * This class is made to provide a set of functions to access the projects: creation, deletion, update, ...
 */
class TPressProject {

	public static $POST_TYPE 		= 'tpress_project';
	
	public static $META_USERS 				= 'tpress_users';
	public static $UMETA_PINNED_PROJECTS 	= 'tpress_pinned_projects';

	/** Queries ******************************************************************************************************/
	
	/**
	 * Count the projects
	 *
	 * @param $status string The post status to count (publish, draft, trash, ...)
	 *
	 * @return int The number of posts corresponding to that status
	 */
    public static function countProjects( $status = 'publish' ) {
		return wp_count_posts( self::$POST_TYPE )->$status;
	}
	
	/**
	 * Find all the projects 
	 *
	 * @param $limit int The maximum number of projects to fetch (-1 for no limit)
	 *
	 * @return WP_Query the projects we found
	 */
    public static function findAll( $limit = -1 ) {
		// Use WordPress to bring back the projects
        $q = new WP_Query( array(
				'post_type' => self::$POST_TYPE,
				'numberposts' => $limit
			) );
			
        return $q;
    }
	
	/**
	 * Find a project given its ID 
	 *
	 * @param $project_id int The ID of the project to fetch 
	 *
	 * @return WP_Query the project we found (or not)
	 */
    public static function findById( $project_id ) {
		// Use WordPress to bring back the projects
        $q = new WP_Query( array(
				'post_type' => self::$POST_TYPE,
				'p' => $project_id
			) );
			
        return $q;
    }
	
	/**
	 * Find the projects pinned by the current user 
	 *
	 * @param $limit int The maximum number of projects to fetch (-1 for no limit)
	 *
	 * @return WP_Query the projects we found
	 */
    public static function findPinnedProjects( $limit = -1 ) {
		$ids = self::getPinnedProjectIds();
		
		if ( $limit > 0 ) $ids = array_slice( $ids, 0, $limit );
		
		if ( empty( $ids ) ) return null;
		
		// Use WordPress to bring back the projects
        $q = new WP_Query( array(
				'post_type' => self::$POST_TYPE,	
				'numberposts' => $limit,			
				'post__in' 	=> $ids
			) );

        return $q;
    }
	
	/**
	 * Find the projects where a user is registered 
	 *
	 * @param $user_id int The id of the user (if not set, will default to the current user)
	 *
	 * @return WP_Query the projects we found
	 */
    public static function findUserProjects( $user_id = null, $limit = -1 ) {
		if ( !isset( $user_id ) ) {
			global $user_ID;
			$user_id = $user_ID;
		}
		
		// Use WordPress to bring back the projects
        $q = new WP_Query( array(
				'post_type' => self::$POST_TYPE,	
				'numberposts' => $limit,	
				'meta_query' 	=> array( 
						array(
							'key' 		=> self::$META_USERS,
							'value' 	=> '|' . $user_id . '|',
							'compare' 	=> 'LIKE'
						)
					)
			) );

        return $q;
    }

	/** Project users ************************************************************************************************/

	/**
	 * See if the user has the given capability in this project. 
	 *
	 * @param $project The project we are talking about (must be a loaded post)
	 * @param $user_id The user ID we are interested about
	 * @param $capability The capability we need to test
	 * @param $always_allow_admin Do we consider that administrators can do this no matter what? (default: true)
	 *
	 * @return bool True if the user can do that
	 *
	 * We assume that a WordPress administrator is someone with the capability 'edit_users'.
	 */
	public static function userCan( $project, $user_id, $capability, $always_allow_admin = true ) {
		if ( $always_allow_admin && user_can( $user_id, 'edit_users' ) ) return true;
		
		$group = self::getUserGroup( $project, $user_id );
		
		return $group!=null && $group->has_cap( $capability );
	}
	
	/**
	 * Get the user group the given user belongs to
	 *
	 * @param $project mixed The project we are talking about or the corresponding ID
	 * @param $user_id int The user ID we are interested about
	 *
	 * @return WP_Role The group the user belongs to, or null if not found
	 */
	public static function getUserGroup( $project, $user_id ) {
		if ( is_a( $project, 'WP_Post' ) ) {
			if ( is_array( $project->users ) ) {
				$users = $project->users;
			} else {
				$users = self::getUsers( $project->ID );
			}
		} else {
			$users = self::getUsers( (int) $project );		
		}
			
		$groups = TPressProjectsPlugin::instance()->getUserGroups();
		
		foreach ( $groups as $g ) {
			// Group unknown?
			if ( !isset( $users[ $g ] ) ) continue;
			
			// user in group?
			if ( in_array( $user_id, $users[ $g ] ) ) {
				return TPressProjectsPlugin::instance()->getUserGroup( $g );
			}
		}
		
		return null;
	}
	
	/**
	 * Is the user part of the project?
	 *
	 * @param $project The project we are talking about (must be a loaded post)
	 * @param $user_id The user ID we are interested about
	 *
	 * @return bool True if the user has a role in the project
	 */
	public static function isUserRegistered( $project, $user_id ) {
		return null != self::getUserGroup( $project, $user_id );
	}
	
	public static function getUsers( $project_id ) {
		return self::decodeProjectUsers( get_post_meta( $project_id, self::$META_USERS, true ) );
	}
	
	public static function getUsersFlat( $project ) {
		$out = array();
		$groups = TPressProjectsPlugin::instance()->getUserGroups();
		foreach ( $groups as $group ) {
			$out = array_merge( $out, $project->users[ $group->name ] );
		}
		return array_unique( $out );
	}
	
	public static function setUsers( $project_id, $users ) {
		update_post_meta( $project_id, self::$META_USERS, self::encodeProjectUsers( $users ) );
	}
	
	/**
	 * Decode an array of users/user groups as stored in the meta table. We store users in an array. The array items
	 * are formed by concatenation of the role and the id (separated by |). For instance: 
	 * [ 'tpress_group_project_coworker|12,14,34,54|', 'tpress_group_project_leader|22,13|' ]
	 */
	private static function decodeProjectUsers( $raw ) {
		$groups = TPressProjectsPlugin::instance()->getUserGroups();
		$users = array();
		
		foreach ( $groups as $group ) {
			$users[ $group->name ] = array();
		}
		
		if ( !isset( $raw ) || empty( $raw ) ) return $users;
		
		foreach ( $raw as $g ) {
			$tokens = explode( '|', $g );		
			$group = $tokens[0];
			$group_users = array_slice( $tokens, 1 );
			
			$users[ (string) $group ] = array_filter( $group_users );
		}
		
		return $users;
	}
	
	/**
	 * Encode an array of users/user groups for storage in the meta table. We expect a dictionnary where the keys are 
	 * user groups and values are arrays of user IDs.
	 */
	private static function encodeProjectUsers( $users ) {		
		$groups = TPressProjectsPlugin::instance()->getUserGroups();
		
		foreach ( $groups as $group ) {
			$g = $group->name;
			$group_users = isset( $users ) && isset( $users[ $g ] ) ? array_filter( $users[ $g ] ) : array();			
			$token = $g . '|' . implode( '|', $group_users ) . '|';
			$raw[] = $token;			
		}
		
		return $raw;
	}
	
	
	/** Project properties *******************************************************************************************/
	
	/**
	 * Read the raw meta array as returned by WordPress' get_post_meta and assign it to object variables
	 *
	 * @param $project The project we want to enrich
	 *
	 * @return WP_Post The project with the meta as object member variables
	 */
	public static function readPostMeta( $project ) {
		if ( null==$project ) return null;
		
		$meta = get_post_meta( $project->ID );
		
		$project->users = apply_filters( 'tpress_project_load_users', self::getUsers( $project->ID ) );
		$project->is_pinned = self::isPinned( $project->ID );
		
		return $project;
	}
	
	public static function isPinned( $project_id ) {
		global $user_ID;
		
		$pinned_projects = get_user_meta( $user_ID, self::$UMETA_PINNED_PROJECTS, true );
		
		return is_array( $pinned_projects ) && isset( $pinned_projects[ $project_id ] );
	}
	
	public static function setPinned( $project_id, $is_pinned = true ) {
		global $user_ID;
		
		$pinned_projects = get_user_meta( $user_ID, self::$UMETA_PINNED_PROJECTS, true );
		
		if ( $is_pinned ) $pinned_projects[ $project_id ] = 1;
		else unset( $pinned_projects[ $project_id ] );
		
		return update_user_meta( $user_ID, self::$UMETA_PINNED_PROJECTS, $pinned_projects );
	}
	
	/**
	 *	Get the IDs of the projects pinned by the current user
	 */
	public static function getPinnedProjectIds() {
		global $user_ID;
		
		$pinned_projects = get_user_meta( $user_ID, self::$UMETA_PINNED_PROJECTS, true );
		
		return is_array( $pinned_projects ) ? array_keys( $pinned_projects ) : array();
	}

	/** Creation/update/deletion *************************************************************************************/
	
	/**
	 * Create a project.
	 *
	 * @param $form_data array containing the project properties:
	 *   - title 		(string)
	 *   - content 		(string)
	 *
	 * @return int The id of the project if successfull. 0 otherwise.
	 */
    public static function create( $form_data ) {
        return self::createOrUpdate( 0, $form_data );
    }

	/**
	 * Update a project.
	 *
	 * @param $existing_project_id int the id of the project to update
	 * @param $form_data array containing the project properties 
	 *
	 * @return int The id of the project if successfull. 0 otherwise.
	 *
	 * @see TPressProject::create
	 */
    public static function update( $existing_project_id, $form_data ) {
        return self::createOrUpdate( $existing_project_id, $form_data );
    }

	/**
	 * Delete a project.
	 *
	 * @param $project_id int the id of the project to delete
	 */
    public static function delete( $project_id, $force = true ) {
        if ( FALSE!=wp_delete_post( $project_id, $force ) ) {
			do_action( 'tpress_project_delete', $project_id );
		}
    }

	/**
	 * Create or update a project.
	 *
	 * @param $project_id int the id of the project to update or 0 if we are creating one
	 * @param $form_data array containing the project properties 
	 *
	 * @return int The id of the project if successfull. 0 otherwise.
	 *
	 * @see TPressProject::create
	 */
    private static function createOrUpdate( $existing_project_id, $form_data ) {
		if ( empty( $form_data ) ) return 0;
		
        $is_update = ( $existing_project_id!=0 ) ? true : false;

		// Update or create the WordPress post object
        $data = array(
            'post_title' 	=> $form_data['project_name'],
            'post_content' 	=> $form_data['project_description'],
            'post_type' 	=> self::$POST_TYPE,
            'post_status' 	=> 'publish'
        );

        if ( $is_update ) {
            $data['ID'] = $existing_project_id;
            $project_id = wp_update_post( $data );
        } else {
            $project_id = wp_insert_post( $data );
        }

        if ( $project_id ) {
			// Update the post meta data
			$users = array();
			if ( isset(  $form_data[ 'project-leaders' ] ) ) { 
				$users[ TPressProjectsPlugin::$ROLE_PROJECT_LEADER ] 	= $form_data[ 'project-leaders' ];
			}
			if ( isset(  $form_data[ 'project-coworkers' ] ) ) { 
				$users[ TPressProjectsPlugin::$ROLE_PROJECT_COWORKER ] 	= $form_data[ 'project-coworkers' ];
			}
			if ( isset(  $form_data[ 'project-clients' ] ) ) { 
				$users[ TPressProjectsPlugin::$ROLE_PROJECT_CLIENT ] 	= $form_data[ 'project-clients' ];
			}			
			
			if ( !empty( $users ) ) self::setUsers( $project_id, $users );
			
			// Give a change to plugins to do something about our project
            if ( $is_update ) {
                do_action( 'tpress_project_update', $project_id, $form_data );
            } else {
                do_action( 'tpress_project_create', $project_id, $form_data );
            }
        }

        return $project_id;
    }
	
	/** Custom post type setup ***************************************************************************************/
	
	/**
	 * Setup the post type
	 */
	public static function registerPostType() {
		$post_type = array();

		// Project labels
		$post_type['labels'] = array(
			'name'               => __( 'TeamPress &raquo; Projects', 	'tpress' ),
			'menu_name'          => __( 'Projects',                   	'tpress' ),
			'singular_name'      => __( 'Project',                    	'tpress' ),
			'all_items'          => __( 'All Projects',               	'tpress' ),
			'add_new'            => __( 'New Project',                	'tpress' ),
			'add_new_item'       => __( 'Create New Project',         	'tpress' ),
			'edit'               => __( 'Edit',                       	'tpress' ),
			'edit_item'          => __( 'Edit Project',               	'tpress' ),
			'new_item'           => __( 'New Project',                	'tpress' ),
			'view'               => __( 'View Project',               	'tpress' ),
			'view_item'          => __( 'View Project',               	'tpress' ),
			'search_items'       => __( 'Search Projects',            	'tpress' ),
			'not_found'          => __( 'No projects found',          	'tpress' ),
			'not_found_in_trash' => __( 'No projects found in Trash', 	'tpress' ),
			'parent_item_colon'  => __( 'Parent Project:',            	'tpress' )
		);

		// Project rewrite
		$post_type['rewrite'] = array(
			'slug'       => self::getProjectPostTypeSlug(),
			'with_front' => false
		);

		// Project supports
		$post_type['supports'] = array(
			'title', 'editor', 'thumbnail'
		);

		// Register Project content type
		register_post_type(
				self::$POST_TYPE,
				apply_filters('tpress_project_register_post_type', array(
						'label' 				=> __( 'Projects', 'tpress' ),
						'description'         	=> __( 'A Project in the TeamPress sense', 'tpress' ),
						'labels'              	=> $post_type['labels'],
						'public'              	=> true,
						'exclude_from_search' 	=> true,
						'publicly_queryable' 	=> true,
						'show_ui'             	=> current_user_can( 'tpress_manage_projects' ),
						'show_in_menu'			=> 'tpress-home',
						'hierarchical'			=> false,
						'supports'            	=> $post_type['supports'],
						'has_archive'			=> true,
						'rewrite'             	=> $post_type['rewrite']
					) )
			);
	}
	
	/** Private Methods **********************************************************************************************/
	
	private static function getProjectPostTypeSlug() {
		return apply_filters( 'tpress_project_post_type_slug', 'project' );
	}	
}

endif; // class_exists