<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace Jobboard\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class JobImportListenerOptions extends AbstractOptions
{
    /**
     *
     *
     * @var array
     */
    private $employmentTypesMap = [];

    /**
     *
     *
     * @var array
     */
    private $industriesMap = [];

    /**
     * @return array
     */
    public function getEmploymentTypesMap()
    {
        return $this->employmentTypesMap;
    }

    /**
     * @param array $employmentTypesMap
     *
     * @return self
     */
    public function setEmploymentTypesMap($employmentTypesMap)
    {
        $this->employmentTypesMap = $employmentTypesMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getIndustriesMap()
    {
        return $this->industriesMap;
    }

    /**
     * @param array $industriesMap
     *
     * @return self
     */
    public function setIndustriesMap($industriesMap)
    {
        $this->industriesMap = $industriesMap;

        return $this;
    }


    
}
