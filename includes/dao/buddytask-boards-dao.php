<?php

/**
 * BuddyTask boards DAO
 */
class BuddyTaskBoardsDAO {
	public function __construct() {
        require_once(buddytask_get_includes_dir() . 'model/buddytask-board.php');
	}

	/**
	 * Creates or updates the board and returns it.
	 *
	 * @param BuddyTaskBoard $board
	 *
	 * @return BuddyTaskBoard
	 * @throws Exception On validation error
	 */
	public function save($board) {
		global $wpdb;

		// low-level validation:
		if ($board->getName() === null) {
			throw new Exception('Name of the board cannot equal null');
		}

		//prepare board data:
		$table = BuddyTaskInstaller::getBoardsTable();

		// update or insert:
		if ($board->getId() !== null) {
            $columns = array(
                'name' => $board->getName()
            );

			$wpdb->update($table, $columns, array('id' => $board->getId()), '%s', '%d');
		} else {
            $columns = array(
                'post_id' => $board->getPostId(), //post id
                'group_id' => $board->getGroupId(), //bp group id
                'name' => $board->getName(), //the name of the board
                'uuid' => $board->getUuid(), //a uuid for the board
                'created_at' => $board->getCreatedAt(), //the time the board was created in epoch
                'created_by' => $board->getCreatedBy(), // the user who created the board
            );

			$wpdb->insert($table, $columns);
			$board->setId($wpdb->insert_id);
		}

		return $board;
	}

	/**
	 * Returns board by ID.
	 *
	 * @param integer $id
	 *
	 * @return BuddyTaskBoard|null
	 */
	public function get($id) {
		global $wpdb;

		$table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE id = %d", intval($id));
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateBoardData($results[0]);
		}

		return null;
	}

	/**
	 * Returns all boards sorted by name.
	 *
	 * @return BuddyTaskBoard[]
	 */
	public function getAll() {
		global $wpdb;

		$boards = array();
		$table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("SELECT * FROM ".$table." ORDER BY name ASC");
		$results = $wpdb->get_results($sql);
		if (is_array($results)) {
			foreach ($results as $result) {
				$boards[] = $this->populateBoardData($result);
			}
		}

		return $boards;
	}

	/**
	 * Returns board by name.
	 *
	 * @param string $name
	 *
	 * @return BuddyTaskBoard|null
	 */
	public function getByName($name) {
		global $wpdb;

		$name = addslashes($name);
		$table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("SELECT * FROM ". $name ." WHERE name = %s", $name);
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateBoardData($results[0]);
		}

		return null;
	}

    /**
     * Returns board by uuid.
     *
     * @param string $uuid
     *
     * @return BuddyTaskBoard|null
     */
    public function getByUuid($uuid) {
        global $wpdb;

        $table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE uuid = %s", $uuid);
        $results = $wpdb->get_results($sql);
        if (is_array($results) && count($results) > 0) {
            return $this->populateBoardData($results[0]);
        }

        return null;
    }

    /**
     * Returns board by group_id.
     *
     * @param string $group_id
     *
     * @return BuddyTaskBoard[]
     */
    public function getByGroupId($group_id) {
        global $wpdb;

        $boards = array();
        $table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE group_id = %s", $group_id);
        $results = $wpdb->get_results($sql);
        if (is_array($results)) {
            foreach ($results as $result) {
                $boards[] = $this->populateBoardData($result);
            }
        }

        return $boards;
    }

    public function getByPostId($post_id) {
        global $wpdb;

        $boards = array();
        $table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("SELECT * FROM ".$table." WHERE post_id = %s", $post_id);
        $results = $wpdb->get_results($sql);
        if (is_array($results)) {
            foreach ($results as $result) {
                $boards[] = $this->populateBoardData($result);
            }
        }

        return $boards;
    }

    /**
     * Deletes the board by ID.
     *
     * @param integer $id
     *
     * @return null
     */
    public function deleteById($id) {
        global $wpdb;

        $id = intval($id);
        $table = BuddyTaskInstaller::getBoardsTable();
        $sql = $wpdb->prepare("DELETE FROM ".$table." WHERE id = %d", $id);
        $wpdb->get_results($sql);
    }

	/**
	 * Converts raw object into BuddyTAsk object.
	 *
	 * @param stdClass $rawBoardData
	 *
	 * @return BuddyTaskBoard
	 */
	private function populateBoardData($rawBoardData) {
		$board = new BuddyTaskBoard();
		if ($rawBoardData->id > 0) {
			$board->setId(intval($rawBoardData->id));
		}
		$board->setName($rawBoardData->name);
		$board->setUuid($rawBoardData->uuid);
		$board->setGroupId($rawBoardData->group_id);
		$board->setPostId($rawBoardData->post_id);
		$board->setCreatedAt($rawBoardData->created_at);
		$board->setCreatedBY($rawBoardData->created_by);

		return $board;
	}
}