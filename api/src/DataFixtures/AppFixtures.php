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

        if(strpos($this->params->get('app_domain'), 'westfriesland.commonground.nu') !== false ||
            $this->params->get('app_domain') == 'westfriesland.commonground.nu'){
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

            $manager->persist($wfRule);

            $manager->flush();
        }
        if(strpos($this->params->get('app_domain'), 'zuid-drecht.nl') !== false ||
            $this->params->get('app_domain') == 'zuid-drecht.nl')
        {
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
        }
    }
}
