<?php
namespace awesome\monoframe\editor;

add_filter('map_meta_cap', 'awesome\monoframe\editor\super_admin_control', 10, 4);
add_action('admin_init' ,'awesome\monoframe\editor\awesome_cap');
function awesome_cap(){
	global $user_ID;
	
	add_action( 'show_user_profile', 'awesome\monoframe\editor\user_profile_fields' );
	add_action( 'edit_user_profile', 'awesome\monoframe\editor\user_profile_fields' );
	add_action( 'profile_update',  'awesome\monoframe\editor\save_profile_update' );

	add_filter( 'manage_users_columns', 'awesome\monoframe\editor\manage_users_columns' );
	add_filter( 'manage_users_custom_column', 'awesome\monoframe\editor\manage_users_custom_column', 10, 3 );
 
   
}		

function save_profile_update( $user_id ) {
		global $wp_roles;
		if ( ! is_super_admin() && ! current_user_can( 'manage_options' ) )
			return;

		if ( empty( $user_id ) )
			return;
		//get user for adding/removing role
		$user = new \WP_User( $user_id );
		
		if ( ! isset( $_POST['awesome_adf_dev_cap'] ) )
		{
			update_user_meta( $user->ID, 'develop_for_awesomeui', 'nope');
			$user->remove_cap( 'develop_for_awesomeui' );
			return;
		}

		//add new role to user
		if ( ! empty( $_POST['awesome_adf_dev_cap'] ) )
		{	
			update_user_meta( $user->ID, 'develop_for_awesomeui', 'yes');
			foreach($_POST['awesome_adf_dev_cap'] as $cap){
				$user->add_cap( $cap);
			}
		}
		return;
	}
	
function user_profile_fields( $user ) {
		global $wp_roles;

		if ( ! is_super_admin() && ! current_user_can( 'manage_options' ) )
			return;
		//print_r($user);
		$checked='';
		if(user_can( $user->ID, 'develop_for_awesomeui' )	)
			$checked='checked="checked"';
			
		?>
		    <h3>Awesome ADF Control</h3>
		    <table class="form-table">
		        <tr>
		            <th>
		                <label for="awesome_adf_dev_cap">Awesome ADF Permissions</label>
					</th>
		            <td>
						<input type='checkbox' value='develop_for_awesomeui' name='awesome_adf_dev_cap[]' <?php echo $checked; ?> id='awesome_adf_dev_cap'>Grant Developer Access
		                <p class="description">This allows user to see and manage Awesome ADF Components</p>
		            </td>
		        </tr>
		    </table>
		<?php
}
function manage_users_columns( $columns ) {

	$columns[ 'wpoets_role' ] ='Awesome ADF Role';
	return $columns;
}

function manage_users_custom_column( $value, $column_name, $user_id ) {
	global $wp_roles;

	if ( 'wpoets_role' != $column_name )
		return $value;

	//$user = get_userdata( $user_id );
	if ( user_can( $user_id, 'develop_for_awesomeui' ) ) {
		$value='Developer Access';
	}

	return $value;
}

function super_admin_control($caps, $cap, $user_id, $args){
	global $wp_roles;
	if(is_multisite()){
		if ($user_id != 0) {
			$develop=get_user_meta( $user_id, 'develop_for_awesomeui',true);
			if($cap=='develop_for_awesomeui' && $develop != 'yes') {
				//$role->add_cap($cap);
				 $caps[] = 'do_not_allow';
			}		
		}
	}
	return $caps;
}