<?php
/**
 *  BuddyTask Actions
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// BuddyPress / WordPress actions to  buddytask ones
add_action( 'bp_init',                  'buddytask_init',                     14 );
add_action( 'bp_ready',                 'buddytask_ready',                    10 );
add_action( 'bp_setup_current_user',    'buddytask_setup_current_user',       10 );
add_action( 'bp_setup_theme',           'buddytask_setup_theme',              10 );
add_action( 'bp_after_setup_theme',     'buddytask_after_setup_theme',        10 );
add_action( 'bp_enqueue_scripts',       'buddytask_register_scripts',          1 );
add_action( 'bp_admin_enqueue_scripts', 'buddytask_register_scripts',          1 );
add_action( 'bp_enqueue_scripts',       'buddytask_enqueue_scripts',          10 );
add_action( 'bp_setup_admin_bar',       'buddytask_setup_admin_bar',          10 );
add_action( 'bp_actions',               'buddytask_actions',                  10 );
add_action( 'bp_screens',               'buddytask_screens',                  10 );
add_action( 'admin_init',               'buddytask_admin_init',               10 );
add_action( 'admin_head',               'buddytask_admin_head',               10 );

function  buddytask_init(){
	do_action( 'buddytask_init' );
}

function  buddytask_ready(){
	do_action( 'buddytask_ready' );
}

function  buddytask_setup_current_user(){
	do_action( 'buddytask_setup_current_user' );
}

function  buddytask_setup_theme(){
	do_action( 'buddytask_setup_theme' );
}

function  buddytask_after_setup_theme(){
	do_action( 'buddytask_after_setup_theme' );
}

function  buddytask_register_scripts() {
	do_action( 'buddytask_register_scripts' );
}

function  buddytask_enqueue_scripts(){
	do_action( 'buddytask_enqueue_scripts' );
}

function  buddytask_setup_admin_bar(){
	do_action( 'buddytask_setup_admin_bar' );
}

function  buddytask_actions(){
	do_action( 'buddytask_actions' );
}

function  buddytask_screens(){
	do_action( 'buddytask_screens' );
}

function  buddytask_admin_init() {
	do_action( 'buddytask_admin_init' );
}

function  buddytask_admin_head() {
	do_action( 'buddytask_admin_head' );
}

// Activation redirect
add_action( 'buddytask_activation', 'buddytask_add_activation_redirect' );
