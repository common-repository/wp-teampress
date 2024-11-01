<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'TPressTaskListTable' ) ) :


class TPressTaskListTable extends WP_List_Table {

	/**
	 * Constructor, we override the parent to pass our own arguments
	 *
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	function __construct( $query ) {
		parent::__construct( array(
				'singular'=> 'wp_list_text_link', 		// Singular label
				'plural' => 'wp_list_test_links', 		// plural label, also this well be one of the table css class
				'ajax'	=> false 						// We won't support Ajax for this table
			) );
		
		$this->query = $query;
	}
	
	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		return $columns= array(
				'cb'			=> '<input type="checkbox" />',
				'title'			=> __( 'Task', 'tpress' ),
				'assignees'		=> __( 'Assignees', 'tpress' ),
				'due_date'		=> __( 'Due Date', 'tpress' ),
				'tags'			=> __( 'Tags', 'tpress' ),
				'comments'		=> '<span><span class="vers"><div title="' . __( 'Comments', 'tpress' ) . '" class="comment-grey-bubble"></div></span></span>'
		);
	}
	
	/**
	 * Decide which columns to activate the sorting functionality on
	 * @return array $sortable, the array of columns that can be sorted by the user
	 */
	public function get_sortable_columns() {
// 		return $sortable = array(
// 				'col_link_id'=>'link_id',
// 				'col_link_name'=>'link_name',
// 				'col_link_visible'=>'link_visible'
// 		);
		return array();
	}
	
	function column_default( $item, $column_name ) {
		return print_r( $item, true ); //Show the whole array for troubleshooting purposes
	}
	
	function column_assignees( $item ) {
		return the_task_assignees( $item, ", ", false );
	}
	
	function column_due_date( $item ) {
		if ( !isset( $item->due_date ) ) return '';		 
		return WordPressHelper::convertDateFromMysqlFormat( $item->due_date, get_option( 'date_format' ), true );
	}
	
	function column_tags( $item ) {
		return the_task_tags( $item, ", ", false );
	}
	
	function column_comments( $item ) {
		return $this->comments_bubble( $item->ID, get_comment_count( $item->ID )[ "total_comments" ] );
	}
	
	function column_title( $item ) {
		//Build row actions
		$actions = array(
				'edit'      => sprintf('<a href="?page=%s&view=%s&action=%s&task-id=%s">%s</a>', $_REQUEST['page'], $_REQUEST['view'], 'edit', $item->ID, __( 'Edit', 'tpress') ),
				'delete'    => sprintf('<a href="?page=%s&view=%s&action=%s&task-id=%s">%s</a>', $_REQUEST['page'], $_REQUEST['view'], 'delete', $item->ID, __( 'Delete', 'tpress') ),
			);
	
		//Return the title contents
		return sprintf( '%1$s %2$s', get_the_title( $item ), $this->row_actions($actions) );
	}
	
	function column_cb( $item ) {
		// That's the first column, so we'll read the meta here.
		$item = TPressTask::readPostMeta( $item );
		
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />',
				/*$1%s*/ $this->_args['singular'],  // Let's simply repurpose the table's singular label ("movie")
				/*$2%s*/ $item->ID                	// The value of the checkbox should be the record's id
			);
	}
	
	// Overriden to change the bubble text
	function comments_bubble( $post_id, $pending_comments ) {
		$pending_phrase = sprintf( _n( '%s comment', '%s comments', $pending_comments, 'tpress' ), $pending_comments );

		if ( $pending_comments )
			echo '<strong>';

		echo "<a href='" . esc_url( add_query_arg( 'p', $post_id, admin_url( 'edit-comments.php' ) ) ) . "' title='" . esc_attr( $pending_phrase ) . "' class='post-com-count'><span class='comment-count'>" . number_format_i18n( get_comments_number() ) . "</span></a>";

		if ( $pending_comments )
			echo '</strong>';
	}
		
	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		global $_wp_column_headers;
		
		// define our column headers
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);

		// Process bulk actions if necessary
		// $this->process_bulk_action();
		
		// Pagination
		$row_count = $this->query->post_count;
		$items_per_page = 15;
		$current_page =  $this->get_pagenum();
		$page_count = ceil( $row_count / $items_per_page );
		
		$this->query->set( 'posts_per_page', $items_per_page );
		$this->query->set( 'paged', $current_page );
		
		$this->set_pagination_args( array(
				"total_items" 	=> $row_count,
				"total_pages" 	=> $page_count,
				"per_page" 		=> $items_per_page,
			) );
	
		$this->items = $this->query->get_posts();
	}
	
	/** @var WP_Query $query */
	private $query;
}

endif;