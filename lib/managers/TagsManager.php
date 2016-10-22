<?php
# Copyright (c) 2011, CESNET. All rights reserved.
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

class TagsManager extends DefaultManager {
  private $_pakiti;

  public function __construct(Pakiti &$pakiti) {
    $this->_pakiti =& $pakiti;
  }

  public function getPakiti() {
    return $this->_pakiti;
  }
  
  public function getTagById($id) {
    return $this->getPakiti()->getDao("Tag")->getById($id);  
  }
  
  public function getTagByName($name) {
    return $this->getPakiti()->getDao("Tag")->getByName($name);  
  }
  
  public function getTagIdByName($name) {
    return $this->getPakiti()->getDao("Tag")->getIdByName($name);  
  }
 
  /*
   * Retrieves all tags assigned to the host
   */
  public function getHostTags() {
  }

  public function getCveTags(Cve $cve)
  {
    $sql = "select Tag.id as _id, Tag.name as _name, Tag.description
            as _description, CveTag.reason as _reason, CveTag.modifier as _modifier, CveTag.timestamp as _timestamp,
            CveTag.enabled as _enabled from CveTag join Tag on CveTag.tagId=Tag.id where CveTag.cveName='" . $cve->getName() . "'";

    return $this->getPakiti()->getManager("DbManager")->queryObjects($sql, "Tag");
  }

  public function getCvesTags()
  {
    $sql = "select 
            Tag.id as _id, Tag.name as _name, Tag.description as _description,
            CveTag.reason as _reason, CveTag.cveName as _cveName, CveTag.modifier as _modifier, CveTag.timestamp as _timestamp, CveTag.enabled as _enabled 
            from CveTag join Tag on CveTag.tagId=Tag.id";

    return $this->getPakiti()->getManager("DbManager")->queryObjects($sql, "Tag");
  }

  /*
   * Create association between host and tag
   */
  public function assignTagToHost(Host &$host, Tag &$tag) {
    if ($host == null || $host->getId() == -1 || $tag == null) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }
  
    # Check if the tag name is valid
    if ($tag->getName() == "") {
      $tag->setName(Constants::$NA);
    }
    $tagId = $this->getTagIdByName($tag->getName());
    if ($tagId == -1) {
      # HostGroup doesn't exist, so create it
      $this->getPakiti()->getDao("Tag")->create($tag);
    } else {
      $tag->setId($tagId);
    }
    Utils::log(LOG_DEBUG, "Assinging the tag to the host [hostId=" . $host->getId() . ",tag=" . $tag->getName() . "]", __FILE__, __LINE__);
    
    # Check if the tag already exists
    $isAssigned =
    $this->getPakiti()->getManager("DbManager")->queryToSingleValue(
      		"select 1 from HostTag where 
      	 		hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId())." and 
      	 		tagId=".$this->getPakiti()->getManager("DbManager")->escape($tag->getId()));

    if ($isAssigned == null) {
      # Association between host and hostTag doesn't exist, so create it
      $this->getPakiti()->getManager("DbManager")->query("
      		insert into HostTag set 
      			hostId=".$this->getPakiti()->getManager("DbManager")->escape($host->getId()).",
      	 		tagId=".$this->getPakiti()->getManager("DbManager")->escape($tag->getId()));
    }
  }

  /*
 * Create association between cve and tag
 */
  public function assignTagToCve(Cve &$cve, Tag &$tag)
  {
    if ($cve == null || $cve->getName() == "" || $tag == null) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cve object is not valid or Cve.name is not set");
    }

    # Check if the tag name is valid
    if ($tag->getName() == "") {
      $tag->setName(Constants::$NA);
    }

    $tagId = $this->getTagIdByName($tag->getName());
    if ($tagId == -1) {
      $this->getPakiti()->getDao("Tag")->create($tag);
    } else {
      $tag->setId($tagId);
    }
    Utils::log(LOG_DEBUG, "Assinging the tag to the cve [cveName=" . $cve->getName() . ",tag=" . $tag->getName() . "]", __FILE__, __LINE__);

    # Check if the already assigned
    $isAssigned =
        $this->getPakiti()->getManager("DbManager")->queryToSingleValue(
            "select 1 from CveTag where
      	 		cveName='" . $this->getPakiti()->getManager("DbManager")->escape($cve->getName()) . "' and
      	 		tagId=" . $this->getPakiti()->getManager("DbManager")->escape($tag->getId()));

    if ($isAssigned != null) {
      throw new Exception($cve->getName() . " is already associated with " . $tag->getName() . " tag!");
    } else {
      # Association between cve and cveTag doesn't exist, so create it
      $this->getPakiti()->getManager("DbManager")->query("
      		insert into CveTag set
      			cveName='" . $this->getPakiti()->getManager("DbManager")->escape($cve->getName()) . "',
      	 		tagId=" . $this->getPakiti()->getManager("DbManager")->escape($tag->getId()) . ",
      	 		`reason`='" . $this->getPakiti()->getManager("DbManager")->escape($tag->getReason()) . "'");

    }
  }



  
  /*
   * Removes all tags associated with the host.
   */
  public function removeHostTags(Host &$host) {
    if ($host == null || $host->getId() == -1) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Host object is not valid or Host.id is not set");
    }

    Utils::log(LOG_DEBUG, "Removing all tags associated with the host [hostname='{$host->getHostname()}']", __FILE__, __LINE__);
    
    $this->getPakiti()->getDao("Tag")->deleteTagsByHostId($host->getId());
  }

