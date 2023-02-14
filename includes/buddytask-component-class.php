<?php
/**
 *  BuddyTask Component
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Main  BuddyTask Component Class
 *
 * Inspired by BuddyPress skeleton component
 */
class  BuddyTask_Component extends BP_Component {
	/**
	 * Constructor method
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::start(
			 buddytask_get_slug(),
			 buddytask_get_name(),
			 buddytask_get_includes_dir()
		);

	 	$this->includes();
	 	$this->actions();
	}

	/**
	 * set some actions
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	private function actions() {
		buddypress()->active_components[$this->id] = '1';
	}

	/**
	 *  buddytask needed files
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 *
	 * @uses bp_is_active() to check if group component is active
	 */
	public function includes( $includes = array() ) {
		// Files to include
		$includes = array();

		if ( bp_is_active( 'groups' ) ) {
			$includes[] = 'buddytask-group-class.php';
		}

		parent::includes( $includes );
	}

	/**
	 * Set up  buddytask globals
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 *
	 * @global obj $bp BuddyPress's global object
	 * @uses buddypress() to get the instance data
	 * @uses  buddytask_get_slug() to get  buddytask root slug
	 */
	public function setup_globals( $args = array() ) {
		$bp = buddypress();

		// Set up the $globals array to be passed along to parent::setup_globals()
		$globals = array(
			'slug'      =>  buddytask_get_slug(),
			'root_slug' => isset( $bp->pages->{$this->id}->slug ) ? $bp->pages->{$this->id}->slug :  buddytask_get_slug(),
            'notification_callback' => array($this, 'format_notifications')
		);

		parent::setup_globals( $globals );
	}

    public function setup_actions() {
        parent::setup_actions();
    }

    public function format_notifications($action, $item_id, $secondary_item_id){
        $notification_id = bp_get_the_notification_id();
        bp_notifications_mark_notification( $notification_id, false );

        $parent = bp_notifications_get_meta($notification_id, 'parent_type', true);
        if ($parent === 'post') {
            $post = get_post($secondary_item_id);
            $name = $post->post_title;
            $link = get_permalink($secondary_item_id);
        } else {
            $group = groups_get_group($secondary_item_id);
            $name = $group->name;
            $link = bp_get_group_permalink($group) . buddytask_get_slug();
        }
        $title = bp_notifications_get_meta($notification_id, 'task_title', true);

        if (!isset($name) || !isset($link)) {
            return null;
        }

        $text = null;
        switch ( $action ) {
            case 'add_task_member':
                $text = sprintf( __( '%s: You have been assigned to task <b>%s</b>', 'buddytask' ), $name, $title );
                break;
            case 'delete_task_member':
                $text = sprintf( __( '%s: You have been removed from task <b>%s</b>', 'buddytask' ), $name, $title );
                break;
            case 'edit_task':
                $text = sprintf( __( '%s: Task <b>%s</b> has been updated', 'buddytask' ), $name, $title );
                break;
            case 'delete_task':
                $text = sprintf( __( '%s: Task <b>%s</b> has been deleted from the task board', 'buddytask' ), $name, $title );
                break;
        }
        return isset($text) ? '<a href="' . esc_url($link) . '">' . wp_kses_post($text) . '</a>' : null;
    }
}

/**
 * Loads the component into the $bp global
 *
 * @uses buddypress()
 */
function  buddytask_load_component() {
	buddypress()->buddytask = new  BuddyTask_Component;
}
add_action( 'bp_loaded', 'buddytask_load_component' );
