<?php

/**
 * @author Jakub Mlcak
 */
class UsersManager extends DefaultManager
{
    /**
     * Create if not exist, else update and set id
     * @return false if already exist
     */
    public function storeUser(User $user)
    {
        Utils::log(LOG_DEBUG, "Storing user", __FILE__, __LINE__);
        if ($user == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("User object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("User");
        $user->setId($dao->getIdByUid($user->getUid()));
        if ($user->getId() == -1) {
            # User is missing, so store it
            $dao->create($user);
            $new = true;
        } else {
            $dao->update($user);
        }
        return $new;
    }

    public function getUserByUid($uid)
    {
        Utils::log(LOG_DEBUG, "Getting user by UID", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        $id = $dao->getIdByUid($uid);
        return $dao->getById($id);
    }

    public function getUserIdByUid($uid)
    {
        Utils::log(LOG_DEBUG, "Getting user ID by UID", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->getIdByUid($uid);
    }

    public function getUserById($id)
    {
        Utils::log(LOG_DEBUG, "Getting user by ID", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->getById($id);
    }

    public function getUsers()
    {
        Utils::log(LOG_DEBUG, "Getting users", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        $usersIds = $dao->getIds();

        $users = array();
        foreach ($usersIds as $id) {
            array_push($users, $dao->getById($id));
        }
        return $users;
    }

    public function getUsersCount()
    {
        Utils::log(LOG_DEBUG, "Counting users", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return sizeof($dao->getIds());
    }

    public function deleteUser($id)
    {
        Utils::log(LOG_DEBUG, "Delete user[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->delete($id);
    }

    public function assignHostToUser($userId, $hostId)
    {
        Utils::log(LOG_DEBUG, "Assign host[$hostId] to user[$userId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->assignHostToUser($userId, $hostId);
    }

    public function assignHostGroupToUser($userId, $hostGroupId)
    {
        Utils::log(LOG_DEBUG, "Assign hostGroup[$hostGroupId] to user[$userId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->assignHostGroupToUser($userId, $hostGroupId);
    }

    public function unassignHostToUser($userId, $hostId)
    {
        Utils::log(LOG_DEBUG, "Unassign host[$hostId] to user[$userId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->unassignHostToUser($userId, $hostId);
    }

    public function unassignHostGroupToUser($userId, $hostGroupId)
    {
        Utils::log(LOG_DEBUG, "Unassign hostGroup[$hostGroupId] to user[$userId]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->unassignHostGroupToUser($userId, $hostGroupId);
    }

    public function getHostGroupsAssignedToUser($userId)
    {
	Utils::log(LOG_DEBUG, "Get host groups assigned to user[$userId]", __FILE__, __LINE__);
	$dao = $this->getPakiti()->getDao("User");
	return $dao->getHostGroupsAssignedToUser($userId);
    }
}
