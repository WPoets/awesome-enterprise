<?php
require('class-aw_modules-tables.php');

add_action( 'admin_init', 'rights_settings_init' );

function rights_options_page($app) {
    $option_slug = 'awesome-app-' . $app['slug'];
    $options = get_option( $option_slug );
    
    $tab = $_GET["tab"];
    if(isset($tab)){
        $active_tab = $tab;
    }else{
        $active_tab = 'settings';
    }
    ?>
    <div id='rights-wrapper' class="wrap">
        <div id="icon-options-general" class="icon32"></div>
        <h1><?php echo $app['name'] . ' Rights' ?></h1>
        <?php
            if(1 == $options['enable_rights']){
                $apppage = 'awesome-app-' . $app['slug'];
            ?>
            <h2 class="nav-tab-wrapper">
                <a href="?page=<?=$apppage?>&tab=settings" class="nav-tab <?php if($active_tab == 'settings'){echo 'nav-tab-active';} ?> "><?php _e('Rights Settings', 'sandbox'); ?></a>
                <a href="?page=<?=$apppage?>&tab=vsession" class="nav-tab <?php if($active_tab == 'vsession'){echo 'nav-tab-active';} ?>"><?php _e('Vsession', 'sandbox'); ?></a>
                <a href="?page=<?=$apppage?>&tab=single_access" class="nav-tab <?php if($active_tab == 'single_access'){echo 'nav-tab-active';} ?>"><?php _e('Single Access', 'sandbox'); ?></a>
                <a href="?page=<?=$apppage?>&tab=module-caps" class="nav-tab <?php if($active_tab == 'module-caps'){echo 'nav-tab-active';} ?>"><?php _e('Module Capabilities', 'sandbox'); ?></a>
            </h2>
            <?php
            }else{
                $active_tab = 'settings';
            }
            
            settings_errors();
            
            switch($active_tab){
                case 'settings':
                    showSettings($option_slug);
                break;
                case 'vsession':
                    showVsession($option_slug);
                break;
                case 'single_access':
                    showSinlgeAccess($option_slug);
                break;
                case 'module-caps':
                    getModulelist($app);
                break;
            }
            
            if(('1' != $options['enable_rights']) || ('1' != $options['enable_single_access'] && $active_tab == 'single_access') || ('1' != $options['enable_vsession'] && $active_tab == 'vsession')){
                echo "<script>jQuery('#rights-wrapper table tr').css('display','none'); jQuery('#rights-wrapper table tr:first').css('display','block');</script>";
            }
        ?>
    </div>
    <?php
}

function showSettings($option_slug){
    ?>
    <form action='options.php' method='post'>
    <input type=hidden name=page value='<?=$option_slug?>' />
    <?php
        settings_fields( 'rightsFields' );
        do_settings_sections( 'rightsFields' );
        submit_button('Save Rights');
    ?>
    </form>
    <?php
}

function showVsession($option_slug){
    ?>
    <form action='options.php' method='post'>
    <input type=hidden name=page value='<?=$option_slug?>' />
    <input type=hidden name=vsession_page value='true' />
    <?php
        settings_fields( 'rightsFields' );
        do_settings_sections( 'vsessionFields' );
        submit_button('Save Vsession Changes');
    ?>
    </form>
    <?php
}

function showSinlgeAccess($option_slug){
    ?>
    <form action='options.php' method='post'>
    <input type=hidden name=page value='<?=$option_slug?>' />
    <input type=hidden name=single_access_page value='true' />
    <?php
        settings_fields( 'rightsFields' );
        do_settings_sections( 'sinlgeAccessFields' );
        submit_button('Save Single Access Changes');
    ?>
    </form>
    <?php
}

function getModulelist($app){
    $role = $_GET['role'];
    if($role){
    ?>
    
    <div class="wrap">
        <h2><?php _e( "Select modules for $role role", 'awesome' ); ?></h2>

        <form method="post">
            <input type="hidden" name="rights_table" value="true">

            <?php
                $list_table = new wp_posts();
                $list_table->prepare_items('m_' . $app['slug']);
                $list_table->search_box( 'search', 'search_id' );
                $list_table->display();
            ?>
        </form>
    </div>
    <?php
    }else{
        $all_roles = wp_roles()->get_names();
        $option_slug = 'awesome-app-' . $app['slug'];
        $options = get_option( $option_slug );

        echo '<h2>Select Role</h2>';
        $has_role_buttons = false;
        foreach($all_roles as $key => $role){
            if($role == 'Administrator') continue;
            if(!isset($options['roles'][$key]) || ('1' == $options['roles'][$key]['access'])) continue;
            $has_role_buttons = true;
            $apppage = 'awesome-app-' . $app['slug'];
            ?>
            <a href="?page=<?=$apppage?>&tab=module-caps&role=<?=$key?>" class="nav-tab"><?= $role?></a>
            <?php
        }
        if(!$has_role_buttons){
            echo "<p>Please select at least 1 restricted role in the settings section</p>";
        }
    }
}

