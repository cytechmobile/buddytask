<?php

/**
 * BuddyTask lists DAO
 */
class BuddyTaskOwnersDAO {
	public function __construct() {
        require_once(buddytask_get_includes_dir() . 'model/buddytask-owner.php');
	}

	/**
	 * Creates or updates the owner and returns it.
	 *
	 * @param BuddyTaskOwner $owner
	 *
	 * @return BuddyTaskOwner
	 * @throws Exception On validation error
	 */
	public function save($owner) {
		global $wpdb;

		// low-level validation:
		if ($owner->getTaskId() === null) {
			throw new Exception('The task_id cannot equal null');
		}

        if ($owner->getUserId() === null) {
            throw new Exception('The user_id cannot equal null');
        }

		//prepare list data:
		$table = BuddyTaskInstaller::getTasksOwnersTable();

		// update or insert:
		if ($owner->getId() !== null) {
            $columns = array(
                'display_name' => $owner->getDisplayName(),
                'avatar_url' => $owner->getAvatarUrl(),
                'assigned_at' => $owner->getAssignedAt()
            );

			$wpdb->update($table, $columns, array('id' => $owner->getId()), '%s', '%d');
		} else {
            $columns = array(
                'task_id' => $owner->getTaskId(),
                'user_id' => $owner->getUserId(),
                'display_name' => $owner->getDisplayName(),
                'username' => $owner->getUsername(),
                'avatar_url' => $owner->getAvatarUrl(),
                'assigned_at' => $owner->getAssignedAt()
            );

			$wpdb->insert($table, $columns);
			$owner->setId($wpdb->insert_id);
		}

		return $owner;
	}

	/**
	 * Returns owner by ID.
	 *
	 * @param integer $id
	 *
	 * @return BuddyTaskOwner|null
	 */
	public function get($id) {
		global $wpdb;

		$table = BuddyTaskInstaller::getTasksOwnersTable();
        $sql = $wpdb->prepare("SELECT * FROM ".$table." WHERE id = %d", intval($id));
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateOwnerData($results[0]);
		}

		return null;
	}

    /**
     * Returns owners by task_id.
     *
     * @param string $task_id
     *
     * @return BuddyTaskOwner[]
     */
    public function getByTaskId($task_id) {
        global $wpdb;

        $owners = array();
        $table = BuddyTaskInstaller::getTasksOwnersTable();
        $sql = $wpdb->prepare("SELECT * FROM ".$table." WHERE task_id = %d order by id asc", $task_id);
        $results = $wpdb->get_results($sql);
        if (is_array($results)) {
            foreach ($results as $result) {
                $owners[] = $this->populateOwnerData($result);
            }
        }

        return $owners;
    }

    /**
     * Deletes the owner by task_id.
     *
     * @param integer $task_id
     *
     * @return void
     */
    public function deleteByTaskId($task_id) {
        global $wpdb;

        $task_id = intval($task_id);
        $table = BuddyTaskInstaller::getTasksOwnersTable();
        $sql = $wpdb->prepare("DELETE FROM ".$table." WHERE task_id = %d", $task_id);
        $wpdb->get_results($sql);
    }

	/**
	 * Converts raw object into BuddyTaskOwner object.
	 *
	 * @param stdClass $rawOwnerData
	 *
	 * @return BuddyTaskOwner
	 */
	private function populateOwnerData($rawOwnerData) {
		$owner = new BuddyTaskOwner();
		if ($rawOwnerData->id > 0) {
			$owner->setId(intval($rawOwnerData->id));
		}
		$owner->setTaskId($rawOwnerData->task_id);
		$owner->setUserId($rawOwnerData->user_id);
		$owner->setAssignedAt($rawOwnerData->assigned_at);
		$owner->setAvatarUrl($rawOwnerData->avatar_url);
		$owner->setDisplayName($rawOwnerData->display_name);
		$owner->setUsername($rawOwnerData->username);

		return $owner;
	}

    /**
     * Returns ids of the task of an owner.
     *
     * @param string $user_name
     *
     * @return array
     */
    public function getTasksByUser($user_name) {
        global $wpdb;
        $tasks = [];
        $table = BuddyTaskInstaller::getTasksOwnersTable();
        $sql = $wpdb->prepare("SELECT * FROM ".$table." WHERE username = %s", $user_name);
        $results = $wpdb->get_results($sql);
        if (is_array($results) && count($results) > 0) {
            foreach ($results as $result) {
                $tasks[] = $this->populateOwnerData($result);
            }
        }

        return $tasks;
    }
}