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
class Acl
{
    private $_pakiti;
    private $_user = null;

    public function getPakiti()
    {
        return $this->_pakiti;
    }

    public function __construct(Pakiti &$pakiti)
    {
        $this->_pakiti = &$pakiti;
        if (Config::$AUTHZ_MODE != Constants::$AUTHZ_MODE_NONE) {

            # Get data from env variables
            array_key_exists(Config::$AUTHZ_UID, $_SERVER) ? $uid = $_SERVER[Config::$AUTHZ_UID] : $uid = "";
            array_key_exists(Config::$AUTHZ_NAME, $_SERVER) ? $name = $_SERVER[Config::$AUTHZ_NAME] : $name = "";
            array_key_exists(Config::$AUTHZ_EMAIL, $_SERVER) ? $email = $_SERVER[Config::$AUTHZ_EMAIL] : $email = "";

            # Try to get user from dtb by UID
            $user = $this->getPakiti()->getManager("UsersManager")->getUserByUid($uid);

            # Create or update user if AUTHZ_MODE is auto-create
            if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_AUTOCREATE) {
                if ($user == null) {
                    $user = new User();
                    # If it is first user in dtb set as admin
                    if ($this->getPakiti()->getManager("UsersManager")->getUsersCount() == 0) {
                        $user->setAdmin(true);
                    }
                }
                # Set up user and store only if data was changed or new one
                if ($user->getId() == -1 || $user->getUid() != $uid || $user->getName() != $name || $user->getEmail() != $email) {
                    $user->setUid($uid);
                    $user->setName($name);
                    $user->setEmail($email);
                    $this->getPakiti()->getManager("UsersManager")->storeUser($user);
                }
            }

            $this->_user = $user;
        }
    }

    /**
    * Get user ID
    * @return -1 if AUTHZ_MODE is none or user is admin
    */
    public function getUserId()
    {
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_NONE) {
            return -1;
        }

        # This method shouldn't be called if user is null
        if ($this->_user == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("When AUTHZ_MODE isn't none and user is null, so this method shouldn't be called");
        }

        if ($this->_user->isAdmin()) {
            return -1;
        }

        return $this->_user->getId();
    }

    /**
    * Get user
    * @return User
    */
    public function getUser()
    {
        return $this->_user;
    }

    /**
    * Check if user has permission to source
    * @return true if user has permission to source
    */
    public function permission($source)
    {
        if (Config::$AUTHZ_MODE == Constants::$AUTHZ_MODE_NONE) {
            return true;
        }

        if ($this->_user == null) {
            return false;
        }

        if ($this->_user->isAdmin()) {
            return true;
        }

        return in_array($source, ["hosts", "host", "hostGroups", "hostGroup", "reports", "cve"]);
    }
}
