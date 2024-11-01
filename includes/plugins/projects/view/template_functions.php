<?php

if ( !function_exists( 'the_project_users_summary' ) ) :

	/**
	 * Outputs the users as a short list of gravatars. 
	 * 
	 * The users are not sorted by default but you can plug your own logic using the 'the_project_users_summary_users' 
	 * filter.
	 *
	 * Output can also be modified by the 'the_project_users_summary' filter.
	 */
	function the_project_users_summary( $max_count = 9, 
			$container_elt = 'ul', $container_class = "project-users",
			$item_elt = 'li', $item_class = 'project-user', $link_class = "tpress-user-badge",
			$show_avatars = true ) {
		global $post;
		
		$users = apply_filters( 'the_project_users_summary_users', $post->users );
		
		$count = 0;
		$show_more_button = count( $users ) > 9;
		$max_count = $show_more_button ? $max_count - 1 : $max_count;
				
		$out  = "<$container_elt class='$container_class'>";

		foreach ( $users as $group => $group_users ) {
			foreach ( $group_users as $user_id ) {
				$u = get_userdata( $user_id );
				if ( $u===FALSE ) continue;
				
				$a_title = esc_attr( 
						sprintf( "%s (%s)", $u->display_name, WordPressHelper::getRoleDisplayName( $group ) ) );			
				$a_content = $show_avatars ? get_avatar( $user_id ) : $u->display_name;
				$link = '';
				
				$out .= "<$item_elt class='$item_class'>";			
				$out .= sprintf( "<a href='%s' title='%s' class='$link_class'>%s</a>", $link, $a_title, $a_content );			
				$out .= "</$item_elt>";
				
				++$count;
				if ( $count >= $max_count ) break;
			}
		}
		
	    if ( $show_more_button ) {			
			$a_title = esc_attr( 
					sprintf( __( "Afficher les %d utilisateurs du projet", 'tpress' ), count( $users ) ) );			
			$a_content = '...';
			$link = '';
			$out .= "<$item_elt class='$item_class'>";			
			$out .= sprintf( "<a href='%s' title='%s' class='$link_class'>%s</a>", $link, $a_title, $a_content );	
			$out .= "</$item_elt>";
		}
	
		$out .= "</$container_elt>";
		
		$out = apply_filters( 'the_project_users_summary', $out );
		
		echo $out;
	} // the_project_users_summary
	
endif; // function_exist

if ( !function_exists( 'the_project_users_block' ) ) :

	/**
	 * Outputs the users as a short list of gravatars. 
	 * 
	 * The users are not sorted by default but you can plug your own logic using the 'the_project_users_summary_users' 
	 * filter.
	 *
	 * Output can also be modified by the 'the_project_users_summary' filter.
	 */
	function the_project_users_block( $max_count = 20, 
			$container_elt = 'ul', $container_class = "project-users",
			$item_elt = 'li', $item_class = 'project-user', $link_class = "tpress-user-badge",
			$show_avatars = true ) {
		global $post, $user_ID;
		
		$users = apply_filters( 'the_project_users_block_users', $post->users );
				
		$count = 0;
		$show_more_button = count( $users ) > 9;
		$max_count = $show_more_button ? $max_count - 1 : $max_count;
				
		$out = "<$container_elt class='$container_class'>";
		
		foreach ( $users as $group => $group_users ) { 
			foreach ( $group_users as $user_id ) {
				$u = get_userdata( $user_id );
				if ( $u===FALSE ) continue;
				
				$a_title = esc_attr( 
						sprintf( "%s (%s)", $u->display_name, WordPressHelper::getRoleDisplayName( $group ) ) );			
				$a_content  = get_avatar( $user_id );			
				$link = '';
				
				$out .= "<$item_elt class='$item_class'>";		
				$out .= sprintf( "<a href='%s' title='%s' class='$link_class'>%s</a>", $link, $a_title, $a_content );	
				$out .= '<span class="name">' . $u->display_name . '</span>';			
				$out .= "</$item_elt>";
				
				++$count;
				if ( $count >= $max_count ) break;
			}
		}
		
	    if ( $show_more_button ) {			
			$a_title = esc_attr( 
					sprintf( __( "Afficher les %d utilisateurs du projet", 'tpress' ), count( $users ) ) );			
			$a_content = '...';
			$link = '';
			$out .= "<$item_elt class='$item_class'>";			
			$out .= sprintf( "<a href='%s' title='%s' class='$link_class'>%s</a>", $link, $a_title, $a_content );	
			$out .= "</$item_elt>";
		}
	
		$out .= "</$container_elt>";
		
		$out .= '<br class="clear">';
		
		$actions = array();
		if ( TPressProject::userCan( $post, $user_ID, 'tpress_manage_project_users' ) ) {
			$actions[] = array(
					'id' 					=> 'manage',
					'link' 					=> '',
					'label' 				=> __( 'Manage', 'tpress' )
				);
		}
		
		tpress_block( 'project_users', 
				__( 'People Involved', 'tpress' ), 
				$out,
				TPressProjectsPlugin::instance()->getProjectUsersLink( $post->ID ),
				$actions );
				
	} // the_project_users_block
	
