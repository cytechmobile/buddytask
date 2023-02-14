<?php

/**
 * BuddyTask task owner model.
 */
class BuddyTaskOwner {
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $task_id;

    /**
     * @var integer
     */
    public $user_id;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $display_name;

    /**
     * @var string
     */
    public $avatar_url;

    /**
     * @var integer
     */
    public $assigned_at;

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
     * @return int
     */
    public function getTaskId()
    {
        return $this->task_id;
    }

    /**
     * @param int $task_id
     */
    public function setTaskId($task_id)
    {
        $this->task_id = $task_id;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * @param string $display_name
     */
    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;
    }

    /**
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatar_url;
    }

    /**
     * @param string $avatar_url
     */
    public function setAvatarUrl($avatar_url)
    {
        $this->avatar_url = $avatar_url;
    }

    /**
     * @return int
     */
    public function getAssignedAt()
    {
        return $this->assigned_at;
    }

    /**
     * @param int $assigned_at
     */
    public function setAssignedAt($assigned_at)
    {
        $this->assigned_at = $assigned_at;
    }



}