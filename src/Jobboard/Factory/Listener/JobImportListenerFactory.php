<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */
  
/** */
namespace Jobboard\Factory\Listener;

use Jobboard\Filter\JobImportCategories;
use Jobboard\Listener\JobImportListener;
use Interop\Container\ContainerInterface;
use Jobboard\Options\JobImportListenerOptions;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for \Jobboard\Listener\JobImportListener
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class JobImportListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $repositories = $container->get('repositories');
        $categories   = $repositories->get('Jobs/Category');
        $employmentTypes = $categories->findOneBy(['value' => 'employmentTypes']);
        $industries   = $categories->findOneBy(['value' => 'industries']);
        $filterOptions = $container->get(JobImportListenerOptions::class);
        $etFilter = new JobImportCategories($filterOptions->getEmploymentTypesMap());
        $inFilter = new JobImportCategories($filterOptions->getIndustriesMap());

        $listener = new JobImportListener([
            'employmentTypes' => $employmentTypes,
            'employmentTypesFilter' => $etFilter,
            'industries' => $industries,
            'industriesFilter' => $inFilter,
            'repositories' => $repositories,
        ]);

        return $listener;
    }
    
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, JobImportListener::class);
    }
}
