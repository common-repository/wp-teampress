<?php 
global $wp_roles;

$roles 	= $wp_roles->role_objects;
$all_plugins = TeamPress::instance()->getRegisteredPlugins();
$column_count = 1; // We got an empty column on the left
?>

<table class="widefat">
	<thead>
		<tr>
			<th></th>
<?php 		foreach ( $roles as $role ) : 
				if ( $this->isRoleHidden( $role ) ) continue; 
				++$column_count; 
?>
			<th><?php echo WordPressHelper::getRoleDisplayName( $role->name ); ?></th>		
<?php 		endforeach; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th></th>
<?php 		foreach ( $roles as $role ) :
				if ( $this->isRoleHidden( $role ) ) continue; ?>
			<th><?php echo WordPressHelper::getRoleDisplayName( $role->name ); ?></th>			
<?php 		endforeach; ?>
		</tr>
	</tfoot>
	<tbody>
	
<?php
foreach ( $all_plugins as $plugin ) :
	$plugin_caps = $plugin->getAllCapabilities();
	
	if ( empty( $plugin_caps ) ) continue;	
?>	
	
		<tr><th colspan="<?php echo $column_count; ?>"><h3><?php echo $plugin->getDisplayName(); ?></h3></th></tr>
	
<?php
	foreach ( $plugin_caps as $cap ) : 
		$cap_name = str_replace( 'tpress ', '', str_replace( '_', ' ', $cap ) );
?>
		<tr>
			<th><?php echo $cap_name; ?></th>
<?php	foreach ( $roles as $role ) : 
			if ( $this->isRoleHidden( $role ) ) continue;
			
			$id = str_replace( ' ', '-', $role->name . '_' . $cap );
			$checked = $role->has_cap( $cap ) ? 'checked="checked" ' : '';
?>
			<td title="<?php echo esc_attr( $cap_name ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $id ); ?>" <?php echo $checked; ?>value="1" />
			</td>
<?php 	endforeach; // Roles ?>
		</tr>
<?php	
	endforeach; // Caps ?>
	</tbody>
<?php
endforeach; // Plugins
?>
</table>
