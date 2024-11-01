<?php 
$all_plugins = TeamPress::instance()->getRegisteredPlugins();	

foreach ( $all_plugins as $plugin ) :			
	if ( !( $plugin instanceof TPressUserGroupBasedPlugin ) ) continue;	
	
	$plugin_caps 	= $plugin->getUserGroupsCapabilities();
	$groups			= $plugin->getUserGroups();
	
	if ( empty( $plugin_caps ) || empty( $groups ) ) continue;
?>

<h3><?php echo $plugin->getDisplayName(); ?></h3>

<table class="widefat">
	<thead>
		<tr>
			<th></th>
<?php 		foreach ( $groups as $group ) : ?>
			<th><?php echo WordPressHelper::getRoleDisplayName( $group->name ); ?></th>		
<?php 		endforeach; ?>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th></th>
<?php 		foreach ( $groups as $group ) : ?>
			<th><?php echo WordPressHelper::getRoleDisplayName( $group->name ); ?></th>			
<?php 		endforeach; ?>
		</tr>
	</tfoot>
	<tbody>
	
<?php
	
	foreach ( $plugin_caps as $cap ) : 
		$cap_name = str_replace( 'tpress ', '', str_replace( '_', ' ', $cap ) );
?>
			<tr>
				<th><?php echo $cap_name; ?></th>
<?php	foreach ( $groups as $group ) : 
			$id = str_replace( ' ', '-', $group->name . '_' . $cap );
			$checked = $group->has_cap( $cap ) ? 'checked="checked" ' : '';
?>	
				<td title="<?php echo esc_attr( $cap_name ); ?>">
					<input type="checkbox" name="<?php echo esc_attr( $id ); ?>" <?php echo $checked; ?>value="1" />
				</td>
<?php 	endforeach; // Roles ?>
			</tr>
<?php	
	endforeach; // Caps ?>
		</tbody>
</table>

<?php
endforeach; // Plugin
?>

<h3>Reset User Groups</h3>

<table class="form-table">
	<tr>
		<td><input type="submit" name="tpress_reset_groups" 
				value="<?php _e("Reset All User Groups", "tpress"); ?>" class="button-primary" /></td>
	</tr>
</table>
