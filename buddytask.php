<?php
/**
Plugin Name:  BuddyTask
Plugin URI:
Description: Adds KanBan like task management boards to Posts, Pages and BuddyPress Groups!
Version: 1.0.0
Requires at least: 4.6.0
Tags: BuddyTask, task management, task list, kanban, kan ban, buddypress
License: GPL V3
Author: Cytech <wp@cytech.gr>
Author URI: https://www.cytechmobile.com
Text Domain:  buddytask
Domain Path: /languages
*/

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'buddytask' ) ) :
/**
 * Main  buddytask Class
 */
class  BuddyTask {

	private static $instance;

	/**
	 * Required BuddyPress version for the plugin.
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 *
	 * @var  string
	 */
	public static $required_bp_version = '2.5.0';

	/**
	 * BuddyPress config.
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public static $bp_config = array();

	/**
	 * Main  buddytask Instance
	 *
	 * Avoids the use of a global
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 *
	 * @uses  buddytask::setup_globals() to set the global needed
	 * @uses  buddytask::includes() to include the required files
	 * @uses  buddytask::setup_actions() to set up the hooks
	 * @return object the instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new BuddyTask;
			self::$instance->setup_globals();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}

	private function __construct() { /* Do nothing here */ }

	/**
	 * Some usefull vars
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 *
	 * @uses plugin_basename()
	 * @uses plugin_dir_path() to build  buddytask plugin path
	 * @uses plugin_dir_url() to build  buddytask plugin url
	 */
	private function setup_globals() {
		$this->version    = '1.0.0';

		// Setup some base path and URL information
		$this->file       = __FILE__;
		$this->basename   = apply_filters( 'buddytask_plugin_basename', plugin_basename( $this->file ) );
		$this->plugin_dir = apply_filters( 'buddytask_plugin_dir_path', plugin_dir_path( $this->file ) );
		$this->plugin_url = apply_filters( 'buddytask_plugin_dir_url',  plugin_dir_url ( $this->file ) );

		// Includes
		$this->includes_dir = apply_filters( 'buddytask_includes_dir', trailingslashit( $this->plugin_dir . 'includes'  ) );
		$this->includes_url = apply_filters( 'buddytask_includes_url', trailingslashit( $this->plugin_url . 'includes'  ) );

		// Languages
		$this->lang_dir  = apply_filters( 'buddytask_lang_dir', trailingslashit( $this->plugin_dir . 'languages' ) );

		//  buddytask slug and name
		$this->buddytask_slug = apply_filters( 'buddytask_slug', 'buddytask' );
		$this->buddytask_name = apply_filters( 'buddytask_name', esc_html__('BuddyTask', 'buddytask') );

		$this->domain           = 'buddytask';
		$this->errors           = new WP_Error(); // Feedback
	}

	/**
	 * Ιncludes the needed files
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	private function includes() {
        require( $this->includes_dir . 'buddytask-functions.php');
        require( $this->includes_dir . 'buddytask-actions.php'  );
        require( $this->includes_dir . 'buddytask-installer.php');
	}

	/**
	 * The main hook used is bp_include to load our custom BuddyPress component
     *
     * @package  buddytask
	 * @since 1.0.0
	 */
	private function setup_actions() {
        //add_action( 'wp_head', array( $this, 'buddytask_core_add_ajax_url_js') );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        add_action('wpmu_new_blog', array('BuddyTaskInstaller', 'newBlog'), 10, 6);
        add_action('delete_blog', array('BuddyTaskInstaller', 'deleteBlog'), 10, 6);

		add_action( 'bp_loaded',  array( $this, 'load_textdomain' ) );
		add_action( 'bp_include', array( $this, 'load_component'  ) );

        add_shortcode( 'buddytask', array($this, 'buddytask_shortcode'));

        add_action( 'wp_ajax_get_board', array($this,'get_board') );
        add_action( 'wp_ajax_nopriv_get_board', array($this,'get_board') );
        add_action( 'wp_ajax_add_new_task', array($this,'add_new_task') );
        add_action( 'wp_ajax_nopriv_add_new_task', array($this,'add_new_task') );

        //assign task to user
        add_action( 'wp_ajax_users_autocomplete', array($this,'users_autocomplete') );
        add_action( 'wp_ajax_add_users_to_assign_list', array($this,'add_users_to_assign_list') );
        add_action( 'wp_ajax_edit_task', array($this, 'edit_task') );
        add_action( 'wp_ajax_nopriv_edit_task', array($this, 'edit_task') );
        add_action( 'wp_ajax_delete_task', array($this, 'delete_task') );
        add_action( 'wp_ajax_nopriv_delete_task', array($this, 'delete_task') );
        add_action( 'wp_ajax_reorder_task', array($this,'reorder_task') );
        add_action( 'wp_ajax_nopriv_reorder_task', array($this,'reorder_task') );
        add_action( 'wp_ajax_get_tasks', array($this,'get_tasks') );
        add_action( 'wp_ajax_nopriv_get_tasks', array($this,'get_tasks') );
        add_action( 'wp_ajax_edit_list', array($this,'edit_list') );
        add_action( 'wp_ajax_nopriv_edit_list', array($this,'edit_list') );

        // Add filter to receive hook, and specify we need 2 parameters.
        add_filter( 'heartbeat_received', array($this, 'receive_heartbeat'), 10, 2 );
        add_filter( 'wp_refresh_nonces', array($this, 'buddytask_refresh_heartbeat_nonces') );

		do_action_ref_array( 'buddytask_after_setup_actions', array( &$this ) );
	}

    public function buddytask_refresh_heartbeat_nonces( $response ) {
        // Refresh the Rest API nonce.
        $response['rest_nonce'] = wp_create_nonce( 'wp_rest' );

        // Refresh the Heartbeat nonce.
        $response['heartbeat_nonce'] = wp_create_nonce( 'heartbeat-nonce' );

        return $response;
    }

    /**
     * Registers the  buddytask shortcode
     * @param $params
     */
    public function buddytask_shortcode($params) {
        $nonce_get_board =  wp_nonce_field( "buddytask_get_board", "_wpnonce_get_board", false, false );
        $nonce__add_new_task = wp_nonce_field( "buddytask_add_new_task", "_wpnonce_add_new_task", false, false );
        $nonce_delete_task = wp_nonce_field( "buddytask_delete_task", "_wpnonce_delete_task", false, false );
        $nonce_reorder_task = wp_nonce_field( "buddytask_reorder_task", "_wpnonce_reorder_task", false, false );
        $nonce_edit_list = wp_nonce_field( "buddytask_edit_list", "_wpnonce_edit_list", false, false );
        $nonce_users_autocomplete = wp_nonce_field( 'buddytask_users_autocomplete', '_wpnonce_users_autocomplete', false, false);
        $nonce_add_users_to_assign_list = wp_nonce_field( 'buddytask_add_users_to_assign_list', '_wpnonce_add_users_to_assign_list', false, false);
        $nonce_assign_users_to_task = wp_nonce_field( 'buddytask_assign_users_to_task', '_wpnonce_assign_users_to_task', false, false);
        $nonce_edit_task = wp_nonce_field( 'buddytask_edit_task', '_wpnonce_edit_task', false, false );
        $nonce_delete_task = wp_nonce_field( 'buddytask_delete_task', '_wpnonce_delete_task', false, false );
        $nonce_get_tasks = wp_nonce_field( 'buddytask_get_tasks', '_wpnonce_get_tasks', false, false );

        $nonce = array($nonce_get_board, $nonce__add_new_task, $nonce_delete_task, $nonce_reorder_task, $nonce_edit_list,
            $nonce_users_autocomplete, $nonce_add_users_to_assign_list, $nonce_assign_users_to_task, $nonce_edit_task,
            $nonce_delete_task, $nonce_get_tasks);

        return '<div class="task-board"></div><div class="task-dialog"></div>'. implode("", $nonce);
    }

	/**
	 * Loads the translation
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 * @uses get_locale()
	 * @uses load_textdomain()
	 */
	public function load_textdomain() {
		$locale = apply_filters( 'buddytask_load_textdomain_get_locale', get_locale(), $this->domain );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );
		$mofile_global = WP_LANG_DIR . '/buddytask/' . $mofile;

		if ( ! load_textdomain( $this->domain, $mofile_global ) ) {
			load_plugin_textdomain( $this->domain, false, basename( $this->plugin_dir ) . '/languages' );
		}
	}

	/**
	 * Finally, Load the component
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	public function load_component() {
		if ( self::bail() ) {
			add_action( self::$bp_config['network_admin'] ? 'network_admin_notices' : 'admin_notices', array( $this, 'warning' ) );
		} else {
			require( $this->includes_dir . 'buddytask-component-class.php' );
		}
	}

	/**
	 * Checks BuddyPress version
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	public static function version_check() {
		// taking no risk
		if ( ! defined( 'BP_VERSION' ) )
			return false;

		return version_compare( BP_VERSION, self::$required_bp_version, '>=' );
	}

    public function enqueue_styles(){
        $load_scripts = $this->load_scripts();
        if($load_scripts){
            wp_enqueue_style(array('wp-jquery-ui', 'wp-jquery-ui-core', 'wp-jquery-ui-dialog', 'jquery-ui-autocomplete'));
            wp_enqueue_style( 'buddytask-css', buddytask_get_plugin_url() . 'assets/css/buddytask.css', '', buddytask_get_version(),'screen' );
        }
    }

    public function enqueue_scripts(){
        $load_scripts = $this->load_scripts();
        if($load_scripts){
            global $bp;
            $group_id = function_exists('bp_get_current_group_id') ? bp_get_current_group_id() : 0;
            $group = $group_id > 0 ? groups_get_group( $group_id ) : null;
            $url     = wp_get_referer();
            $post_id = url_to_postid( $url );
            $user_id = get_current_user_id();

            wp_enqueue_script('hooks-js', get_home_url() . '/wp-includes/js/dist/hooks.js', array('jquery'), buddytask_get_version());
            wp_enqueue_script('heartbeat-js', get_home_url() . '/wp-includes/js/heartbeat.js', array('jquery'), buddytask_get_version());
            wp_add_inline_script( 'heartbeat-js', "var ajaxurl = '". admin_url( 'admin-ajax.php' ) ."';", true);

            // Load some bundled WP resources
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('jquery-ui-dialog');
            wp_enqueue_script('jquery-dropdown');
            wp_enqueue_script('jquery-effects-core');
            wp_enqueue_editor();

            wp_enqueue_script( 'buddytask-js', buddytask_get_plugin_url() . "assets/js/buddytask.js", array( 'jquery' ), buddytask_get_version() );
            wp_localize_script('buddytask-js', 'btargs', array(
                'lang' => array(
                    'due_date' => esc_html__('Due Date', 'buddytask'),
                    'assign_to' => esc_html__('Assign To', 'buddytask'),
                    'delete' => esc_html__('Delete', 'buddytask'),
                    'edit' => esc_html__('Edit', 'buddytask'),
                    'view' => esc_html__('View', 'buddytask'),
                    'cancel' => esc_html__('Cancel', 'buddytask'),
                    'submit' => esc_html__('Submit', 'buddytask'),
                    'edit_task' => esc_html__('Edit Task', 'buddytask'),
                    'who_can_view' =>  esc_html__('Who can view this task',  'buddytask'),
                    'title' => esc_html__('Title', 'buddytask'),
                    'description' => esc_html__('Description', 'buddytask'),
                    'tasks' => esc_html__('Tasks', 'buddytask'),
                    'add_task_press_enter' => esc_html__('Write the task and press enter ...', 'buddytask'),
                    'delete_task' => esc_html__('Delete the task?', 'buddytask'),
                    'delete_warning' => esc_html__('The task will be permanently deleted and cannot be recovered. Are you sure?', 'buddytask'),
                ),
                'ajaxurl' =>  admin_url('admin-ajax.php'),
                'heartbeat' => array(
                    'interval' => 10,
                    'nonce' => wp_create_nonce('buddytask_heartbeat')
                ),
                'user_id' => $user_id,
                'post_id' => $post_id,
                'group_id' => $group_id,
                'user_profile_path' => function_exists('buddypress') ? 'members' : 'author'
            ));

            wp_enqueue_script( 'jquery-ui-datepicker' );
        }
    }

    public function load_scripts(){
        $load_scripts = false;
        if(is_page() || is_single() || is_singular()){
            $post = get_post();
            if($post && has_shortcode($post->post_content, buddytask_get_slug())){
                $load_scripts = true;
            } else if( function_exists( 'buddypress' )){
                global $bp;
                if(buddytask_get_slug() === $bp->current_action){
                    $load_scripts = true;
                }
            }
        }

        return apply_filters( 'buddytask_enqueue_scripts_load_scripts', $load_scripts );
    }

	/**
	 * Checks if your plugin's config is similar to BuddyPress
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	public static function config_check() {
		/**
		 * blog_status    : true if your plugin is activated on the same blog
		 * network_active : true when your plugin is activated on the network
		 * network_status : BuddyPress & your plugin share the same network status
		 */
		self::$bp_config = array(
			'blog_status'    => false,
			'network_active' => false,
			'network_status' => true,
			'network_admin'  => false
		);

		$buddypress = false;

		if ( function_exists( 'buddypress' ) ) {
			$buddypress = buddypress()->basename;
		}

		if ( $buddypress && get_current_blog_id() == bp_get_root_blog_id() ) {
			self::$bp_config['blog_status'] = true;
		}

		$network_plugins = get_site_option( 'active_sitewide_plugins', array() );

		// No Network plugins
		if ( empty( $network_plugins ) )
			return self::$bp_config;

		$buddytask = plugin_basename( __FILE__ );

		// Looking for  buddytask
		$check = array( $buddytask );

		// And for BuddyPress if set
		if ( ! empty( $buddypress ) )
			$check = array_merge( array( $buddypress ), $check );

		// Are they active on the network ?
		$network_active = array_diff( $check, array_keys( $network_plugins ) );

		// If result is 1, your plugin is network activated
		// and not BuddyPress or vice & versa. Config is not ok
		if ( count( $network_active ) == 1 )
			self::$bp_config['network_status'] = false;

		self::$bp_config['network_active'] = isset( $network_plugins[ $buddytask ] );

		// We need to know if the BuddyPress is network activated to choose the right
		// notice ( admin or network_admin ) to display the warning message.
		self::$bp_config['network_admin']  = ! empty( $buddypress ) && isset( $network_plugins[ $buddypress ] );

		return self::$bp_config;
	}

	/**
	 * Bail if BuddyPress config is different than this plugin
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	public static function bail() {
		$retval = false;

		$config = self::config_check();

		if ( ! self::version_check() || ! $config['blog_status'] || ! $config['network_status'] )
			$retval = true;

		return $retval;
	}

	/**
	 * Display a warning message to admin
	 *
	 * @package  buddytask
	 * @since 1.0.0
	 */
	public function warning() {
		$warnings = $resolve = array();

		if ( ! self::version_check() ) {
			$warnings[] = sprintf( esc_html__( 'BuddyTask requires at least version %s of BuddyPress.', 'buddytask' ), self::$required_bp_version );
			$resolve[]  = sprintf( esc_html__( 'Upgrade BuddyPress to at least version %s', 'buddytask' ), self::$required_bp_version );
		}

		if ( ! empty( self::$bp_config ) ) {
			$config = self::$bp_config;
		} else {
			$config = self::config_check();
		}

		if ( ! $config['blog_status'] ) {
			$warnings[] = esc_html__( 'BuddyTask requires to be activated on the blog where BuddyPress is activated.', 'buddytask' );
			$resolve[]  = esc_html__( 'Activate  buddytask on the same blog than BuddyPress', 'buddytask' );
		}

		if ( ! $config['network_status'] ) {
			$warnings[] = esc_html__( 'BuddyTask and BuddyPress need to share the same network configuration.', 'buddytask' );
			$resolve[]  = esc_html__( 'Make sure BuddyTask is activated at the same level than BuddyPress on the network', 'buddytask' );
		}

		if ( ! empty( $warnings ) ) {
			// Give some more explanations to administrator
			if ( is_super_admin() ) {
				$deactivate_link = ! empty( $config['network_active'] ) ? network_admin_url( 'plugins.php' ) : admin_url( 'plugins.php' );
				$deactivate_link = '<a href="' . esc_url( $deactivate_link ) . '">' . esc_html__( 'deactivate', 'buddytask' ) . '</a>';
				$resolve_message = '<ol><li>' . sprintf( esc_html__( 'You should %s  buddytask', 'buddytask' ), $deactivate_link ) . '</li>';

				foreach ( (array) $resolve as $step ) {
					$resolve_message .= '<li>' . $step . '</li>';
				}

				if ( $config['network_status'] && $config['blog_status']  )
					$resolve_message .= '<li>' . esc_html__( 'Once done try to activate  buddytask again.', 'buddytask' ) . '</li></ol>';

				$warnings[] = $resolve_message;
			}

		?>
		<div id="message" class="error">
			<?php foreach ( $warnings as $warning ) : ?>
				<p><?php echo wp_kses_post($warning); ?></p>
			<?php endforeach ; ?>
		</div>
		<?php
		}
	}

    public function get_board(){
        check_ajax_referer( 'buddytask_get_board' );

        $board = $this->get_or_create_board();
        die(json_encode(get_object_vars($board)));
    }

    public function get_or_create_board($uuid = null){
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-boards-dao.php'  );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-lists-dao.php'  );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php'  );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-owners-dao.php'  );

        $group_id = function_exists('bp_get_current_group_id') ? bp_get_current_group_id() : 0;
        $url     = wp_get_referer();
        $post_id = url_to_postid( $url );

        $boardsDao = new BuddyTaskBoardsDAO();
        if($uuid === null){
            $boards = $group_id > 0 ? $boardsDao->getByGroupId($group_id) : $boardsDao->getByPostId($post_id);
        } else {
            $boards = array($boardsDao->getByUuid($uuid));
        }

        $is_new_board = false;
        if(empty($boards)){
            $is_new_board = true;

            $board = new BuddyTaskBoard();
            $board->setUuid(wp_generate_uuid4());
            $board->setName(sprintf(esc_html__('Board %s'), time()));
            if ($group_id > 0) {
                $board->setGroupId($group_id);
            } else {
                $board->setPostId($post_id);
            }
            $board->setCreatedAt(time());
            $board->setCreatedBy(get_current_user_id());
            try {
                $board = $boardsDao->save($board);
            } catch (Exception $e) {
                $board = null;
            }
        } else {
            $board = $boards[0];
        }

        if($board !== null){
            $listsDao = new BuddyTaskListsDAO();
            if($is_new_board){
                $names = buddytask_default_settings()['lists'];

                foreach($names as $name){
                    $list = new BuddyTaskList();
                    $list->setBoardId($board->getId());
                    $list->setUuid(wp_generate_uuid4());
                    $list->setName($name);
                    $listsDao->save($list);
                }
            }
            $lists = $listsDao->getByBoardId($board->getId());
            if($is_new_board) {
                $board->setLists($lists);
            } else {
                //lets look for tasks
                $tasksDao = new BuddyTaskTasksDAO();
                $ownersDao = new BuddyTaskOwnersDAO();
                foreach ($lists as &$list) {
                    $tasks = $tasksDao->getByListId($list->getId());
                    foreach ($tasks as &$task) {
                        $owners = $ownersDao->getByTaskId($task->getId());
                        $task->setOwners($owners);
                    }
                    $list->setTasks($tasks);
                    $board->addList($list);
                }
            }
        }
        return $board;
    }

    public function add_new_task(){
        check_ajax_referer( 'buddytask_add_new_task' );

        $list_uuid = isset($_REQUEST['list_id']) && wp_is_uuid($_REQUEST['list_id']) ? sanitize_text_field($_REQUEST['list_id']) : false;
        if(!$list_uuid){
            return;
        }

        $parent_uuid = isset($_REQUEST['parent_id']) && wp_is_uuid($_REQUEST['parent_id'])? sanitize_text_field($_REQUEST['parent_id']) : false;
        $position = isset($_REQUEST['position']) && is_numeric($_REQUEST['position'])? intval($_REQUEST['position']) : 0;

        $group_id = function_exists('bp_get_current_group_id') ? bp_get_current_group_id() : 0;
        $url     = wp_get_referer();
        $post_id = url_to_postid( $url );

        require_once( buddytask_get_includes_dir() . 'dao/buddytask-lists-dao.php'  );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php'  );

        $listsDao = new BuddyTaskListsDAO();
        $list = $listsDao->getByUuid($list_uuid);
        if($list !== null){
            $tasksDao = new BuddyTaskTasksDAO();

            $task = new BuddyTaskTask();
            $task->setUuid(wp_generate_uuid4());
            $task->setCreatedBy(get_current_user_id());
            $task->setCreatedAt(time());
            $task->setTitle(esc_html__('Click to edit the task...', 'buddytask'));
            $task->setDescription(esc_html__('Click to edit the description...', 'buddytask'));
            $task->setListId($list->getId());
            $task->setDone(false);
            $task->setPosition($position);

            if($parent_uuid){
                $parent_task = $tasksDao->getByUuid($parent_uuid);
                $task->setParentId($parent_task->getId());
            }

            $tasksDao->save($task);
        }

        $board = $this->get_or_create_board();
        die(json_encode($board));
    }

    public function users_autocomplete() {
        check_ajax_referer('buddytask_users_autocomplete');

        $search_terms =  isset($_REQUEST['term']) ? sanitize_text_field($_REQUEST['term']) : null;
        $task_assign_to = isset($_REQUEST['task_assign_to']) && is_array($_REQUEST['task_assign_to']) ?
            array_map('absint', $_REQUEST['task_assign_to']) : array();

        $args = array();
        $exclude = array();
        if ($task_assign_to) {
            $exclude = array_unique(array_merge($exclude, $task_assign_to));
        }

        if (!empty($exclude)) {
            $args['exclude'] = $exclude;
        }

        global $bp;
        $group_id = isset($bp->groups->current_group) && is_object($bp->groups->current_group) ? $bp->groups->current_group->id : 0;
        $suggestions = array();
        if (isset($group_id) && !empty($group_id) && $group_id !== 0) {
            $args['group_id'] = $group_id;
            $args['group_role'] =  array('member', 'mod', 'admin');
            $args['fields'] =  'all';
            if ($search_terms) {
                $args['search_terms'] = $search_terms;
            }

            $members = groups_get_group_members($args);
            if(!empty($members)) {
                foreach ( $members['members'] as $user ) {
                    $suggestions[] 	= array(
                        'value' => $user->ID,
                        'label' => $user->display_name . ' (' . $user->user_login . ')'
                    );
                }
            }
        } else {
            if ($search_terms) {
                $args['search'] = '*'.$search_terms.'*';
                $args['search_columns'] = array('user_login', 'user_nicename', 'display_name');
            }
            $users = get_users($args);
            foreach ( $users as $user ) {
                $suggestions[] 	= array(
                    'value' => $user->ID,
                    'label' => $user->display_name . ' (' . $user->user_login . ')'
                );
            }
        }

        die(json_encode( $suggestions ));
    }

    public function add_users_to_assign_list() {
        check_ajax_referer('buddytask_add_users_to_assign_list');

        $user_id = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? absint($_POST['user_id']) : null;
        if (is_null($user_id)){
            return false;
        }

        $user = new WP_User($user_id);
        $avatar = function_exists('bp_core_fetch_avatar') ?
            bp_core_fetch_avatar(array( 'item_id' => $user->id )) :
            get_avatar($user->id);
        $userlink = function_exists('bp_core_get_userlink') ?
            bp_core_get_userlink($user->id, false, true) :
            get_author_posts_url($user->id);
        $username =  function_exists('bp_core_get_userlink') ?
            bp_core_get_userlink($user->id, true) :
            $user->display_name;

        echo sprintf($this->get_assign_list_entry_template(), esc_attr($user->id), wp_kses_post($avatar), esc_url($userlink), esc_attr($username));

        die();
    }

    public function get_assign_list_entry_template(){
        return '<div id="uid-%1$s" class="user-avatar" title="%4$s"><a href="%3$s">%2$s</a><i class="dashicons dashicons-no delete-assigned-user"></i></div>';
    }

    public function edit_task(){
        check_ajax_referer('buddytask_edit_task');

        $task_uuid = isset($_REQUEST['task_id']) && wp_is_uuid($_REQUEST['task_id']) ? sanitize_text_field($_REQUEST['task_id']) : null;
        $task_title = isset($_REQUEST['task_title']) ? wp_unslash(sanitize_text_field($_REQUEST['task_title'])) : null;
        $task_description = isset($_REQUEST['task_description']) ? wp_unslash(sanitize_text_field($_REQUEST['task_description'])) : null;
        $task_due_date = isset($_REQUEST['task_due_date']) && !empty($_REQUEST['task_due_date']) && is_numeric($_REQUEST['task_due_date']) ?
            floatval($_REQUEST['task_due_date']) : null;
        $task_assign_to = isset($_REQUEST['task_assign_to']) && is_array($_REQUEST['task_assign_to']) ?
            array_map('absint', $_REQUEST['task_assign_to']) : array();
        $task_todos =  isset($_REQUEST['task_todos']) ? json_decode(wp_unslash(sanitize_text_field($_REQUEST['task_todos']))) : null;

        $logged_in_user = get_current_user_id();

        require_once( buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php' );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-owners-dao.php' );

        $tasksDao = new BuddyTaskTasksDAO();
        $ownersDao = new BuddyTaskOwnersDAO();

        $task = $tasksDao->getByUuid($task_uuid);
        $task->setTitle($task_title);
        $task->setDescription($task_description);
        $task->setDueTo($task_due_date);

        $membersBeforeUpdate = $this->get_task_members($task_uuid);

        //override the existing owners
        $ownersDao->deleteByTaskId($task->getId());
        foreach ($task_assign_to as $user_id){
            $owner = new BuddyTaskOwner();
            $owner->setTaskId($task->getId());
            $owner->setUserId($user_id);
            $user = new WP_User($user_id);
            $displayName = function_exists('bp_core_get_user_displayname') ?
                bp_core_get_user_displayname($user_id) :
                $user->display_name;
            $avatar = function_exists('bp_core_fetch_avatar') ?
                bp_core_fetch_avatar(array('item_id' => $user_id, 'html' => false)):
                get_avatar_url($user_id);
            $username = function_exists('bp_core_get_username') ?
                bp_core_get_username($user_id):
                $user->user_login;
            $owner->setDisplayName($displayName);
            $owner->setAvatarUrl($avatar);
            $owner->setUsername($username);
            $owner->setAssignedAt(time());
            $ownersDao->save($owner);
        }

        $total = count($task_todos);
        $done = 0;
        $existing_task_todos = $tasksDao->getByParentId($task->id);
        foreach ($existing_task_todos as $index => $existing_todo){
            if (!in_array($existing_todo->uuid, array_column($task_todos, 'uuid'))){
                $tasksDao->deleteById($existing_todo->id);
            }
        }
        foreach ($task_todos as $index => $todo){
            if($todo->isNew){
                $sub_task = new BuddyTaskTask();
                $sub_task->setParentId($task->getId());
                $sub_task->setUuid(wp_generate_uuid4());
                $sub_task->setListId($task->getListId());
                $sub_task->setCreatedBy($logged_in_user);
                $sub_task->setCreatedAt(time());
            } else {
                $sub_task = $tasksDao->getByUuid($todo->uuid);
            }

            $sub_task->setTitle($todo->title);
            $sub_task->setPosition($index);
            $sub_task->setDone($todo->isDone);
            if($todo->isDone){
                $sub_task->setDoneAt(time());
                $sub_task->setDoneBy($logged_in_user);
                $sub_task->setDonePercent(100);
                $done += 1;
            } else {
                $sub_task->setDoneAt(null);
                $sub_task->setDoneBy(null);
                $sub_task->setDonePercent(0);
            }
            $tasksDao->save($sub_task);
        }
        $done_percent = !empty($total) ? round(($done * 100) / $total) : 0;
        $task->setDonePercent($done_percent);
        $tasksDao->save($task);

        $board = $this->get_or_create_board();
        $this->send_notifications_to_task_members_on_update('edit', $membersBeforeUpdate, $task);

        die(json_encode($board));
    }


    public function delete_task(){
        check_ajax_referer('buddytask_delete_task');

        $task_uuid = isset($_REQUEST['task_id']) && wp_is_uuid($_REQUEST['task_id']) ? sanitize_text_field($_REQUEST['task_id']) : null;
        if($task_uuid === null){
            return false;
        }

        require_once( buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php' );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-owners-dao.php' );

        $tasksDao = new BuddyTaskTasksDAO();
        $ownersDao = new BuddyTaskOwnersDAO();

        $task = $tasksDao->getByUuid($task_uuid);
        if (isset($task)) {
            $membersBeforeUpdate = $this->get_task_members($task_uuid);

            $tasksDao->deleteById($task->getId());
            $tasksDao->deleteByParentId($task->getId());
            $ownersDao->deleteByTaskId($task->getId());

            $this->send_notifications_to_task_members_on_update('delete', $membersBeforeUpdate, $task);
        }
        $board = $this->get_or_create_board();
        die(json_encode($board));
    }

    public function reorder_task(){
        check_ajax_referer('buddytask_reorder_task');

        $task_uuid = isset($_REQUEST['task_id']) && wp_is_uuid($_REQUEST['task_id']) ? sanitize_text_field($_REQUEST['task_id']) : null;
        $list_uuid = isset($_REQUEST['list_id']) && wp_is_uuid($_REQUEST['list_id']) ? sanitize_text_field($_REQUEST['list_id']) : null;
        $parent_uuid = isset($_REQUEST['parent_id']) && wp_is_uuid($_REQUEST['parent_id']) ? sanitize_text_field($_REQUEST['parent_id']) : null;
        $task_index = isset($_REQUEST['task_index']) && is_numeric($_REQUEST['task_index']) ? intval($_REQUEST['task_index']) : null;

        if(isset($task_uuid) && isset($list_uuid) && isset($task_index) && $task_index >= 0 ) {
            require_once(buddytask_get_includes_dir() . 'dao/buddytask-lists-dao.php');
            require_once(buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php');

            $listsDao = new BuddyTaskListsDAO();
            $list = $listsDao->getByUuid($list_uuid);
            $list_id = $list->getId();

            $tasksDao = new BuddyTaskTasksDAO();
            $parent_task_id = null;
            if ($parent_uuid !== null) {
                $parent_task = $tasksDao->getByUuid($parent_uuid);
                $parent_task_id = $parent_task->getId();
            }
            $tasksDao->reorderTask($list_id, $parent_task_id, $task_uuid, $task_index);
        }

        $board = $this->get_or_create_board();
        die(json_encode($board));
    }

    public function get_tasks(){
        check_ajax_referer('buddytask_get_tasks');

        $parent_uuid = isset($_REQUEST['parent_id']) && wp_is_uuid($_REQUEST['parent_id']) ? sanitize_text_field($_REQUEST['parent_id']) : null;
        $tasks = array();
        if (isset($parent_uuid)) {
            require_once(buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php');

            $tasksDao = new BuddyTaskTasksDAO();
            if ($parent_uuid !== null) {
                $parent_task = $tasksDao->getByUuid($parent_uuid);
                $parent_task_id = $parent_task->getId();
                $tasks = $tasksDao->getByParentId($parent_task_id);
            }
        }
        die(json_encode($tasks));
    }

    public function edit_list(){
        check_ajax_referer('buddytask_edit_list');

        $list_uuid = isset($_REQUEST['id']) && wp_is_uuid($_REQUEST['id'])? sanitize_text_field($_REQUEST['id']) : null;
        $list_name = isset($_REQUEST['name']) ? sanitize_text_field($_REQUEST['name']) : null;
        if(isset($list_uuid) && isset($list_name)) {
            require_once(buddytask_get_includes_dir() . 'dao/buddytask-lists-dao.php');

            $listsDao = new BuddyTaskListsDAO();
            $list = $listsDao->getByUuid($list_uuid);
            $list->setName($list_name);
            $listsDao->save($list);
        }
        die();
    }

    public function get_task_members($task_uuid){
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-tasks-dao.php' );
        require_once( buddytask_get_includes_dir() . 'dao/buddytask-owners-dao.php' );
        $tasksDao = new BuddyTaskTasksDAO();
        $ownersDao = new BuddyTaskOwnersDAO();
        $task = $tasksDao->getByUuid($task_uuid);

        if($task !== null){
            $owners = $ownersDao->getByTaskId($task->getId());
            return array_map(function($owner){ return intval($owner->getUserId()); }, $owners);
        }

        return array();
    }

    public function receive_heartbeat( $response, $data ) {
        if ( empty( $data['refresh_board'] ) ||  !$data['refresh_board']) {
            return $response;
        }

        $board = $this->get_or_create_board();

        $response['board'] = $board;
        return $response;
    }

    public function send_notifications_to_task_members_on_update($action, $membersBeforeUpdate, $task){
        // Bail out early if notifications are not enabled
        if (!function_exists( 'buddypress' ) || !bp_is_active( 'notifications' )) {
            return;
        }

        $url     = wp_get_referer();
        $post_id = url_to_postid( $url );
        if (isset($post_id) && $post_id > 0) {
            $parent_type = 'post';
            $secondary_item_id = $post_id;
        } else {
            global $bp;
            $secondary_item_id = $bp->groups->current_group->id;
            $parent_type = 'group';
        }

        $task_id = $task->getId();
        $membersAfterUpdate = $this->get_task_members($task->getUuid($task->getUuid()));
        $added_members = array_diff($membersAfterUpdate, $membersBeforeUpdate);
        $deleted_members = array_diff($membersBeforeUpdate, $membersAfterUpdate);
        $existing_members = array_diff($membersBeforeUpdate, $deleted_members);

        if ($action === 'edit') {
            $this->add_notifications('delete_task_member', $deleted_members, $task_id, $secondary_item_id, $task->title, $parent_type);
            $this->add_notifications('add_task_member', $added_members, $task_id, $secondary_item_id, $task->title, $parent_type);
            $this->add_notifications('edit_task', $existing_members, $task_id, $secondary_item_id, $task->title, $parent_type);
        } else if ($action === 'delete') {
            $this->add_notifications('delete_task', $membersBeforeUpdate, $task_id, $secondary_item_id, $task->title, $parent_type);
        }
    }

    public function add_notifications($action, $recipients, $task_id, $secondary_item_id, $title, $parent_type) {
        foreach ($recipients as $user_id) {
            if ($user_id != get_current_user_id()) {
                $notification_id = bp_notifications_add_notification(array(
                    'user_id' => $user_id,
                    'item_id' => $task_id,
                    'secondary_item_id' => $secondary_item_id,
                    'component_name' => buddytask_get_slug(),
                    'component_action' => $action,
                ));
                bp_notifications_add_meta($notification_id, 'task_title', $title);
                bp_notifications_add_meta($notification_id, 'parent_type', $parent_type);
            }
        }
    }

}

function  buddytask() {
	return  buddytask::instance();
}

buddytask();

//register the lifecycle hooks
register_activation_hook(__FILE__, array('BuddyTaskInstaller', 'activate'));
register_deactivation_hook(__FILE__, array('BuddyTaskInstaller', 'deactivate'));
register_uninstall_hook(__FILE__, array('BuddyTaskInstaller', 'uninstall'));

endif;

