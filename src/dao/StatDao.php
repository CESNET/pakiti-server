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
class StatDao
{
    private $db;

    public function __construct(DbManager $dbManager)
    {
        $this->db = $dbManager;
    }

    /**
     * Stores the stat in the DB
     */
    public function create(Stat $stat)
    {
        $this->db->query("insert into Stat set
            name='".$this->db->escape($stat->getName())."',
            value=".$this->db->escape($stat->getValue())."");
    }

    /**
     * Get the stat by its name
     */
    public function get($name)
    {
        return $this->db->queryObject("select name as _name, value as _value from Stat
            where name='".$this->db->escape($name)."'", "Stat");
    }

    /**
     * List all stats
     */
    public function listAll()
    {
        return $this->db->queryObjects("select name as _name, value as _value from Stat", "Stat");
    }

    /**
     * Update the stat in the DB
     */
    public function update(Stat $stat)
    {
        $this->db->query("update Stat set value=".$this->db->escape($stat->getValue())."
            where name='".$this->db->escape($stat->getName())."'");
    }

    /**
     * Delete the stat from the DB
     */
    public function delete($name)
    {
        $this->db->query("delete from Stat where name=".$this->db->escape($name));
    }
}
