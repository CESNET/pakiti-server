<?php

/* Instantiating dependencies during execution directly using new is against
   the principles of Dependency Injection (see
   e.g.https://phpfashion.com/co-je-dependency-injection). In Pakiti, we are
   advised to get rid of creating objects during execution by getManager() and
   pass each object its dependencies explicitly (using the contstructor).

   Pragmatically, it's mainly needed for objects that need to be mocked for tests (in our case).

   There's more information at https://php-di.org/doc/understanding-di.html */

class PakitiFactory
{
	private $db_dbManager = null;

    public function __construct(
		DbManager $dbManager)
	{
		$this->db_dbManager = $dbManager;
	}

	public function create()
	{
		$pakiti = new Pakiti();

		if ($this->db_dbManager)
			$pakiti->setManager("DbManager", $this->db_dbManager);

		return $pakiti;
	}
}
