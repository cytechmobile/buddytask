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

			$wpdb->update($table, $columns, array('id' => $list->getId()), '%s', '%d');
		} else {
            $columns = array(
                'board_id' => $list->getBoardId(),
                'name' => $list->getName(), 
                'uuid' => $list->getUuid(), 
            );

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
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE id = %d", intval($id));
		$results = $wpdb->get_results($sql);
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
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE uuid = %s", $uuid);
        $results = $wpdb->get_results($sql);
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
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE board_id = %s order by id asc", $board_id);
        $results = $wpdb->get_results($sql);
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
        $sql = $wpdb->prepare("DELETE FROM ".$table." WHERE id = %d", $id);
        $wpdb->get_results($sql);
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