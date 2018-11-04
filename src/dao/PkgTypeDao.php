<?php

/**
 * @author Jakub Mlcak
 */
class PkgTypeDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    public function create(PkgType $pkgType)
    {
        $sql = "insert into PkgType set
            name='" . $this->db->escape($pkgType->getName()) . "'";
        $this->db->query($sql);

        # Set the newly assigned id
        $pkgType->setId($this->db->getLastInsertedId());
    }

    public function getById($id)
    {
        $sql = "select id as _id, name as _name from PkgType
            where id='" . $this->db->escape($id) . "'";
        return $this->db->queryObject($sql, "PkgType");
    }

    public function getIdByName($name)
    {
        $sql = "select id from PkgType
            where name='" . $this->db->escape($name) . "'";
        $id = $this->db->queryToSingleValue($sql);
        return ($id == null) ? -1 : $id;
    }

    public function getIds()
    {
        $sql = "select id from PkgType";
        return $this->db->queryToSingleValueMultiRow($sql);
    }
}
