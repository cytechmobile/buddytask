<?php
/**
 *  buddytask functions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * What is the version of the plugin.
 *
 * @uses  buddytask()
 * @return string the version of the plugin
 */
function buddytask_get_version() {
	return  buddytask()->version;
}

/**
 * Is it the first install ?
 *
 * @uses get_option() to get the  buddytask version
 * @return boolean true or false
 */
function buddytask_is_install() {
	$buddytask_version = get_option( '_ buddytask_version', '' );

	if ( empty( $buddytask_version ) ) {
		return true;
	}

	return false;
}

/**
 * Do we need to eventually update ?
 *
 * @uses get_option() to get the  buddytask version
 * @return boolean true or false
 */
function buddytask_is_update() {
	$buddytask_version = get_option( '_ buddytask_version', '' );

	if ( ! empty( $buddytask_version ) && version_compare( $buddytask_version,  buddytask_get_version(), '<' ) ) {
		return true;
	}

	return false;
}

/**
 * Gets the slug of the plugin
 *
 * @uses  buddytask() to get plugin's globals
 * @return string the slug
 */
function buddytask_get_slug() {
    $slug = buddytask()->buddytask_slug ;

    return apply_filters( 'buddytask_get_slug', $slug );
}

/**
 * Gets the name of the plugin
 *
 * @uses  buddytask() to get plugin's globals
 * @uses buddypress() to get directory pages global settings
 * @return string the name
 */
function buddytask_get_name() {
    $name = isset( buddypress()->pages->buddytask->slug ) ? buddypress()->pages->buddytask->title :  buddytask()->buddytask_name ;

    return apply_filters( 'buddytask_get_name', $name );
}


/**
 * What is the path to the includes dir ?
 *
 * @uses   buddytask()
 * @return string the path
 */
function buddytask_get_includes_dir() {
	return  buddytask()->includes_dir;
}

/**
 * What is the path of the plugin dir ?
 *
 * @uses   buddytask()
 * @return string the path
 */
function buddytask_get_plugin_dir() {
	return  buddytask()->plugin_dir;
}

/**
 * What is the url to the plugin dir ?
 *
 * @uses   buddytask()
 * @return string the url
 */
function buddytask_get_plugin_url() {
	return  buddytask()->plugin_url;
}

/**
 * Welcome screen step one : set transient
 *
 * @uses  buddytask_is_install() to check of first install
 * @uses set_transient() to temporarly save some data to db
 */
function buddytask_add_activation_redirect() {
	// Bail if activating from network, or bulk
	if ( isset( $_GET['activate-multi'] ) && is_bool($_GET['activate-multi']))
		return;

	// Record that this is a new installation, so we show the right
	// welcome message
	if (  buddytask_is_install() ) {
		set_transient( '_ buddytask_is_new_install', true, 30 );
	}

	// Add the transient to redirect
	set_transient( '_ buddytask_activation_redirect', true, 30 );
}

/**
 * Checks plugin version against db and updates
 *
 * @uses  buddytask_is_install() to see if first install
 * @uses  buddytask_get_db_version() to get db version
 * @uses  buddytask_get_version() to get  buddytask plugin version
 */
function buddytask_check_version() {
	// Bail if config does not match what we need
	if (  buddytask::bail() ) {
		return;
	}

	// Finally upgrade plugin version
	update_option( '_ buddytask_version',  buddytask_get_version() );
}
add_action( 'buddytask_admin_init', 'buddytask_check_version' );

function buddytask_default_settings(){
    $defaults = array(
        'enabled' => true,
        'lists' => array('Backlog', 'Todo', 'Doing', 'Done')
    );
    return apply_filters( 'buddytask_default_settings', $defaults );
}

function buddytask_groups_get_groupmeta($group_id, $meta_key, $default){
    $value = groups_get_groupmeta( $group_id, $meta_key, true);
    if(!$value){
        $value = $default;
    }
    return $value;
}

function buddytask_groups_update_groupmeta($group_id, $meta_key, $default){
    $value = sanitize_meta($meta_key, $_POST[$meta_key], 'bp_groups');
    if(!$value){
        $value = $default;
    }
    groups_update_groupmeta( $group_id, $meta_key, $value );
}

function buddytask_is_enabled($group_id = false){
    if($group_id){
        $enabled = get_option('_buddytask_enabled') && groups_get_groupmeta($group_id, 'buddytask_enabled', true);
    } else {
        $enabled = get_option('_buddytask_enabled') === "1";
    }
    return $enabled;
}