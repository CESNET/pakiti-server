<?php

/**
 * @author Jakub Mlcak
 */
class StatDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    /**
     * Stores the stat in the DB
     */
    public function create(Stat $stat)
    {
        $this->db->query("insert into Stat set
            name='".$this->db->escape($stat->getName())."',
            value=".$this->db->escape($stat->getValue())."");
    }

    /**
     * Get the stat by its name
     */
    public function get($name)
    {
        return $this->db->queryObject("select name as _name, value as _value from Stat
            where name='".$this->db->escape($name)."'", "Stat");
    }

    /**
     * List all stats
     */
    public function listAll()
    {
        return $this->db->queryObjects("select name as _name, value as _value from Stat", "Stat");
    }

    /**
     * Update the stat in the DB
     */
    public function update(Stat $stat)
    {
        $this->db->query("update Stat set value=".$this->db->escape($stat->getValue())."
            where name='".$this->db->escape($stat->getName())."'");
    }

    /**
     * Delete the stat from the DB
     */
    public function delete($name)
    {
        $this->db->query("delete from Stat where name=".$this->db->escape($name));
    }
}
