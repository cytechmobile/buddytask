<?php

/**
 * BuddyTask board model.
 */
class BuddyTaskBoard {
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
    public $group_id;

    /**
     * @var integer
     */
    public $post_id;

    /**
     * @var integer
     */
    public $created_by;

    /**
     * @var integer
     */
    public $created_at;

    /**
     * @var BuddyTaskList[]
     */
    public $lists = array();


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
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * @param int $group_id
     */
    public function setGroupId($group_id)
    {
        $this->group_id = $group_id;
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->post_id;
    }

    /**
     * @param int $post_id
     */
    public function setPostId($post_id)
    {
        $this->post_id = $post_id;
    }

    /**
     * @return BuddyTaskList[]
     */
    public function getLists()
    {
        return $this->lists;
    }

    /**
     * @param BuddyTaskList[] $lists
     */
    public function setLists($lists)
    {
        $this->lists = $lists;
    }

    /**
     * @param BuddyTaskList $list
     */
    public function addList(BuddyTaskList $list)
    {
        array_push($this->lists, $list);
    }



}