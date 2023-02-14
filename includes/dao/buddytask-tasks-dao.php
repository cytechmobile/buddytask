<?php

/**
 * BuddyTask tasks DAO
 */
class BuddyTaskTasksDAO {
	public function __construct() {
        require_once(buddytask_get_includes_dir() . 'model/buddytask-task.php');
	}

	/**
	 * Creates or updates the task and returns it.
	 *
	 * @param BuddyTaskTask $task
	 *
	 * @return BuddyTaskTask
	 * @throws Exception On validation error
	 */
	public function save($task) {
		global $wpdb;

		// low-level validation:
		if ($task->getTitle() === null) {
			throw new Exception('Title of the task cannot equal null');
		}

		//prepare task data:
		$table = BuddyTaskInstaller::getTasksTable();

		// update or insert:
		if ($task->getId() !== null) {
            $columns = array(
                'title' => $task->getTitle(),
                'list_id' => $task->getListId(),
                'description' => $task->getDescription(),
                'due_to' => $task->getDueTo(),
                'done' => $task->isDone(),
                'done_by' => $task->getDoneBy(),
                'done_at' => $task->getDoneAt(),
                'done_percent' => $task->getDonePercent(),
                'position' => $task->getPosition()
            );

			$wpdb->update($table, $columns, array('id' => $task->getId()), '%s', '%d');
		} else {
            $columns = array(
                'title' => $task->getTitle(),
                'list_id' => $task->getListId(),
                'parent_id' => $task->getParentId(),
                'uuid' => $task->getUuid(),
                'description' => $task->getDescription(),
                'created_at' => $task->getCreatedAt(),
                'created_by' => $task->getCreatedBy(),
                'due_to' => $task->getDueTo(),
                'done' => $task->isDone(),
                'done_by' => $task->getDoneBy(),
                'done_at' => $task->getDoneAt(),
                'done_percent' => $task->getDonePercent(),
                'position' => $task->getPosition()
            );

			$wpdb->insert($table, $columns);
			$task->setId($wpdb->insert_id);
		}

		return $task;
	}

	/**
	 * Returns task by ID.
	 *
	 * @param integer $id
	 *
	 * @return BuddyTaskTask|null
	 */
	public function get($id) {
		global $wpdb;

		$table = BuddyTaskInstaller::getTasksTable();
        $sql = $wpdb->prepare("SELECT * FROM ".$table." WHERE id = %d", intval($id));
		$results = $wpdb->get_results($sql);
		if (is_array($results) && count($results) > 0) {
			return $this->populateTaskData($results[0]);
		}

		return null;
	}

    /**
     * Returns task by list_id.
     *
     * @param string $list_id
     * @param bool $sub_tasks
     *
     * @return BuddyTaskTask[]
     */
    public function getByListId($list_id, $sub_tasks = false) {
        global $wpdb;

        $tasks = array();
        $table = BuddyTaskInstaller::getTasksTable();
        if($sub_tasks) {
            $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE list_id = %s order by position asc", $list_id);
        } else {
            $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE list_id = %s AND parent_id is null order by position asc", $list_id);
        }
        $results = $wpdb->get_results($sql);
        if (is_array($results)) {
            foreach ($results as $result) {
                $tasks[] = $this->populateTaskData($result);
            }
        }

        return $tasks;
    }

    /**
     * Returns task by parent_id.
     *
     * @param integer $parent_id
     *
     * @return BuddyTaskTask[]
     */
    public function getByParentId($parent_id) {
        global $wpdb;

        $tasks = array();
        $table = BuddyTaskInstaller::getTasksTable();
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE parent_id = %d order by position asc", $parent_id);
        $results = $wpdb->get_results($sql);
        if (is_array($results)) {
            foreach ($results as $result) {
                $tasks[] = $this->populateTaskData($result);
            }
        }

        return $tasks;
    }

