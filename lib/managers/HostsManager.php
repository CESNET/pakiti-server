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
 * @author Jakub Mlcak
 */
class HostsManager extends DefaultManager
{
    /**
     * Create if not exist, else update and set id
     * @return false if already exist
     */
    public function storeHost(Host &$host)
    {
        Utils::log(LOG_DEBUG, "Storing the host", __FILE__, __LINE__);
        if ($host == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Host object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("Host");
        $host->setId($dao->getIdByHostnameIpReporterHostnameReporterIp($host->getHostname(), $host->getIp(), $host->getReporterHostname(), $host->getReporterIp()));
        if ($host->getid() == -1) {
            # Host is missing, so store it
            $dao->create($host);
            $new = true;
        } else {
            $dao->update($host);
        }
        return $new;
    }

    /**
     * Try to find host using hostname, ip, reporterHostname and reporterIp
     */
    public function getHostId($hostname, $ip, $reporterHostname, $reporterIp)
    {
        Utils::log(LOG_DEBUG, "Getting the host ID", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Host")->getIdByHostnameIpReporterHostnameReporterIp($hostname, $ip, $reporterHostname, $reporterIp);
    }

    /**
     * Get the host by its ID
     */
    public function getHostById($id, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting the host[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        return $dao->getById($id, $userId);
    }

    /**
     * Get the host by its hostname
     */
    public function getHostByHostname($hostname)
    {
        Utils::log(LOG_DEBUG, "Getting the host by its hostname [hostname=$hostname]", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Host")->getByHostname($hostname);
    }

    /**
     * Get hosts
     */
    public function getHosts($orderBy = null, $pageSize = -1, $pageNum = -1, $search = null, $cveName = null, $tag = null, $hostGroupId = -1, $activeIn = null, $pkgId = -1, $userId = -1, $directlyAssignedToUser = false)
    {
        Utils::log(LOG_DEBUG, "Getting hosts", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        $hostsIds = $dao->getHostsIds($orderBy, $pageSize, $pageNum, $search, $cveName, $tag, $hostGroupId, $activeIn, $pkgId, $userId, $directlyAssignedToUser);

        $hosts = array();
        foreach ($hostsIds as $hostId) {
            array_push($hosts, $this->getHostById($hostId));
        }
        return $hosts;
    }

    /**
     * Get hosts IDs
     */
    public function getHostsIds($orderBy = null, $pageSize = -1, $pageNum = -1, $search = null, $cveName = null, $tag = null, $hostGroupId = -1, $activeIn = null, $pkgId = -1, $userId = -1, $directlyAssignedToUser = false)
    {
        Utils::log(LOG_DEBUG, "Getting hosts IDs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        return $dao->getHostsIds($orderBy, $pageSize, $pageNum, $search, $cveName, $tag, $hostGroupId, $activeIn, $pkgId, $userId, $directlyAssignedToUser);
    }

    /**
     * Get hosts count
     */
    public function getHostsCount($search = null, $cveName = null, $tag = null, $hostGroupId = -1, $activeIn = null, $pkgId = -1, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting hosts count", __FILE__, __LINE__);
        return sizeof($this->getPakiti()->getDao("Host")->getHostsIds(null, -1, -1, $search, $cveName, $tag, $hostGroupId, $activeIn, $pkgId, $userId));
    }

    public function deleteHost($id)
    {
        Utils::log(LOG_DEBUG, "Delete host[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        return $dao->delete($id);
    }

    public function getInactiveHostsLongerThan($days)
    {
        Utils::log(LOG_DEBUG, "Getting hosts inactive longer than $days days", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        $ids = $dao->getInactiveIdsLongerThan($days);

        $hosts = array();
        foreach ($ids as $id) {
            array_push($hosts, $this->getPakiti()->getDao("Host")->getById($id));
        }
        return $hosts;
    }

    public function recalculateCvesCountForHost($hostId)
    {
        Utils::log(LOG_DEBUG, "Recalculating numOfCves for Host[".$hostId."]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        $host = $this->getHostById($hostId);
        if ($host == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("Host[".$hostId."] doesn't exist");
        }
        # Get number of CVEs
        $cvesManager = $this->getPakiti()->getManager("CvesManager");
        $host->setNumOfCves(sizeof($cvesManager->getCvesNamesForHost($host->getId(), null)));
        $host->setNumOfCvesWithTag(sizeof($cvesManager->getCvesNamesForHost($host->getId(), true)));

        $dao->update($host);
    }

    public function recalculateCvesCountForEachHost()
    {
        Utils::log(LOG_DEBUG, "Recalculating numOfCves for each host", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        $ids = $dao->getHostsIds();

        foreach ($ids as $id) {
            $this->recalculateCvesCountForHost($id);
        }
    }

    /**
     * Get arch
     */
    public function getArch($name)
    {
        Utils::log(LOG_DEBUG, "Getting arch by Name", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Arch")->getByName($name);
    }

    /**
     * Create arch
     */
    public function createArch($name)
    {
        Utils::log(LOG_DEBUG, "Creating arch $name", __FILE__, __LINE__);
        $arch = new Arch();
        $arch->setName($name);
        $this->getPakiti()->getDao("Arch")->create($arch);
        return $arch;
    }
}
