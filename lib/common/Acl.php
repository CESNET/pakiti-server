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

class Acl {
  public static $PAKITI_ADMIN = 1;
  public static $HOSTGROUP_ADMIN = 2;
  public static $HOSTGROUP_VIEWER = 3;
  public static $HOST_ADMIN = 4;
  public static $HOST_VIEWER = 5;
  public static $ANONYMOUS = 6;
  
  private $_userIdentity;
  private $_roles = array();
  
  public function __construct() {

    // Get the user identity, try REMOTE_USER and SSL_CLIENT_S_DN
    if (array_key_exists('REMOTE_USER', $_SERVER)) {
      $this->_userIdentity = $_SERVER['REMOTE_USER'];
    } else if (array_key_exists('SSL_CLIENT_S_DN', $_SERVER)) {
      $this->userIdentity = $_SERVER['SSL_CLIENT_S_DN'];
    } else {
      $this->userIdentity = null;
    }
    
    $this->userIdentity = trim($this->_userIdentity);
    
    if ($this->userIdentity != null && Config::$ENABLE_AUTHZ) {
      // Initialize user's roles
      $this->initRoles();
    }
  }
  
  public function getUserIdentity() {
    return $this->_userIdentity;
  }
  
  public function getRoles() {
    return $this->_roles;
  }
  
  public function isPakitiAdmin() {
  }
  
  public function isHostGroupAdmin(HostGroup $hostGroup) {
  }
  
  public function isHostGroupViewer(HostGroup $hostGroup) {
  }
  
  public function isHostAdmin(Host $host) {
  }
  
  public function isHostViewer(Host $host) {
  }
  
  protected function initRoles() {
    // Here we will iterate through all acl modules in the directory lib/modules/acl/ and try to get info about user's roles
    //TODO 
  }
}