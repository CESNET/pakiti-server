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

class SubSourceDef implements ISubSourceDef {
  private $_id = -1;
  private $_name;
  private $_uri;
  private $_enabled;
  private $_lastChecked;
  private $_subSourceId = -1;
  
  public function __construct() {
  }

  public function getId() {
    return $this->_id;
  }
  
  public function getName() {
    return $this->_name;
  }
  
  public function getUri() {
    return $this->_uri;
  }
  
  public function getEnabled() {
    return $this->_enabled;
  }
  
  public function getLastChecked() {
    return $this->_lastChecked;
  }
  
  public function getSubSourceId() {
    return $this->_subSourceId;
  }
  
  public function setId($val) {
    $this->_id = $val;
  }
  
  public function setName($val) {
    $this->_name = $val;
  }
  
  public function setUri($val) {
    $this->_uri = $val;
  }
  
  public function setEnabled($val) {
    $this->_enabled = $val;
  }
  
  public function setLastChecked($val) {
    $this->_lastChecked = $val;
  }
  
  public function setSubSourceId($val) {
    $this->_subSourceId = $val;
  }
  
}
