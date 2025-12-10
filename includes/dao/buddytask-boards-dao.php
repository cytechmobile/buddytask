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

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
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

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE id = %d", array($table, intval($id))));
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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i ORDER BY name ASC", array($table)));
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

		$table = BuddyTaskInstaller::getBoardsTable();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE name = %s", array($table, $name)));
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE uuid = %s", array($table, $uuid)));
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE group_id = %s", array($table, $group_id)));
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM %i WHERE post_id = %s", array($table, $post_id)));
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
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query($wpdb->prepare("DELETE FROM %i WHERE id = %d", array($table, $id)));
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