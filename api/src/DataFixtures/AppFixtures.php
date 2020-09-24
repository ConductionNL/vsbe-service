<?php

namespace App\DataFixtures;

use App\Entity\Condition;
use App\Entity\Rule;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $params;
    private $encoder;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, UserPasswordEncoderInterface $encoder, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->encoder = $encoder;
        $this->commonGroundService = $commonGroundService;
    }

    public function load(ObjectManager $manager)
    {
        // Lets make sure we only run these fixtures on larping enviroment
        if (
            strpos($this->params->get('app_domain'), 'zuid-drecht.nl') == false &&
            $this->params->get('app_domain') != 'zuid-drecht.nl' &&
            strpos($this->params->get('app_domain'), 'westfriesland.commonground.nu') == false &&
            $this->params->get('app_domain') != 'westfriesland.commonground.nu'
        ) {
            return false;
        }

        $rule = new Rule();
        $rule->setCode('vcs');
        $rule->setObject('VRC/request');
        $rule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'vcs', 'type'=>'request_conversions']));

        $condition = new Condition();
        $condition->setProperty('action');
        $condition->setValue('CREATE');
        $condition->setOperation('==');

        $rule->addCondition($condition);

        $condition = new Condition();
        $condition->setProperty('@type');
        $condition->setValue('Request');
        $condition->setOperation('==');

        $rule->addCondition($condition);

        $manager->persist($rule);

        $manager->flush();

        if (strpos($this->params->get('app_domain'), 'westfriesland.commonground.nu') !== false ||
            $this->params->get('app_domain') == 'westfriesland.commonground.nu') {
            $wfRule = new Rule();
            $wfRule->setCode('wfs');
            $wfRule->setObject('VRC/request');
            $wfRule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'wfs', 'type'=>'request_conversions']));

            $condition = new Condition();
            $condition->setProperty('@type');
            $condition->setValue('Request');
            $condition->setOperation('==');

            $wfRule->addCondition($condition);

            $condition = new Condition();
            $condition->setProperty('properties.gemeente');
            $condition->setValue('');
            $condition->setOperation('exists');

            $wfRule->addCondition($condition);

            $condition = new Condition();
            $condition->setProperty('organization');
            $condition->setValue('resourceValue:properties.gemeente');
            $condition->setOperation('!=');

            $wfRule->addCondition($condition);

            $manager->persist($wfRule);

            $begrafenisRule = new Rule();
            $begrafenisRule->setCode('bgs');
            $begrafenisRule->setObject('VRC/request');
            $begrafenisRule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'bgs', 'type'=>'web_hooks']));

            $condition = new Condition();
            $condition->setProperty('@type');
            $condition->setValue('Request');
            $condition->setOperation('==');

            $begrafenisRule->addCondition($condition);

            $condition = new Condition();
            $condition->setProperty('requestType');

            if ($this->params->get('app_env') == 'prod') {
                $condition->setValue('https://vtc.westfriesland.commonground.nu/request_types/c2e9824e-2566-460f-ab4c-905f20cddb6c');
            } else {
                $condition->setValue('https://vtc.dev.westfriesland.commonground.nu/request_types/c2e9824e-2566-460f-ab4c-905f20cddb6c');
            }

            $condition->setOperation('==');

            $begrafenisRule->addCondition($condition);

            $condition = new Condition();
            $condition->setProperty('properties.begraafplaats');
            $condition->setOperation('exists');
            $condition->setValue('');

            $begrafenisRule->addCondition($condition);

            $condition = new Condition();
            $condition->setProperty('properties.datum');
            $condition->setOperation('exists');
            $condition->setValue('');

            $begrafenisRule->addCondition($condition);

            $manager->persist($begrafenisRule);

            $manager->flush();
        }
        if (strpos($this->params->get('app_domain'), 'zuid-drecht.nl') !== false ||
            $this->params->get('app_domain') == 'zuid-drecht.nl') {
            $tsRule = new Rule();
            $tsRule->setCode('ts');
            $tsRule->setObject('VRC/request');
            $tsRule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'ts', 'type'=>'web_hooks']));

            $condition = new Condition();
            $condition->setProperty('@type');
            $condition->setValue('Request');
            $condition->setOperation('==');

            $tsRule->addCondition($condition);

            $condition = new Condition();
            $condition->setProperty('requestType');
            $condition->setValue($this->commonGroundService->cleanUrl(['component'=>'vtc', 'type'=>'request_types', 'id'=>'d0badfff-1c90-4ddb-80fc-49842d806eaa']));
            $condition->setOperation('==');

            $tsRule->addCondition($condition);

            $manager->persist($tsRule);

            $manager->flush();

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
                $condition->setValue('https://vtc.zuid-drecht.nl/request_types/c328e6b4-77f6-4c58-8544-4128452acc80');
            } else {
                $condition->setValue('https://vtc.dev.zuid-drecht.nl/request_types/c328e6b4-77f6-4c58-8544-4128452acc80');
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
}
