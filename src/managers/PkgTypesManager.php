<?php

/**
 * @author Jakub Mlcak
 */
class PkgTypesManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storePkgType(PkgType $pkgType)
    {
        Utils::log(LOG_DEBUG, "Storing the pkgType", __FILE__, __LINE__);
        if ($pkgType == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("PkgType object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("PkgType");
        $pkgType->setId($dao->getIdByName($pkgType->getName()));
        if ($pkgType->getId() == -1) {
            # PkgType is missing, so store it
            $dao->create($pkgType);
            $new = true;
        }
        return $new;
    }

    /**
     * Get pkgType ID by name
     */
    public function getPkgTypeIdByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting pkgType ID by name[$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("PkgType")->getIdByName($name);
    }

    /**
     * Get all pkgTypes IDs
     */
    public function getPkgTypesIds()
    {
        return $this->getPakiti()->getDao("PkgType")->getIds();
    }
}
