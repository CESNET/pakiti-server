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
 */
class Source extends VdsSource
{
    private $_pakiti;
    private $_subSources;

    public function __construct(Pakiti &$pakiti)
    {
        $this->_pakiti =& $pakiti;
        $this->_subSources = array();
    }

    public function init()
    {
        # Load all CVE sources
        $this->loadSubSources();
    }

    /**
     * Get the name of this class.
     */
    public function getClassName()
    {
        return get_class();
    }

    /**
     * Returns all registred subsources
     */
    public function getSubSources()
    {
        Utils::log(LOG_DEBUG, "Getting all subsources", __FILE__, __LINE__);
        return $this->_subSources;
    }

    /**
     * Get CVE source by Id
     */
    public function getSubSourceById($id)
    {
        Utils::log(LOG_DEBUG, "Getting subsource by ID [id=$id]", __FILE__, __LINE__);
        foreach ($this->_subSources as &$subSource) {
            if ($subSource->getId() == $id) {
                return $subSource;
            }
        }
    }

    /**
     * Checks if the particular Cve source is registered in the DB (VdsSubSource table) and load it.
     */
    protected function loadSubSources()
    {
        Utils::log(LOG_DEBUG, "Loading CVE sources", __FILE__, __LINE__);
        # List all files in the sources directory, each file represents submodule
        if (!file_exists(realpath(dirname(__FILE__)) . '/../sources/' . $this->getName() . 'SubSources/')) {
            mkdir(realpath(dirname(__FILE__)) . '/../sources/' . $this->getName() . 'SubSources/');
        }

        $dir = realpath(dirname(__FILE__)) . '/../sources/' . $this->getName() . 'SubSources/';
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                # Load only files and ommit the OvalSourceInterface
                if (is_file($dir.$file) && preg_match('/.php$/i', $file)) {
                    require_once($dir.$file);

                # Get the filename and extension, filename represent the class name
                $className = preg_replace('/.php$/i', '', $file);
                eval("\$subSource = new $className(\$this->_pakiti);");

                # Check if the module is already registered
                if (($id = $this->_pakiti->getManager("DbManager")->queryToSingleValue("select id from VdsSubSource where type='".$this->_pakiti->getManager("DbManager")->escape($subSource->getType())."' and name='".$this->_pakiti->getManager("DbManager")->escape($subSource->getName())."'")) == null) {
                    # Module is not registered, so store the name and type into the DB

                    # Start transaction
                    $this->_pakiti->getManager("DbManager")->begin();

                    $this->_pakiti->getManager("DbManager")->query("insert into VdsSubSource set type='".$subSource->getType()."', name='".$subSource->getName()."', vdsSourceId=".$this->getId());

                    $id = $this->_pakiti->getManager("DbManager")->getLastInsertedId();

                    # Commit transaction
                    $this->_pakiti->getManager("DbManager")->commit();
                }
                # Set the submodule ID
                $subSource->setId($id);

                # Finally add the CVE submodule
                array_push($this->_subSources, $subSource);
                }
            }
        }
    }
}
