<?php

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
    public function storeHost(Host $host)
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
     * Get host ID by hostname, ip, reporterHostname and reporterIp
     */
    public function getHostId($hostname, $ip, $reporterHostname, $reporterIp)
    {
        Utils::log(LOG_DEBUG, "Getting the host ID", __FILE__, __LINE__);
        return $this->getPakiti()->getDao("Host")->getIdByHostnameIpReporterHostnameReporterIp($hostname, $ip, $reporterHostname, $reporterIp);
    }

    /**
     * Get host by ID
     */
    public function getHostById($id, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting the host[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        return $dao->getById($id, $userId);
    }

    /**
     * Get hosts
     */
    public function getHosts($orderBy = null, $pageSize = -1, $pageNum = -1, $search = null, $cveName = null, $tag = null, $hostGroupId = -1, $activity = null, $pkgId = -1, $userId = -1, $directlyAssignedToUser = false)
    {
        Utils::log(LOG_DEBUG, "Getting hosts", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        $hostsIds = $dao->getHostsIds($orderBy, $pageSize, $pageNum, $search, $cveName, $tag, $hostGroupId, $activity, $pkgId, $userId, $directlyAssignedToUser);

        $hosts = array();
        foreach ($hostsIds as $hostId) {
            array_push($hosts, $this->getHostById($hostId));
        }
        return $hosts;
    }

    /**
     * Get hosts IDs
     */
    public function getHostsIds($orderBy = null, $pageSize = -1, $pageNum = -1, $search = null, $cveName = null, $tag = null, $hostGroupId = -1, $activity = null, $pkgId = -1, $userId = -1, $directlyAssignedToUser = false)
    {
        Utils::log(LOG_DEBUG, "Getting hosts IDs", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        return $dao->getHostsIds($orderBy, $pageSize, $pageNum, $search, $cveName, $tag, $hostGroupId, $activity, $pkgId, $userId, $directlyAssignedToUser);
    }

    /**
     * Get hosts count
     */
    public function getHostsCount($search = null, $cveName = null, $tag = null, $hostGroupId = -1, $activity = null, $pkgId = -1, $userId = -1)
    {
        Utils::log(LOG_DEBUG, "Getting hosts count", __FILE__, __LINE__);
        return sizeof($this->getPakiti()->getDao("Host")->getHostsIds(null, -1, -1, $search, $cveName, $tag, $hostGroupId, $activity, $pkgId, $userId));
    }

    /**
     * Delete host by ID
     */
    public function deleteHost($id)
    {
        Utils::log(LOG_DEBUG, "Delete host[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        return $dao->delete($id);
    }

    /**
     * Recalculate CVEs count for host by ID
     */
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

    /**
     * Recalculate CVEs count for each host
     */
    public function recalculateCvesCountForEachHost()
    {
        Utils::log(LOG_DEBUG, "Recalculating numOfCves for each host", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("Host");
        $ids = $dao->getHostsIds();

        foreach ($ids as $id) {
            $this->recalculateCvesCountForHost($id);
        }
    }
}
