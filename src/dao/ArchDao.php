<?php

/**
 * @author Michal Prochazka
 */
class ArchDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    /**
     * Stores the arch in the DB
     */
    public function create(Arch $arch)
    {
        $this->db->query("insert into Arch set
            name='".$this->db->escape($arch->getName())."'");

        # Set the newly assigned id
        $arch->setId($this->db->getLastInsertedId());
    }

    /**
     * Get the arch by its ID
     */
    public function getById($id)
    {
        if (!is_numeric($id)) {
            return null;
        }
        return $this->getBy($id, "id");
    }

    /**
     * Get the arch by its name
     */
    public function getByName($name)
    {
        return $this->getBy($name, "name");
    }

    /**
     * Get the arch ID by its name
     */
    public function getIdByName($name)
    {
        $sql = "select id from Arch
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getIds()
    {
        $sql = "select id from Arch";
        return $this->db->queryToSingleValueMultiRow($sql);
    }

    /**
     * Update the arch in the DB
     */
    public function update(Arch $arch)
    {
        $this->db->query("update Arch set
            name='".$this->db->escape($arch->getName())."
            where id=".$this->db->escape($arch->getId()));
    }

    /**
     * Delete the arch from the DB
     */
    public function delete(Arch $arch)
    {
        $this->db->query("delete from Arch where id=".$this->db->escape($arch->getId()));
    }

    /**
     * We can get the data by ID or name
     */
    protected function getBy($value, $type)
    {
        $where = "";
        if ($type == "id") {
            $where = "id=".$this->db->escape($value);
        } elseif ($type == "name") {
            $where = "name='".$this->db->escape($value)."'";
        } else {
            throw new Exception("Undefined type of the getBy");
        }
        return $this->db->queryObject("select id as _id, name as _name from Arch
            where $where", "Arch");
    }
}
