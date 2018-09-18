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

require(realpath(dirname(__FILE__)) . '/include/ISource.php');
require(realpath(dirname(__FILE__)) . '/include/ISubSource.php');

/**
 * @author Michal Prochazka
 */
class VdsModule extends DefaultModule
{
    private $_sources;
    private $_pakiti;

    public function __construct(Pakiti $pakiti)
    {
        parent::__construct($pakiti);
        $this->_pakiti = $pakiti;
        $this->_sources = array();
        # Load all VDS sources
        $this->loadSources();
    }

    /**
     * Synchronize all vulnerability definition sources 
     */
    public function synchronize()
    {
        Utils::log(LOG_DEBUG, "Synchronizing VDS", __FILE__, __LINE__);
        foreach ($this->_sources as $source) {
            $vulnerabilities = $source->retrieveVulnerabilities();
            if ($vulnerabilities) {
                $this->_pakiti->getManager("VulnerabilitiesManager")->storeVulnerabilities($vulnerabilities);
            }
        }
    }

    /**
     * Get all registered sources
     */
    public function getSources()
    {
        Utils::log(LOG_DEBUG, "Getting all VDS sources", __FILE__, __LINE__);
        return $this->_sources;
    }

    /**
     * Get source by Id
     */
    public function getSourceById($id)
    {
        Utils::log(LOG_DEBUG, "Getting VDS source by id [id=$id]", __FILE__, __LINE__);
        # We have to provide also Pakiti object, because constructor of the VDS source requires id
        foreach ($this->_sources as $source) {
            if ($source->getId() == $id) {
                return $source;
            }
        }
    }

    /**
     * Get SubSource by id
     */
    public function getSubSourceById($id)
    {
        Utils::log(LOG_DEBUG, "Getting VDS subSource by id [id=$id]", __FILE__, __LINE__);
        $sourceObject = new Source($this->getPakiti());
        return $sourceObject->getSubSourceById($id);
    }

    /**
     * Get SubSourceDef by id
     */
    public function getSubSourceDefById($id)
    {
        Utils::log(LOG_DEBUG, "Getting VDS subSourceDef by id [id=$id]", __FILE__, __LINE__);
        $subSourceObject = new SubSource($this->getPakiti());
        return $subSourceObject->getSubSourceDefById($id);
    }


    /**
     * Load all VDS sources located in sources directory
     */
    protected function loadSources()
    {
        Utils::log(LOG_DEBUG, "Loading VDS sources", __FILE__, __LINE__);
        # List all files in the sources directory, each file represents source
        $dir = realpath(dirname(__FILE__))."/sources/";
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (is_file($dir.$file) && preg_match('/.php$/i', $file)) {
                    require($dir.$file);
                    # Get the filename and extension, filename represent the class name
                    $classname = preg_replace('/.php$/i', '', $file);

                    $source = new $classname($this->getPakiti());

                    # Register source if it was not registered before
                    if (!$this->isSourceRegistered($source)) {
                        $this->registerSource($source);
                    }

                    # Initialize module (it will load subSources)
                    $source->init();

                    # Finally add the VDS source
                    array_push($this->_sources, $source);

                    Utils::log(LOG_DEBUG, "VDS source loaded [name=" . $source->getName() . "]", __FILE__, __LINE__);
                }
            }
        }
    }

    /**
     * Register the VDS source, but firstly check if the source hasn't been already registered. 
     * Enclose the operation with the transaction.
     */
    protected function registerSource(ISource $source)
    {
        if ($source == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("source is null");
        }

        Utils::log(LOG_DEBUG, "Registering VDS source [name=" . $source->getName() . "]", __FILE__, __LINE__);

        $this->getPakiti()->getManager("DbManager")->begin();
        if (($id = $this->getPakiti()->getDao("VdsSource")->getIdByName($source->getName())) != -1) {
            $source->setId($id);
        } else {
            $this->getPakiti()->getDao("VdsSource")->create($source);
        }
        $this->getPakiti()->getManager("DbManager")->commit();
    }

    /**
     * Check if the source has been registered.
     */
    protected function isSourceRegistered(ISource $source)
    {
        if ($source == null) {
            Utils::log(LOG_ERR, "Exception", __FILE__, __LINE__);
            throw new Exception("source is null");
        }
        Utils::log(LOG_DEBUG, "Checking if the VDS source is registered [name=" . $source->getName() . "]", __FILE__, __LINE__);

        if (($id = $this->getPakiti()->getDao("VdsSource")->getIdByName($source->getName())) != -1) {
            $source->setId($id);
            return true;
        }
        return false;
    }
}