function rights_settings_init() {
    $option_slug = $_REQUEST['page'];
    register_setting( 'rightsFields', $option_slug, 'rightsFieldsValidation' );
    
    add_settings_field( 
		'enable_rights', 
		'Enable Login', 
        'enable_rights_callback',
        'rightsFields',
        'rights_section',
        $option_slug 
	);
    
    add_settings_field( 
        'roles',
        'Roles',
        'roles_callback',
        'rightsFields', 
        'rights_section',
        $option_slug
    );
    
    add_settings_field( 
		'unlogged', 
		'Custom unlogged url (Optional)', 
        'unlogged_callback',
        'rightsFields',
        'rights_section',
        $option_slug 
	);
    
    add_settings_section(
        'rights_section', 
        '', 
        '', 
        'rightsFields'
    );

    // setting fields for vsession
    add_settings_field( 
        'enable_vsession', 
        'Enable Vsession Login', 
        'enable_vsession_callback',
        'vsessionFields',
        'vsession_section',
        $option_slug 
    );

    add_settings_field( 
        'vsession_key', 
        'Vsession key', 
        'vsession_key_callback',
        'vsessionFields',
        'vsession_section',
        $option_slug 
    );

    add_settings_section(
        'vsession_section', 
        '', 
        '', 
        'vsessionFields'
    );

    // setting fields for single access
    add_settings_field( 
        'enable_single_access', 
        'Enable Single Access', 
        'enable_single_access_callback',
        'sinlgeAccessFields',
        'single_access_section',
        $option_slug 
    );

    add_settings_field( 
        'single_access_roles', 
        'User Roles', 
        'single_access_roles_callback',
        'sinlgeAccessFields',
        'single_access_section',
        $option_slug 
    );

    add_settings_section(
        'single_access_section',
        '', 
        '', 
        'sinlgeAccessFields'
    );
    
}

function rightsFieldsValidation($newoptions){
    $options = get_option($_POST['page']);
    
    if(isset($_POST['vsession_page'])){
        $options['enable_vsession'] = $newoptions['enable_vsession'];
        $options['vsession_key'] = $newoptions['vsession_key'];
    }else if(isset($_POST['single_access_page'])){
        $options['enable_single_access'] = $newoptions['enable_single_access'];
        $options['single_access_roles'] = implode(',', $_POST['single-access-roles']);
    }else{
        $roles = array();
        foreach($newoptions as $key => $option){
            if(strpos($key, 'role_') === 0){
                $role = str_replace('role_','',$key);
                $roles[$role]['enable'] = true;
                $roles[$role]['access'] = true; //setting default access true
            }
            if(strpos($key, 'r_access_') === 0){
                $role = str_replace('r_access_','', $key);
                if(isset($roles[$role])) $roles[$role]['access'] = $option;
            }
        }

        $options['roles'] = $roles;
        $options['enable_rights'] = $newoptions['enable_rights'];
        $options['unlogged'] = $newoptions['unlogged'];
    }
    
    return $options;
}

function enable_rights_callback($option_slug) {
    $options = get_option( $option_slug );
    ?>
	<input type='checkbox' name='<?= $option_slug;?>[enable_rights]' <?php checked( $options['enable_rights'], 1 ); ?> value='1'>
	<?php
}

function enable_vsession_callback($option_slug){
    $options = get_option( $option_slug );
    ?>
	<input type='checkbox' name='<?= $option_slug;?>[enable_vsession]' <?php checked( $options['enable_vsession'], 1 ); ?> value='1'>
	<?php
}

function enable_single_access_callback($option_slug){
    $options = get_option( $option_slug );
    ?>
	<input type='checkbox' name='<?= $option_slug;?>[enable_single_access]' <?php checked( $options['enable_single_access'], 1 ); ?> value='1'>
	<?php
}

function vsession_key_callback($option_slug){
    $options = get_option( $option_slug );
    ?>
    <input type='text' name='<?= $option_slug;?>[vsession_key]' placeholder='Email' value="<?= $options['vsession_key']?>">
	<p class='description'>This key is used to check if user is vsession logged in or not. Set this key after user successfully logged in. default key is 'email'.</p>
    <?php
}

function unlogged_callback($option_slug){
    $options = get_option( $option_slug );
    ?>
    <code><?=site_url();?>/</code>
    <input type='text' name='<?= $option_slug;?>[unlogged]' value="<?= $options['unlogged']?>">
	<p class='description'>To redirect user when don't have access. default wp_login</p>
    <?php
}

function roles_callback( $option_slug ) {
    $options = get_option( $option_slug );
    $all_roles = wp_roles()->get_names();
    
    foreach($all_roles as $key => $role){
        if($role == 'Administrator') continue;
        ?>
        <input type='checkbox' id="role-<?=$key?>" name='<?php echo $option_slug . '[role_' . $key . ']'?>' <?php checked( $options['roles'][$key]['enable'], 1 ); ?> value='1'>
        <label for='role-<?=$key?>'><?=$role?></label>
        <input type='radio' id="role-<?=$key?>1" name='<?php echo $option_slug . '[r_access_' . $key . ']'?>' <?php checked( $options['roles'][$key]['access'], 1 ); ?> value='1'>
        <label for='role-<?=$key?>1'>All Access</label>
        <input type='radio' id="role-<?=$key?>2" name='<?php echo $option_slug . '[r_access_' . $key . ']'?>' <?php checked( $options['roles'][$key]['access'], 0 ); ?> value='0'>
        <label for='role-<?=$key?>2'>Restricted Access</label><br>
        <?php
    }
}

function single_access_roles_callback( $option_slug ) {
    $options = get_option( $option_slug );
    $all_roles = wp_roles()->get_names();
    
    foreach($all_roles as $key => $role){
        if($role == 'Administrator') continue;
        ?>
        <input type='checkbox' id="role-<?=$key?>" name='single-access-roles[]' <?php checked( in_array($key ,explode(',', $options['single_access_roles'])) ); ?> value='<?=$key?>'>
        <label for='role-<?=$key?>'><?=$role?></label>
        <br>
        <?php
    }
}