endif; // function_exist

if ( !function_exists( 'the_project_description' ) ) :

	/**
	 * Outputs the project description
	 */
	function the_project_description() {
		global $post, $user_ID;

		$footer = '';
		$actions = array();
		if ( TPressProject::userCan( $post, $user_ID, 'tpress_edit_project_details' ) ) {
			$actions[] = array(
					'id' 	=> 'edit',
					'link' 	=> '',
					'label' => __( 'Edit', 'tpress' )
			);
		}
		if ( current_user_can( 'tpress_manage_projects' ) ) {
			
			$confirm_callback  = "            function() {\n";			
			$confirm_callback .= "            	  var element = $('#tpress_block_project_content a.block-action-delete');\n";
			$confirm_callback .= "            	  var project_id = element.attr('data-project_id');\n";
			$confirm_callback .= "            	  var nonce = element.attr('data-nonce');\n";				
			$confirm_callback .= "            	  $.post(ajaxurl, { action: 'tpress_delete_project', project_id : project_id, nonce: nonce },\n"; 
			$confirm_callback .= "            	  function(response) {\n";
			$confirm_callback .= "            	      handleAjaxJsonResponse(response);\n";
			$confirm_callback .= "            	  }, 'json');\n";
			$confirm_callback .= "            	  return false;\n";
			$confirm_callback .= "            }";
			
			$actions[] = array(
					'id' 					=> 'delete',
					'link' 					=> '',
					'label' 				=> __( 'Delete', 'tpress' ),
					'data'					=> array( 'project_id' => $post->ID, 'nonce' => wp_create_nonce("tpress_delete_project") ),
					'show_confirm_dialog' 	=> true,
					'confirmation_callback' => $confirm_callback
			);
		}
		
		tpress_block( 'project_content', 
				__( 'About the Project', 'tpress' ), 
				get_the_content(),
				'',
				$actions, 
				'', 
				$footer );
	}
	
endif; // function_exist

if ( !function_exists( 'the_project_statistics' ) ) :

	/**
	 * Outputs the stats of a project as a list. 
	 */
	function the_project_statistics( $container_elt = 'div', $container_class = "project-statistics" ) {
		global $post;
		
		$statistics = apply_filters( 'tpress_project_statistics', array(), $post );
		
		$out = "<$container_elt class='inside $container_class'>";
		
		$is_left = false;		
		foreach ( $statistics as $slug => $stat ) {
			$is_left = !$is_left;

			if ( $is_left ) {
				$out .= '<div class="table-container">';
			}
			
			$out .= "<div class='table " . ($is_left ? 'table_left' : 'table_right' ) . "'>";
			$out .= '<p class="sub">' . $stat[ 'display_name' ] . '</p>';
			$out .= '<table>';
			
			foreach ( $stat[ 'data' ] as $data ) {
				$num  = $data[ 'number' ];
				$text = $data[ 'text' ];
				$link = esc_attr( $data[ 'link' ] );
				
				if ( isset( $link ) ) {
					$num  = '<a href="' . $link . '">' . $num  . '</a>';
					$text = '<a href="' . $link . '">' . $text . '</a>';				
				}

				$out .= '<tr>';
				$out .= '<td class="b">'. $num . '</td>';
				$out .= '<td class="t">'. $text . '</td>';
				$out .= '</tr>';
			}
			
			$out .= '</table>';
			$out .= '</div>';
			
			if ( !$is_left ) {
				$out .= '<br class="clear">';
				$out .= '</div>';
				$out .= '<br class="clear" />';
			}
		}	
		$out .= '<br class="clear" />';
		$out .= "</$container_elt>";
		
		tpress_block( 'project_statistics', 
				__( 'In short&#8230;', 'tpress' ), 
				$out );
	}
	
endif; // function_exist