<?php 
$is_left = false;
foreach ( $statistics as $slug => $stat ) : 
	$is_left = !$is_left;
?>
<?php if ( $is_left ) : ?>
	<div class="table-container">
<?php endif; ?>

	<div class="table <?php echo $is_left ? 'table_left' : 'table_right'; ?>">
		<p class="sub"><?php echo $stat[ 'display_name' ]; ?></p>
		<table>
<?php 	foreach ( $stat[ 'data' ] as $data ) : 
			$num  = $data[ 'number' ];
			$text = $data[ 'text' ];
			$link = esc_attr( $data[ 'link' ] );
			
			if ( isset( $link ) ) {
				$num  = '<a href="' . $link . '">' . $num  . '</a>';
				$text = '<a href="' . $link . '">' . $text . '</a>';				
			}
?>
			<tr>
				<td class="b"><?php echo $num; ?></td>
				<td class="t"><?php echo $text; ?></td>
			</tr>
<?php 	endforeach; ?>
		</table>
	</div>
<?php if ( !$is_left ) : ?>
		<br class="clear" />
	</div>
	<br class="clear" />
<?php endif; 
endforeach; 
?>
<?php if ( $is_left ) : ?>
		<br class="clear" />
	</div>
	<br class="clear" />
<?php endif; ?>

	<div class="versions">
		<span id="wp-version-message">
			<?php printf( __( 'You are using <span class="b">TeamPress %s</span>.', 'bbpress' ), TeamPress::instance()->getVersion() ); ?>
		</span>
	</div>

	<br class="clear" />

<?php do_action( 'tpress_dashboard_right_now_widget_end' ); ?>
