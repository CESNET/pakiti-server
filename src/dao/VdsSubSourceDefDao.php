<?php

class VdsSubSourceDefDao
{
    private $db;

    public function __construct(DbManager $dbManager) {
        $this->db = $dbManager;
    }

	public function getNamesByCveName($cveName)
	{
		$select = "distinct(VdsSubSourceDef.name)";
		$from = "VdsSubSourceDef";
		$join[] = "inner join CveDef on VdsSubSourceDef.id = CveDef.vdsSubSourceDefId";
		$join[] = "inner join CveCveDef on CveDef.id = CveCveDef.cveDefId";
		$join[] = "inner join Cve on (CveCveDef.cveId = Cve.id and Cve.name = '" . $this->db->escape($cveName) . "')";
		$where = null;
		$order = null;

		$sql = Utils::sqlSelectStatement($select, $from, $join, $where, $order);
		return $this->db->queryToSingleValueMultiRow($sql);
	}
}
