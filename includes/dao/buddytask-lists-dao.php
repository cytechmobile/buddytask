<?php

/**
 * BuddyTask lists DAO
 */
class BuddyTaskListsDAO {
	public function __construct() {
        require_once(buddytask_get_includes_dir() . 'model/buddytask-list.php');
	}

	/**
	 * Creates or updates the list and returns it.
	 *
	 * @param BuddyTaskList $list
	 *
	 * @return BuddyTaskList
	 * @throws Exception On validation error
	 */
	public function save($list) {
		global $wpdb;

		// low-level validation:
		if ($list->getName() === null) {
			throw new Exception('Name of the list cannot equal null');
		}

		//prepare list data:
		$table = BuddyTaskInstaller::getListsTable();

		// update or insert:
		if ($list->getId() !== null) {
            $columns = array(
                'name' => $list->getName()
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update($table, $columns, array('id' => $list->getId()), '%s', '%d');
		} else {
            $columns = array(
                'board_id' => $list->getBoardId(),
                'name' => $list->getName(), 
                'uuid' => $list->getUuid(), 
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->insert($table, $columns);
			$list->setId($wpdb->insert_id);
		}

		return $list;
	}

	/**
	 * Returns list by ID.
	 *
	 * @param integer $id
	 *
	 * @return BuddyTaskList|null
	 */
	public function get($id) {
        global $wpdb;
        $table = BuddyTaskInstaller::getListsTable();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE id = %d", array($table, intval($id))));

        if (is_array($results) && count($results) > 0) {
			return $this->populateListData($results[0]);
		}

		return null;
	}

    /**
     * Returns list by uuid.
     *
     * @param string $uuid
     *
     * @return BuddyTaskList|null
     */
    public function getByUuid($uuid) {
        global $wpdb;

        $table = BuddyTaskInstaller::getListsTable();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE uuid = %s", array($table, $uuid)));
        if (is_array($results) && count($results) > 0) {
            return $this->populateListData($results[0]);
        }

        return null;
    }

    /**
     * Returns list by board_id.
     *
     * @param string $board_id
     *
     * @return BuddyTaskList[]
     */
    public function getByBoardId($board_id) {
        global $wpdb;

        $lists = array();
        $table = BuddyTaskInstaller::getListsTable();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE board_id = %s order by id asc", array($table, $board_id)));
        if (is_array($results)) {
            foreach ($results as $result) {
                $lists[] = $this->populateListData($result);
            }
        }

        return $lists;
    }

    /**
     * Deletes the list by ID.
     *
     * @param integer $id
     *
     * @return null
     */
    public function deleteById($id) {
        global $wpdb;

        $id = intval($id);
        $table = BuddyTaskInstaller::getListsTable();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query($wpdb->prepare("DELETE FROM %i WHERE id = %d", array($table, $id)));
    }

	/**
	 * Converts raw object into BuddyTaskList object.
	 *
	 * @param stdClass $rawListData
	 *
	 * @return BuddyTaskList
	 */
	private function populateListData($rawListData) {
		$list = new BuddyTaskList();
		if ($rawListData->id > 0) {
			$list->setId(intval($rawListData->id));
		}
		$list->setName($rawListData->name);
		$list->setUuid($rawListData->uuid);
		$list->setBoardId($rawListData->board_id);

		return $list;
	}
}