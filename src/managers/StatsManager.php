<?php

/**
 * @author Jakub Mlcak
 */
class StatsManager extends DefaultManager
{
    public function get($name)
    {
        return $this->getPakiti()->getDao("Stat")->get($name);
    }

    public function listAll()
    {
        return $this->getPakiti()->getDao("Stat")->listAll();
    }

    public function add($name, $number)
    {
        $stat = $this->getPakiti()->getDao("Stat")->get($name);
        if ($stat == null) {
            $stat = new Stat();
            $stat->setName($name);
            $stat->setValue($number);
            $stat = $this->getPakiti()->getDao("Stat")->create($stat);
        } else {
            $value = $stat->getValue();
            $stat->setValue($value + $number);
            $stat = $this->getPakiti()->getDao("Stat")->update($stat);
        }
    }

    public function remove($name)
    {
        return $this->getPakiti()->getDao("Stat")->delete($name);
    }
}
