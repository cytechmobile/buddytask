<?php

/**
 * BuddyTask installer.
 */
class BuddyTaskInstaller {

	public static function getBoardsTable() {
		global $wpdb;

		return $wpdb->prefix.'buddytask_boards';
	}

	public static function getListsTable() {
		global $wpdb;
		
		return $wpdb->prefix.'buddytask_lists';
	}
	
	public static function getTasksTable() {
		global $wpdb;
		
		return $wpdb->prefix.'buddytask_tasks';
	}

    public static function getTasksOwnersTable() {
        global $wpdb;

        return $wpdb->prefix.'buddytask_tasks_owners';
    }

	/**
	 * Plugin's activation action. Creates database structure (if does not exist), upgrades database structure and
	 * initializes options. Supports WordPress multisite.
	 *
	 * @param boolean $networkWide True if it is a network activation - if so, run the activation function for each blog id
	 */
	public static function activate($networkWide) {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			if ($networkWide) {
				$oldBlogID = $wpdb->blogid;
				$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blogIDs as $blogID) {
					switch_to_blog($blogID);
					self::doActivation();
				}
				switch_to_blog($oldBlogID);
				return;
			}
		}
		self::doActivation();

        update_option('_buddytask_enabled', true);

        do_action( 'buddytask_activation' );
	}

	/**
	 * Executed when admin creates a site in mutisite installation.
	 *
	 * @param integer $blogID
	 * @param integer $userID
	 * @param string $domain
	 * @param string $path
	 * @param string $siteID
	 * @param mixed $meta
	 */
	public static function newBlog($blogID, $userID, $domain, $path, $siteID, $meta) {
		global $wpdb;

		if (is_plugin_active_for_network('buddytask/buddytask.php')) {
			$oldBlogID = $wpdb->blogid;
			switch_to_blog($blogID);
			self::doActivation();
			switch_to_blog($oldBlogID);
		}
	}

	/**
	 * Executed when admin deletes a site in mutisite installation.
	 *
	 * @param int $blogID Blog ID
	 * @param bool $drop True if blog's table should be dropped. Default is false.
	 */
	public static function deleteBlog($blogID, $drop) {
		global $wpdb;

		$oldBlogID = $wpdb->blogid;
		switch_to_blog($blogID);
		self::doUninstall('deleteblog_'.$blogID);
		switch_to_blog($oldBlogID);
	}

	private static function doActivation() {
		global $wpdb, $user_level, $sac_admin_user_level;
		
		if ($user_level < $sac_admin_user_level) {
			return;
		}

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$tableName = self::getBoardsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				uuid varchar(36) NOT NULL,
				name text NOT NULL,
				group_id bigint(11),
				post_id bigint(11),
				created_by bigint(11) NOT NULL,
				created_at bigint(11) DEFAULT '0' NOT NULL
		) DEFAULT CHARSET=utf8;";
		dbDelta($sql);
		
		$tableName = self::getListsTable();
		$sql = "CREATE TABLE ".$tableName." (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				board_id bigint(11),
				uuid varchar(36) NOT NULL,
				name text NOT NULL 
		) DEFAULT CHARSET=utf8;";
		dbDelta($sql);

		$tableName = self::getTasksTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				list_id bigint(11),
				parent_id bigint(11),
				position bigint(11),
				uuid varchar(36) NOT NULL,
				title text,
				description text,
				created_at bigint(11),
				created_by bigint(11),
				due_to bigint(11),
				done BOOLEAN,
				done_at bigint(11),
				done_by bigint(11),
				done_percent int DEFAULT 0
		) DEFAULT CHARSET=utf8;";
		dbDelta($sql);

		$tableName = self::getTasksOwnersTable();
		$sql = "CREATE TABLE " . $tableName . " (
				id bigint(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				task_id bigint(11),
				user_id bigint(11),
				username text,
				display_name text,
				avatar_url text,
				assigned_at bigint(11) NOT NULL
		) DEFAULT CHARSET=utf8;";
		dbDelta($sql);
	}

	/**
	 * Plugin's deactivation action.
	 */
	public static function deactivate() {
		global $wpdb, $user_level, $sac_admin_user_level;
		
		if ($user_level < $sac_admin_user_level) {
			return;
		}

        update_option('_buddytask_enabled', false);

        do_action( 'buddytask_deactivation' );
	}

	/**
	 * Plugin's uninstall action. Deletes all database tables and plugin's options.
	 * Supports WordPress multisite.
	 */
	public static function uninstall() {
		global $wpdb;

		if (function_exists('is_multisite') && is_multisite()) {
			$oldBlogID = $wpdb->blogid;
			$blogIDs = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
			foreach ($blogIDs as $blogID) {
				switch_to_blog($blogID);
				self::doUninstall();
			}
			switch_to_blog($oldBlogID);
			return;
		}
		self::doUninstall();

        delete_option('_buddytask_enabled');

        do_action( 'buddytask_uninstall' );
	}

	private static function doUninstall($refererCheck = null) {
		if (!current_user_can('activate_plugins')) {
			return;
		}
		if ($refererCheck !== null) {
			check_admin_referer($refererCheck);
		}
        
        global $wpdb;
		
		$tableName = self::getBoardsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		$tableName = self::getListsTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);

		$tableName = self::getTasksTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
		
		$tableName = self::getTasksOwnersTable();
		$sql = "DROP TABLE IF EXISTS {$tableName};";
		$wpdb->query($sql);
	}
}