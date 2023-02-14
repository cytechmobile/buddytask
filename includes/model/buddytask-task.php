<?php

/**
 * BuddyTask task model.
 */
class BuddyTaskTask {
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
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var integer
     */
    public $list_id;

    /**
     * @var integer
     */
    public $parent_id;

    /**
     * @var integer
     */
    public $position;

    /**
     * @var integer
     */
    public $created_at;

    /**
     * @var integer
     */
    public $created_by;

    /**
     * @var integer
     */
    public $due_to;

    /**
     * @var boolean
     */
    public $done;

    /**
     * @var integer
     */
    public $done_at;

    /**
     * @var integer
     */
    public $done_by;

    /**
     * @var integer
     */
    public $done_percent;

    /**
     * @var BuddyTaskOwner[]
     */
    public $owners = array();

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getListId()
    {
        return $this->list_id;
    }

    /**
     * @param int $list_id
     */
    public function setListId($list_id)
    {
        $this->list_id = $list_id;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param int $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return int
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param int $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
    }

    /**
     * @return int
     */
    public function getDueTo()
    {
        return $this->due_to;
    }

    /**
     * @param int $due_to
     */
    public function setDueTo($due_to)
    {
        $this->due_to = $due_to;
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->done;
    }

    /**
     * @param bool $done
     */
    public function setDone($done)
    {
        $this->done = $done;
    }

    /**
     * @return int
     */
    public function getDoneAt()
    {
        return $this->done_at;
    }

    /**
     * @param int $done_at
     */
    public function setDoneAt($done_at)
    {
        $this->done_at = $done_at;
    }

    /**
     * @return int
     */
    public function getDoneBy()
    {
        return $this->done_by;
    }

    /**
     * @param int $done_by
     */
    public function setDoneBy($done_by)
    {
        $this->done_by = $done_by;
    }

    /**
     * @return int
     */
    public function getDonePercent()
    {
        return $this->done_percent;
    }

    /**
     * @param int $done_percent
     */
    public function setDonePercent($done_percent)
    {
        $this->done_percent = $done_percent;
    }

    /**
     * @return BuddyTaskTask[]
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param BuddyTaskTask $task
     */
    public function addTask(BuddyTaskTask $task)
    {
        array_push($this->tasks, $task);
    }

    /**
     * @param BuddyTaskTask[] $tasks
     */
    public function setTasks($tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * @return BuddyTaskOwner[]
     */
    public function getOwners()
    {
        return $this->owners;
    }

    /**
     * @param BuddyTaskOwner[] $owners
     */
    public function setOwners($owners)
    {
        $this->owners = $owners;
    }

    /**
     * @param BuddyTaskOwner $owner
     */
    public function addOwner(BuddyTaskOwner $owner)
    {
        array_push($this->owners, $owner);
    }

}