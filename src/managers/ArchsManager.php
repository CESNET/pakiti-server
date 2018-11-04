<?php

/**
 * @author Jakub Mlcak
 */
class ArchsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeArch(Arch $arch)
    {
        Utils::log(LOG_DEBUG, "Storing the arch", __FILE__, __LINE__);
        if ($arch == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Arch object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("Arch");
        $arch->setId($dao->getIdByName($arch->getName()));
        if ($arch->getId() == -1) {
            # Arch is missing, so store it
            $dao->create($arch);
            $new = true;
        }
        return $new;
    }

    /**
     * Get arch ID by name
     */
    public function getArchIdByName($name)
    {
        Utils::log(LOG_DEBUG, "Getting arch ID by name[$name]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Arch")->getIdByName($name);
    }

    /**
     * Get all archs IDs
     */
    public function getArchsIds()
    {
        return $this->getPakiti()->getDao("Arch")->getIds();
    }
}
