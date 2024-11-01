<?php

if ( !function_exists( 'tpress_card' ) ) :

	function tpress_card( $id, $filter_id, 
			$title, $content, 
			$title_link = '', $header = '', $footer = '', 
			$echo = true ) {
		
		$title 		= apply_filters( 'tpress_card_title_' . $filter_id, $title );
		$header 	= apply_filters( 'tpress_card_header_' . $filter_id, $header );
		$footer 	= apply_filters( 'tpress_card_footer_' . $filter_id, $footer );
		$content 	= apply_filters( 'tpress_card_content_' . $filter_id, $content );
		$title_link = apply_filters( 'tpress_card_title_link_' . $filter_id, $title_link );
		
		$out  = '<div id="tpress_' . $id . '" class="tpress-card '. $filter_id . '">';
		$out .= '<h3 class="card-title"><a href="' . esc_attr( $title_link ) . '">' . $title . '</a></h3>';
		$out .= '<div class="card-header">' . $header . '</div>';
		$out .= '<div class="card-content">' . $content . '</div>';
		$out .= '<div class="card-footer">' . $footer . '</div>';
		$out .= '</div>';
		
		if ( $echo ) echo $out;
		else return $out;
	}

endif; // function_exist

if ( !function_exists( 'tpress_block' ) ) :

	function tpress_block( $id, $title, $content, $title_link = '', $title_actions = array(), $header = '', $footer = '', $echo = true ) {
		
		$title 		= apply_filters( 'tpress_block_title_' . $id, $title );
		$header 	= apply_filters( 'tpress_block_header_' . $id, $header );
		$footer 	= apply_filters( 'tpress_block_footer_' . $id, $footer );
		$content 	= apply_filters( 'tpress_block_content_' . $id, $content );
		$title_link = apply_filters( 'tpress_block_title_link_' . $id, $title_link );		
		$title_actions = apply_filters( 'tpress_block_$title_actions_' . $id, $title_actions );

		$actions = '';
		if ( !empty( $title_actions ) ) {
			$actions = '<span class="actions">';
			$is_first = true;
			foreach ( $title_actions as $action ) {
				if ( !$is_first ) $actions .= " | ";
				else $is_first = false;
				
				$url = esc_attr( $action[ 'link' ] );
				
				$classes = "block-action";
				$classes .= " " . sanitize_html_class( 'block-action-' . $action[ 'id' ] );
				
				if ( isset( $action[ 'show_confirm_dialog' ] ) && $action[ 'show_confirm_dialog' ]==true ) {
					$classes .= " confirmation-required";
				}
				
				$data_attrs = '';
				if ( isset( $action[ 'data' ] ) ) {
					foreach ( $action[ 'data' ] as $key => $value) {
						$data_attrs .= ' data-' . $key . '="' . $value . '"';
					}
				}
				
				$actions .= sprintf('<a href="%s" class="%s" %s>%s</a>',
						$url,
						$classes,
						$data_attrs,
						$action[ 'label' ] );
			}
			$actions .= '</span>';
		}
		
		$javascript = '';
		if ( !empty( $title_actions ) ) {
			$javascript .= "<script type='text/javascript'>\n";
			$javascript .= "<!--\n";
			$javascript .= "jQuery(function($) {\n";
			
			$confirm_title 		= esc_attr__( 'Confirm', 'tpress' );
			$confirm_message 	= esc_attr__( 'Are you sure you want to do this?', 'tpress' );
			$ok_button 			= __( 'OK', 'tpress' );
			$cancel_button 		= __( 'Cancel', 'tpress' );
			
			foreach ( $title_actions as $action ) {
				if ( !isset( $action[ 'show_confirm_dialog' ] ) || $action[ 'show_confirm_dialog' ]==false || !isset( $action[ 'confirmation_callback' ] )) continue;

				$action_selector = "#tpress_block_" . $id . " a." .  sanitize_html_class( 'block-action-' . $action[ 'id' ] );
				
				$javascript .= "    $('" . $action_selector . "').click(function() {\n";
				$javascript .= "        showConfirmDialog( $, '" . $confirm_title . "',\n";
				$javascript .= "            '" . $confirm_message . "',\n";
				$javascript .= $action[ 'confirmation_callback' ] . ",\n";
				$javascript .= "            '" . $ok_button . "', '" . $cancel_button . "');\n";
				$javascript .= "        return false;\n";
				$javascript .= "    });\n";
			}
			
			$javascript .= "});\n";
			$javascript .= "//-->\n";
			$javascript .= '</script>';
		}
		
		
		$out  = '<div id="tpress_block_' . $id . '" class="tpress-block">';
		
		if ( !empty( $title_link ) )
 			$out .= '<div class="block-title"><h3><a href="' . esc_attr( $title_link ) . '">' . $title . '</a></h3>'. $actions . "</div>";
		else 
			$out .= '<div class="block-title"><h3>' . $title . '</h3>'. $actions . "</div>";
		
		$out .= '<div class="block-header">' . $header . '</div>';
		$out .= '<div class="block-content">' . $content . '</div>';
		$out .= '<div class="block-footer">' . $footer . '</div>';
		$out .= $javascript;
		$out .= '</div>';
		
		if ( $echo ) echo $out;
		else return $out;
	}

endif; // function_exist