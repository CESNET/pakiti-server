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

# DO NOT MODIFY THESE CONSTANTS!

final class Constants {
  public static $PAKITI_VERSION = "3.0.0";
  
  public static $FEEDER_SYNCHRONOUS_MODE = 1;
  public static $FEEDER_ASYNCHRONOUS_MODE = 2;
  
  public static $RETURN_OK = "OK";
  public static $RETURN_ERROR = "ERROR";
  
  public static $NA = "N/A";
  
  public static $ENABLED = 1;
  public static $DISABLED = 0;
  
  public static $OS_NAMES_DEFINITIONS = array ( "sl-release"     => "Scientific Linux",
                                              "redhat-release" => "Red Hat Linux",
                                              "sles-release"   => "SUSE Linux",
                                              "hpc-release"    => "HPC Linux",
                                              "centos-release" => "CentOS Linux",
                                              "fedora-release" => "Fedora Linux",
                                              "redhat-release-server" => "Red Hat Linux Server",
                                              "redhat-release-client" => "Red Hat Linux Client",
                                              "redhat-release-workstation" => "Red Hat Linux Workstation",
                                              "redhat-release-computenode" => "Red Hat Linux ComputeNode",
                                             );
                                             
  # OS names mapping, used for cannonization of the OS name sent by the client. ? will be replaced by the version/release.
  public static $OS_NAMES_MAPPING = array ( 'ScientificSL ([\w.-]+)' => 'Scientific Linux ${1}',
					    'Scientific ([\w.-]+)' => 'Scientific Linux ${1}',
					    'ScientificCERNSLC ([\w.-]+)' => 'Scientific Linux ${1}.cern',
					    'RedHatEnterpriseServer ([\w.-]+)' => 'Red Hat Linux Server ${1}',
					    'Scientific Linux SL release ([\w.-]+) .*' => 'Scientific Linux ${1}',
					    'Scientific Linux CERN SLC release ([\w.-]+) .*' => 'Scientific Linux ${1}.cern',
					    'Ubuntu ([\w.-]+)' => 'Ubuntu ${1}',
					    'CentOS ([\w.-]+)' => 'CentOS Linux ${1}',
					    'CentOS release ([\w.-]+) .*' => 'CentOS Linux ${1}',
					    'Fedora ([\w.-]+)' => 'Fedora Linux ${1}',
					    'SUSE LINUX ([\w.-]+)' => 'SUSE Linux ${1}',
					    'Debian ([\w.-\/]+)' => 'Debian ${1}',
					    );

  # Where to put OS names which do not have any mapping
  public static $UNKNOWN_OS_NAMES_FILE = "/tmp/pakiti-unknownOses.txt";

  # Entries names from the report                                           
  public static $REPORT_HOSTNAME = "host";
  public static $REPORT_IP = "ip";
  public static $REPORT_OS = "os";
  public static $REPORT_ARCH = "arch";
  public static $REPORT_KERNEL = "kernel";
  public static $REPORT_SITE = "site";
  public static $REPORT_TAG = "tag";
  public static $REPORT_VERSION = "version";
  public static $REPORT_REPORT = "report";
  public static $REPORT_PROXY = "proxy";
  public static $REPORT_TYPE = "type";
  public static $REPORT_PKGS = "pkgs";
  public static $REPORT_REPORTER_IP = "reporterIp";
  public static $REPORT_REPORTER_HOSTNAME = "reporterHostname";
  public static $REPORT_TIMESTAMP = "timestamp";
  public static $REPORT_LAST_HEADER_HASH = "lastReportHeaderHash";
  public static $REPORT_LAST_PKGS_HASH = "lastReportPkgsHash";
  
  # If the server will send the report back to the client
  public static $SEND_REPORT = 1;
  public static $DO_NOT_SEND_REPORT = 0;
  
  # Is the reporting host a proxy?
  public static $HOST_IS_PROXY = 1;
  public static $HOST_IS_NOT_PROXY = 0;
  public static $PROXY_AUTHN_MODE_HOSTNAME = 'hostname';
  public static $PROXY_AUTHN_MODE_IP = 'ip';
  public static $PROXY_AUTHN_MODE_x509 = 'x509';
  
  # SSL related variables
  public static $SSL_CLIENT_SUBJECT = 'SSL_CLIENT_S_DN';
  
  # Type of the packager system (dpkg/rpm)
  public static $PACKAGER_SYSTEM_RPM = 'rpm';
  public static $PACKAGER_SYSTEM_DPKG = 'dpkg';
  
  # Does the host sending also its own repositories definitions?
  public static $OWN_REPOSITORIES_DEF = 1;
}
?>
