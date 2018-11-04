<?php

/**
 * @author Michal Prochazka
 */
class VdsSourceDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    /**
     * Stores the vdsSource in the DB
     */
    public function create(VdsSource $vdsSource)
    {
        $this->db->query("insert into VdsSource set
            name='".$this->db->escape($vdsSource->getName())."',
            className='".$this->db->escape($vdsSource->getClassName())."'");

        # Set the newly assigned id
        $vdsSource->setId($this->db->getLastInsertedId());
    }

    /**
     * Get the vdsSource by its ID
     */
    public function getById($id, Pakiti $pakiti)
    {
        if (!is_numeric($id)) {
            return null;
        }
        return $this->getBy($id, "id", $pakiti);
    }

    /**
     * Get the vdsSource by its name
     */
    public function getByName($name, Pakiti $pakiti)
    {
        return $this->getBy($name, "name", $pakiti);
    }

    /**
     * Get the vdsSource ID by its name
     */
    public function getIdByName($name)
    {
        $id = $this->db->queryToSingleValue("select id from VdsSource
            where name='".$this->db->escape($name)."'");
        if ($id == null) {
            return -1;
        }
        return $id;
    }

    public function getVdsSourcesIds($orderBy, $pageSize, $pageNum)
    {
        $sql = "select id from VdsSource order by name";

        if ($pageSize != -1 && $pageNum != -1) {
            $offset = $pageSize*$pageNum;
            $sql .= " limit $offset,$pageSize";
        }

        return $this->db->queryToSingleValueMultiRow($sql);
    }

    /**
     * Update the vdsSource in the DB
     */
    public function update(VdsSource $vdsSource)
    {
        $this->db->query("update VdsSource set
            name='".$this->db->escape($vdsSource->getName()).",
            className='".$this->db->escape($vdsSource->getClassName()).",
            where id=".$this->db->escape($vdsSource->getId()));
    }

    /**
     * Delete the vdsSource from the DB
     */
    public function delete(VdsSource $vdsSource)
    {
        $this->db->query("delete from VdsSource where id=".$this->db->escape($vdsSource->getId()));
    }

    /**
     * We can get the data by ID or name
     */
    protected function getBy($value, $type, Pakiti $pakiti)
    {
        $where = "";
        if ($type == "id") {
            $where = "id=".$this->db->escape($value);
        } elseif ($type == "name") {
            $where = "name='".$this->db->escape($value)."'";
        } else {
            throw new Exception("Undefined type of the getBy");
        }

        $className = $this->db->queryToSingleValue("select className from VdsSource where $where");
        $params = array();
        array_push($params, $pakiti);

        return $this->db->queryObject("select id as _id, name as _name from VdsSource
            where $where", $className, $params);
    }
}
