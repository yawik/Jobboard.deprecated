<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace Jobboard\Filter;

use Zend\Filter\Exception;
use Zend\Filter\FilterInterface;

/**
 * ${CARET}
 * 
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test 
 */
class JobImportCategories implements FilterInterface
{
    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function filter($value)
    {
        $return = [];

        foreach ($value as $key) {
            $return[] = isset($this->map[$key]) ? $this->map[$key] : $value;
        }

        return $return;
    }
}