    /**
     * Returns task by uuid.
     *
     * @param string $uuid
     *
     * @return BuddyTaskTask
     */
    public function getByUuid($uuid) {
        global $wpdb;

        $table = BuddyTaskInstaller::getTasksTable();
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE uuid = %s", $uuid);
        $results = $wpdb->get_results($sql);
        if (is_array($results) && count($results) > 0) {
            return $this->populateTaskData($results[0]);
        }

        return null;
    }

    /**
     * Deletes the task by ID.
     *
     * @param integer $id
     *
     * @return void
     */
    public function deleteById($id) {
        global $wpdb;

        $id = intval($id);
        $table = BuddyTaskInstaller::getTasksTable();
        $sql = $wpdb->prepare("DELETE FROM ". $table ." WHERE id = %d", $id);
        $wpdb->get_results($sql);
    }

    /**
     * Deletes tasks by parent_id.
     *
     * @param integer $parent_id
     *
     * @return bool
     */
    public function deleteByParentId($parent_id) {
        global $wpdb;

        $parent_id = intval($parent_id);
        $table = BuddyTaskInstaller::getTasksTable();
        $sql = $wpdb->prepare("DELETE FROM ". $table ." WHERE parent_id = %d", $parent_id);
        $wpdb->get_results($sql);
    }

    /**
     * @param $list_id
     * @param $parent_id
     * @param $task_uuid
     * @param $position
     *
     * @return bool
     */
    public function reorderTask($list_id, $parent_id, $task_uuid, $position) {
        global $wpdb;

        if($list_id === null && $parent_id === null){
            return false;
        }

        //begin transaction
        $wpdb->query('START TRANSACTION');

        $table = BuddyTaskInstaller::getTasksTable();

        //try to locate the task to reorder
        $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE uuid = %s FOR UPDATE", $task_uuid);
        $results = $wpdb->get_results($sql);
        $task = null;
        if (is_array($results) && count($results) > 0) {
            $task = $this->populateTaskData($results[0]);
            //update the list id to the new one because task might have been moved to another list
            $task->setListId($list_id);
        }

        if($task === null){
            return false;
        }

        //now get all the tasks of the target list or parent
        $task_id = $task->getId();
        if($parent_id === null){
            $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE list_id = %d AND id != %d ORDER BY position FOR UPDATE", $list_id, $task_id);
        } else {
            $sql = $wpdb->prepare("SELECT * FROM ". $table ." WHERE parent_id = %d AND id != %d ORDER BY position FOR UPDATE", $parent_id, $task_id);
        }

        $tasks = array();
        $results = $wpdb->get_results($sql);
        if (is_array($results)) {
            foreach ($results as $result) {
                $tasks[] = $this->populateTaskData($result);
            }
        }

        //finally add the new task in the proper position and persist all tasks
        array_splice($tasks, $position, 0, array($task));
        try {
            foreach ($tasks as $index => &$task) {
                $task->setPosition($index);
                $this->save($task);
            }
            $wpdb->query('COMMIT');
            return true;
        } catch (Exception $e){
            $wpdb->query('ROLLBACK');
            return false;
        }
    }

	/**
	 * Converts raw object into BuddyTaskTask object.
	 *
	 * @param stdClass $rawTaskData
	 *
	 * @return BuddyTaskTask
	 */
	private function populateTaskData($rawTaskData) {
		$task = new BuddyTaskTask();
		if ($rawTaskData->id > 0) {
			$task->setId(intval($rawTaskData->id));
		}

		$task->setUuid($rawTaskData->uuid);
		$task->setListId($rawTaskData->list_id);
		$task->setParentId($rawTaskData->parent_id);
        $task->setTitle($rawTaskData->title);
        $task->setDescription($rawTaskData->description);
        $task->setDueTo($rawTaskData->due_to);
        $task->setCreatedAt($rawTaskData->created_at);
        $task->setCreatedBy($rawTaskData->created_by);
        $task->setDone($rawTaskData->done);
        $task->setDoneAt($rawTaskData->done_at);
        $task->setDoneBy($rawTaskData->done_by);
        $task->setPosition($rawTaskData->position);
        $task->setDonePercent($rawTaskData->done_percent);

		return $task;
	}
}