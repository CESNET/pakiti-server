<?php

/**
 * @author Jakub Mlcak
 */
class DomainsManager extends DefaultManager
{
    /**
    * Create if not exist, else set id
    * @return false if already exist
    */
    public function storeDomain(Domain $domain)
    {
        Utils::log(LOG_DEBUG, "Storing the domain", __FILE__, __LINE__);
        if ($domain == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Domain object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("Domain");
        $domain->setId($dao->getIdByName($domain->getName()));
        if ($domain->getId() == -1) {
            # Domain is missing, so store it
            $dao->create($domain);
            $new = true;
        }
        return $new;
    }
}
