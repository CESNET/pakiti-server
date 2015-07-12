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

# Global configuration file

final class Config {

  # Pakiti operational mode
  #   1 - Synchronous mode - process clients reports immediatelly, useful for small deployments with < 1000 hosts
  #   2 - Asynchronous mode - process clients reports from the queue, needed in the deployments with > 1000 hosts
  public static $FEEDER_MODE = 1;
  
  # MySQL database configuration
  public static $DB_HOST = "localhost";
  public static $DB_USER = "mysql_user";
  public static $DB_PASSWORD = "secret_passwor";
  public static $DB_NAME = "pakiti3";
  
  # Enable authorization?
  public static $ENABLE_AUTHZ = 1;
  # Debug
  public static $DEBUG = TRUE;
  
  # Directory, where to put the reports (only applied for asynchronous mode)
  public static $REPORTS_DIR = "/var/tmp/pakiti-reports/";
  # Should be report compressed?
  public static $COMPRESS_REPORTS = 1;

  # If CERN reports are used, path to the private key must be defined, in order to decrypt incomming report
  public static $CERN_REPORT_DECRYPTION_KEY = "/etc/ssl/cert.pem";
  
  # Do we want backup the reports
  public static $BACKUP = TRUE;
  public static $BACKUP_DIR = "/var/log/pakitiv3-reports/";

  # Proxy authentication mode (hostname | ip | x509)
  public static $PROXY_AUTHENTICATION_MODE = "hostname";
  
  # Allowed proxies. Depends on the authentication mode, it should be list of hostnames|ips|X509 Subjects
  public static $PROXY_ALLOWED_PROXIES = array ( 
					    "ukko.ics.muni.cz",
					    "poros.ics.muni.cz",
                                         );
                                         
  # Enable - 1/Disable - 0 outgoing proxy for accessing remote repositories and OVAL definitions
  public static $ENABLE_OUTGOING_PROXY = 0;
  public static $OUTGOING_PROXY = "tcp://proxy.example.com:3128";
  
  # List of packages which will be ignored by Pakiti
  public static $IGNORE_PACKAGES = array (
                                      "kernel-headers",
                                      "kernel-debug",
                                      "kernel-source",
                                   );
  # Also the ignore list, but using REGEXP                                   
  public static $IGNORE_PACKAGES_PATTERNS = array (
                                      ".*-devel$",
                                      ".*-doc$",
                                   );
                                   
  # Packages names which represents kernels
  public static $KERNEL_PACKAGES_NAMES = array (
                                            "kernel", 
                                            "kernel-devel", 
                                            "kernel-smp", 
                                            "kernel-smp-devel", 
                                            "kernel-xenU", 
                                            "kernel-xenU-devel", 
                                            "kernel-largesmp", 
                                            "kernel-largesmp-devel", 
                                            "kernel-xen", 
                                            "kernel-PAE", 
                                            "kernel-hugemem",
                                            "linux-image-generic",
                                            "linux-image-2.4",
                                            "linux-image-2.6",
                                         );
}

?>
