<?php
/**
 *  buddytask Groups
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'BP_Group_Extension' ) && bp_is_active( 'groups' ) ) :

/**
 * The  buddytask group class
 *
 * @package  buddytask
 * @since 1.0.0
 * @version 1.1.0 (BP 12.0+ Compatible)
 */
class  BuddyTask_Group extends BP_Group_Extension {
    function __construct() {
        $args = array(
            'name' => buddytask_get_name(),
            'slug' =>  buddytask_get_slug(),
            'nav_item_position' => 40,
            'show_tab_callback' => array( $this, 'show_tab' )
        );
        parent::init( $args );
    }

    function show_tab( $group_id = null ) {

        if ( ! $group_id ) {
            $group_id = bp_get_current_group_id();
        }

        $show_tab = 'noone';
        $active = groups_get_groupmeta( $group_id, 'buddytask_enabled', true );
        if ( $group_id && $active ) {
            $show_tab = 'anyone';
        }

        return $show_tab;
    }

    function create_screen( $group_id = null) {
        if ( !bp_is_group_creation_step( $this->slug ) )
            return false;

        wp_nonce_field( 'groups_create_save_' . $this->slug );

        $this->render_settings($group_id, true);
    }

    function create_screen_save( $group_id = null) {
        if ( ! $group_id ) {
            $group_id = bp_get_current_group_id();
        }

        check_admin_referer( 'groups_create_save_' . $this->slug );

        $this->persist_settings($group_id);
    }

    function edit_screen( $group_id = null ) {
       if (! bp_is_group_admin_screen( $this->slug ) ) {
            return false;
        }

        if (!$group_id){
            $group_id = bp_get_current_group_id();
        }

        // Permission check.
        if (! groups_is_user_admin( bp_loggedin_user_id(), $group_id ) && ! current_user_can( 'bp_moderate' ) ) {
            return false;
        }

        wp_nonce_field( 'groups_edit_save_' . $this->slug );

        $this->render_settings($group_id, false);
        ?>

        <p><input type="submit" name="save" value="<?php esc_attr_e( 'Save Settings', 'buddytask' );?>" /></p>
        <?php
    }

    function edit_screen_save( $group_id = null ) {
        $save = sanitize_text_field($_POST['save']);
        if ($save == null)
            return false;

        if ( !$group_id ) {
            $group_id = bp_get_current_group_id();
        }

        check_admin_referer( 'groups_edit_save_' . $this->slug );

        $this->persist_settings($group_id);

        bp_core_add_message( esc_html__( 'Settings saved successfully', 'buddytask' ) );

        $group = groups_get_group( $group_id );
        if ( $group ) {
            bp_core_redirect( bp_get_group_permalink( $group ). 'admin/'. $this->slug );
        }
    }

    function display( $group_id = null ) {
        if (! $group_id ) {
            $group_id = bp_get_current_group_id();
        }

        if (! $group_id ) {
            // Should not happen if nav item is displayed, but as a safeguard.
            return;
        }

        // Check membership status.
        if ( groups_is_user_member( bp_loggedin_user_id(), $group_id )
            || groups_is_user_mod( bp_loggedin_user_id(), $group_id )
            || groups_is_user_admin( bp_loggedin_user_id(), $group_id )
            || is_super_admin() ) {

            $enabled = groups_get_groupmeta( $group_id, 'buddytask_enabled', true );
            if ( $enabled == 1 ) {
                $this->get_groups_template_part( 'tasks/home' );
            }
        }  else {
            // Display a message for non-members.
            bp_core_add_message( esc_html__( 'This content is only available to group members.', 'buddytask' ), 'error' );
            $template = bp_locate_template( 'groups/single/home.php', false, true );
            if ( $template ) {
                require $template;
            }
        }
    }

    function render_settings($group_id, $is_create){
        $defaults =  buddytask_default_settings();
        $enabled = $is_create ? $defaults['enabled'] : buddytask_is_enabled($group_id);

        ?>
        <h4><?php echo esc_html( buddytask_get_name(). ' '. __( 'Settings', 'buddytask' ) );?></h4>

        <fieldset>
            <div class="field-group">
                <div class="checkbox">
                    <label for="buddytask_enabled">
                        <input type="checkbox" name="buddytask_enabled" id="buddytask_enabled" value="1" <?php checked( $enabled );?>>
                        <?php esc_html_e( 'Enable tasks for this group.', 'buddytask' );?>
                    </label>
                </div>
                <p class="description"><?php esc_html_e( 'Check this box to activate the tasks feature within this group.', 'buddytask' );?></p>
            </div>
        </fieldset>
        <?php
    }

    function persist_settings($group_id){
        buddytask_groups_update_groupmeta($group_id, 'buddytask_enabled', "0");
    }

    function get_groups_template_part( $slug ) {
        add_filter( 'bp_locate_template_and_load', '__return_true'                        );
        add_filter( 'bp_get_template_stack', array($this, 'set_template_stack'), 10, 1 );

        bp_get_template_part( 'groups/single/' . $slug );

        remove_filter( 'bp_locate_template_and_load', '__return_true' );
        remove_filter( 'bp_get_template_stack', array($this, 'set_template_stack'), 10);
    }

    function set_template_stack( $stack = array() ) {
        if ( empty( $stack ) ) {
            $stack = array(  buddytask_get_plugin_dir() . 'templates' );
        } else {
            $stack[] =  buddytask_get_plugin_dir() . 'templates';
        }

        return $stack;
    }
}

/**
 * Waits for bp_init hook before loading the group extension
 *
 * Let's make sure the group id is defined before loading our stuff
 *
 * @since 1.0.0
 *
 * @uses bp_register_group_extension() to register the group extension
 */
function buddytask_register_group_extension() {
    if ( bp_is_active( 'groups' ) ) {
        bp_register_group_extension( 'BuddyTask_Group' );
    }
}
add_action( 'bp_init', 'buddytask_register_group_extension' );

endif;