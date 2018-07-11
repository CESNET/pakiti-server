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
class StatsManager extends DefaultManager
{
    public function get($name)
    {
        return $this->getPakiti()->getDao("Stat")->get($name);
    }

    public function listAll()
    {
        return $this->getPakiti()->getDao("Stat")->listAll();
    }

    public function add($name, $number)
    {
        $stat = $this->getPakiti()->getDao("Stat")->get($name);
        if ($stat == null) {
            $stat = new Stat();
            $stat->setName($name);
            $stat->setValue($number);
            $stat = $this->getPakiti()->getDao("Stat")->create($stat);
        } else {
            $value = $stat->getValue();
            $stat->setValue($value + $number);
            $stat = $this->getPakiti()->getDao("Stat")->update($stat);
        }
    }

    public function remove($name)
    {
        return $this->getPakiti()->getDao("Stat")->delete($name);
    }
}
