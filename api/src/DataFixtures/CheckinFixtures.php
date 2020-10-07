<?php

namespace App\DataFixtures;

use App\Entity\Condition;
use App\Entity\Rule;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CheckinFixtures extends Fixture implements DependentFixtureInterface
{
    private $params;
    /**
     * @var CommonGroundService
     */
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public function getDependencies()
    {
        return [
            ZuiddrechtFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        if (
            !$this->params->get('app_build_all_fixtures') &&
            $this->params->get('app_domain') != 'checking.nu' && strpos($this->params->get('app_domain'), 'checking.nu') == false &&
            $this->params->get('app_domain') != 'zuid-drecht.nl' && strpos($this->params->get('app_domain'), 'zuid-drecht.nl') == false
        ) {
            return false;
        }

        // Checkin vrc requests
        $checkInRule = new Rule();
        $checkInRule->setCode('chisRequests');
        $checkInRule->setObject('VRC/request');
        $checkInRule->setServiceEndpoint('http://chis.dev.svc.cluster.local/web_hooks');
        //$checkInRule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'chis', 'type'=>'web_hooks']));

        $condition = new Condition();
        $condition->setProperty('@type');
        $condition->setValue('Request');
        $condition->setOperation('==');

        $checkInRule->addCondition($condition);

        $condition = new Condition();
        $condition->setProperty('requestType');
        if ($this->params->get('app_env') == 'prod') {
            $condition->setValue('https://zuid-drecht.nl/api/v1/vtc/request_types/c328e6b4-77f6-4c58-8544-4128452acc80');
        } else {
            $condition->setValue('https://dev.zuid-drecht.nl/api/v1/vtc/request_types/c328e6b4-77f6-4c58-8544-4128452acc80');
        }

        $condition->setOperation('==');

        $checkInRule->addCondition($condition);

        $manager->persist($checkInRule);

        $manager->flush();

        // Checkin chin checkins
        $checkInRule = new Rule();
        $checkInRule->setCode('chisCheckins');
        $checkInRule->setObject('CHIN/checkin');
        $checkInRule->setServiceEndpoint('http://chis.dev.svc.cluster.local/web_hooks');
        //$checkInRule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'chis', 'type'=>'web_hooks']));

        $condition = new Condition();
        $condition->setProperty('@type');
        $condition->setValue('Checkin');
        $condition->setOperation('==');

        $checkInRule->addCondition($condition);

        $manager->persist($checkInRule);

        $manager->flush();
    }
}
