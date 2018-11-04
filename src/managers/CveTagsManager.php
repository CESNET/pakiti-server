<?php

/**
 * @author Jakub Mlcak
 */
class CveTagsManager extends DefaultManager
{
    /**
     * Create if not exist, else set id
     * @return false if already exist
     */
    public function storeCveTag(CveTag $cveTag)
    {
        Utils::log(LOG_DEBUG, "Storing cveTag " . $cveTag->getCveName(), __FILE__, __LINE__);
        if ($cveTag == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("CveTag object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("CveTag");
        $cveTag->setId($dao->getIdByCveNameTagName($cveTag->getCveName(), $cveTag->getTagName()));
        if ($cveTag->getId() == -1) {
            # CveTag is missing, so store it
            $dao->create($cveTag);
            $new = true;
        } else {
            $dao->update($cveTag);
        }
        return $new;
    }

    public function getCveTags($orderBy = null, $pageSize = -1, $pageNum = -1)
    {
        Utils::log(LOG_DEBUG, "Getting cveTags", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        $ids = $dao->getIds($orderBy, $pageSize, $pageNum);

        $cveTags = array();
        foreach ($ids as $id) {
            array_push($cveTags, $dao->getById($id));
        }
        return $cveTags;
    }

    public function getCveTagsCount()
    {
        Utils::log(LOG_DEBUG, "Getting cveTags count", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        return sizeof($dao->getIds());
    }

    public function getCveTagIdByCveNameTagName($cveName, $tagName)
    {
        Utils::log(LOG_DEBUG, "Getting cveTag ID by CVE name[".$cveName."] and Tag name[".$tagName."]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        return $dao->getIdByCveNameTagName($cveName, $tagName);
    }

    public function getCveTagsByCveName($cveName)
    {
        Utils::log(LOG_DEBUG, "Getting cveTags by CVE name[".$cveName."]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        $ids = $dao->getIdsByCveName($cveName);

        $cveTags = array();
        foreach ($ids as $id) {
            array_push($cveTags, $dao->getById($id));
        }
        return $cveTags;
    }

    public function getCveTagById($id)
    {
        Utils::log(LOG_DEBUG, "Getting cveTag by ID[".$id."]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        return $dao->getById($id);
    }

    public function getCveTagsIds()
    {
        Utils::log(LOG_DEBUG, "Getting cveTags IDs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        return $dao->getIds();
    }

    public function deleteCveTagById($id)
    {
        Utils::log(LOG_DEBUG, "Deleting cveTag by ID[".$id."]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        $dao->deleteById($id);
    }

    public function getTagNames()
    {
        Utils::log(LOG_DEBUG, "Getting tag names", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("CveTag");
        return $dao->getTagNames();
    }
}
