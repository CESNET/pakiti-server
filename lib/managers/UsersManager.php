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
class UsersManager extends DefaultManager
{
    /**
    * Create if not exist, else update and set id
    * @return false if already exist
    */
    public function storeUser(User &$user)
    {
        Utils::log(LOG_DEBUG, "Storing user", __FILE__, __LINE__);
        if ($user == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("User object is not valid");
        }

        $new = false;
        $dao = $this->getPakiti()->getDao("User");
        $user->setId($dao->getUserIdByUid($user->getUid()));
        if ($user->getId() == -1) {
            # User is missing, so store it
            $dao->createUser($user);
            $new = true;
        } else {
            $dao->updateUser($user);
        }
        return $new;
    }

    public function getUserByUid($uid)
    {
        Utils::log(LOG_DEBUG, "Getting user by UID", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        $id = $dao->getUserIdByUid($uid);
        return $dao->getUserById($id);
    }

    public function getUserIdByUid($uid)
    {
        Utils::log(LOG_DEBUG, "Getting user ID by UID", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->getUserIdByUid($uid);
    }

    public function getUserById($id)
    {
        Utils::log(LOG_DEBUG, "Getting user by ID", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->getUserById($id);
    }

    public function getUsers()
    {
        Utils::log(LOG_DEBUG, "Getting users", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        $usersIds = $dao->getUsersIds();

        $users = array();
        foreach ($usersIds as $id) {
            array_push($users, $dao->getUserById($id));
        }
        return $users;
    }

    public function getUsersCount()
    {
        Utils::log(LOG_DEBUG, "Counting users", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return sizeof($dao->getUsersIds());
    }

    public function deleteUser($id)
    {
        Utils::log(LOG_DEBUG, "Delete user[$id]", __FILE__, __LINE__);
        $dao = $this->getPakiti()->getDao("User");
        return $dao->deleteUser($id);
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
}
