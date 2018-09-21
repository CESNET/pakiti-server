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
 * @author Michal Prochazka
 */
class VdsSubSourceDao
{
    private $db;

    public function __construct(DbManager $dbManager) {
        $this->db = $dbManager;
    }

    /**
     * Stores the vdsSubSource in the DB
     */
    public function create(VdsSubSourceDao $vdsSubSource) {
        $this->db->query("insert into VdsSubSource set
            name='".$this->db->escape($vdsSubSource->getName())."',
            type='".$this->db->escape($vdsSubSource->getType())."',
            vdsSourceId='".$this->db->escape($vdsSubSource->getVdsSourceId())."'");

        # Set the newly assigned id
        $vdsSubSource->setId($this->db->getLastInsertedId());
    }

    public function getIdByName($name){
        $id = $this->db->queryToSingleValue("select id from VdsSubSource
            where name='".$this->db->escape($name)."'");
        if ($id == null) {
            return -1;
        }
        return $id;
    }

    /**
     * We can get the data by ID or name
     */
    protected function getBy($value, $type) {
        $where = "";
        if ($type == "id") {
        $where = "id=".$this->db->escape($value);
        } else if ($type == "name") {
        $where = "name='".$this->db->escape($value)."'";
        } else {
        throw new Exception("Undefined type of the getBy");
        }

        $params = array();
        array_push($params, $pakiti);

        $type = $this->db->queryToSingleValue("select `type` from VdsSubSource where $where");

        return $this->db->queryObject("select id as _id, name as _name from VdsSubSource
            where $where", $type, $params);
    }
}