  /*
   * Removes all tags associated with the CVE.
   */
  public function removeCveTags(Cve &$cve)
  {
    if ($cve == null || $cve->getName() == "") {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cve object is not valid or Cve.name is not set");
    }

    Utils::log(LOG_DEBUG, "Removing all tags associated with the cve [cveName='{$cve->getName()}']", __FILE__, __LINE__);

    $this->getPakiti()->getDao("Tag")->deleteTagsByCveName($cve->getName());
  }
  
  
    /*
     * Get all tags
     */
    public function getTags($orderBy = "name", $pageSize = -1, $pageNum = -1) {
      Utils::log(LOG_DEBUG, "Getting all tags", __FILE__, __LINE__);
        $tagsIds =& $this->getPakiti()->getDao("Tag")->getTagsIds($orderBy, $pageSize, $pageNum);
    
        $tags = array();
	if ($tagsIds) {
          foreach ($tagsIds as $tagId) {
            array_push($tags, $this->getTagById($tagId));
          }
	}
        return $tags;
    }

  /*
 * Get all tags
 */
  public function getTagsByCveName($cveName)
  {
    Utils::log(LOG_DEBUG, "Getting tags by CVE name=" . $cveName, __FILE__, __LINE__);

    if ($cveName == "") {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cve name is not valid");
    }
    return $this->getPakiti()->getManager("DbManager")->queryObjects("select id as _id, cveName as _cveName, name as _name,
    description as _description, reason as _reason, modifier as _modifier, timestamp as
    _timestamp, enabled as _enabled from CveTag join Tag on CveTag.tagId = Tag.id  where cveName='" . $this->getPakiti()->getManager("DbManager")->escape($cveName) . "'", "Tag");
  }

  /** Get Tag by CveName and TagId
   * @param $cveName
   * @param $tagId
   * @return mixed
   * @throws Exception
   */
  public function getCveTagByCveNameAndTagId($cveName, $tagId)
  {
    if ($cveName == "") {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Cve name is not valid");
    }

    if (!is_numeric($tagId)) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Tag id is not valid");
    }

    return $this->getPakiti()->getManager("DbManager")->queryObject("select id as _id, cveName as _cveName, name as _name,
    description as _description, reason as _reason, modifier as _modifier, timestamp as
    _timestamp, enabled as _enabled from CveTag join Tag on CveTag.tagId = Tag.id  where cveName='" . $this->getPakiti()->getManager("DbManager")->escape($cveName) . "'
    and tagId=" . $this->getPakiti()->getManager("DbManager")->escape($tagId) . "
    ", "Tag");
  }

  public function updateCveTag(Tag &$tag)
  {
    if ($tag->getCveName() == "") {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Tag CVE name is not valid");
    }

    if (!is_numeric($tag->getId())) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Tag id is not valid");
    }

    $this->getPakiti()->getManager("DbManager")->query("update CveTag set cveName= '" . $this->getPakiti()->getManager("DbManager")->escape($tag->getCveName()) . "', tagId=" . $this->getPakiti()->getManager("DbManager")->escape($tag->getId()) . ",
     reason='" . $this->getPakiti()->getManager("DbManager")->escape($tag->getReason()) . "', modifier= '" . $this->getPakiti()->getManager("DbManager")->escape($tag->getModifier()) . "', timestamp ='" . $this->getPakiti()->getManager("DbManager")->escape($tag->getTimestamp()) . "',
    enabled=" . $this->getPakiti()->getManager("DbManager")->escape($tag->getEnabled()) . " where cveName='" . $this->getPakiti()->getManager("DbManager")->escape($tag->getCveName()) . "' and tagId=" . $this->getPakiti()->getManager("DbManager")->escape($tag->getId()) . "");
  }

  /**
   * Delete tag from CveTag table
   */
  public function deleteCveTag(Tag &$tag)
  {
    if ($tag->getCveName() == "") {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Tag CVE name is not valid");
    }

    if (!is_numeric($tag->getId())) {
      Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
      throw new Exception("Tag id is not valid");
    }

    $this->getPakiti()->getManager("DbManager")->query("delete from CveTag where cveName='" . $this->getPakiti()->getManager("DbManager")->escape($tag->getCveName()) . "' and tagId=" . $this->getPakiti()->getManager("DbManager")->escape($tag->getId()) . "");
  }
}
