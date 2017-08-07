<?php
# Copyright (c) 2017, CESNET. All rights reserved.
#
# Redistribution and use in source and binary forms, with or
# without modification, are permitted provided that the following
# conditions are met:
#
#   o Redistributions of source code must retain the above
#     copyright notice, this list of conditions and the following
#     disclaimer.
#   o Redistributions in binary form must reproduce the above
#     copyright notice, this list of conditions and the following
#     disclaimer in the documentation and/or other materials
#     provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
# CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
# INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
# MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS
# BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
# EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
# ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
# OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
# POSSIBILITY OF SUCH DAMAGE.

/**
 * @author Jakub Mlcak
 */
class CveTagsManager extends DefaultManager
{
    /**
    * Create if not exist, else set id
    * @return false if already exist
    */
    public function storeCveTag(CveTag &$cveTag)
    {
        Utils::log(LOG_DEBUG, "Storing the cveTag", __FILE__, __LINE__);
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
