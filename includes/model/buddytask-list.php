<?php

/**
 * BuddyTask board model.
 */
class BuddyTaskList {
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $uuid;

    /**
     * @var string
     */
    public $name;

    /**
     * @var integer
     */
    public $board_id;

    /**
     * @var BuddyTaskTask[]
     */
    public $tasks = array();

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getBoardId()
    {
        return $this->board_id;
    }

    /**
     * @param int $board_id
     */
    public function setBoardId($board_id)
    {
        $this->board_id = $board_id;
    }

    /**
     * @return BuddyTaskTask[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param BuddyTaskTask[] $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @param BuddyTaskTask $task
     */
    public function addTask(BuddyTaskTask $task)
    {
        array_push($this->tasks, $task);
    }

}