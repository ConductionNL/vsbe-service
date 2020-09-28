<?php

namespace App\DataFixtures;

use App\Entity\Condition;
use App\Entity\Rule;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ZuiddrechtFixtures extends Fixture
{
    private $params;
    /**
     * @var CommonGroundService
     */
    private $commonGroundService;

    public const ORGANIZATION_ZUIDDRECHT = 'organization-zuiddrecht';

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public function load(ObjectManager $manager)
    {
        if (
            !$this->params->get('app_build_all_fixtures') &&
            $this->params->get('app_domain') != 'zuiddrecht.nl' && strpos($this->params->get('app_domain'), 'zuiddrecht.nl') == false &&
            $this->params->get('app_domain') != 'zuid-drecht.nl' && strpos($this->params->get('app_domain'), 'zuid-drecht.nl') == false
        ) {
            return false;
        }

//        $rule = new Rule();
//        $rule->setCode('vcs');
//        $rule->setObject('VRC/request');
//        $rule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'vcs', 'type'=>'request_conversions']));
//
//        $condition = new Condition();
//        $condition->setProperty('action');
//        $condition->setValue('CREATE');
//        $condition->setOperation('==');
//
//        $rule->addCondition($condition);
//
//        $condition = new Condition();
//        $condition->setProperty('@type');
//        $condition->setValue('Request');
//        $condition->setOperation('==');
//
//        $rule->addCondition($condition);
//
//        $manager->persist($rule);
//
//        $manager->flush();

        $tsRule = new Rule();
        $tsRule->setCode('ts');
        $tsRule->setObject('VRC/request');
        $tsRule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component' => 'ts', 'type' => 'web_hooks']));

        $condition = new Condition();
        $condition->setProperty('@type');
        $condition->setValue('Request');
        $condition->setOperation('==');

        $tsRule->addCondition($condition);

        $condition = new Condition();
        $condition->setProperty('requestType');
        $condition->setValue($this->commonGroundService->cleanUrl(['component' => 'vtc', 'type' => 'request_types', 'id' => 'd0badfff-1c90-4ddb-80fc-49842d806eaa']));
        $condition->setOperation('==');

        $tsRule->addCondition($condition);

        $manager->persist($tsRule);

        $manager->flush();
    }
}
