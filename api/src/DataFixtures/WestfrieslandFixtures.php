<?php

namespace App\DataFixtures;

use App\Entity\Condition;
use App\Entity\Rule;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WestfrieslandFixtures extends Fixture
{
    private $params;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public function load(ObjectManager $manager)
    {
        if (
            !$this->params->get('app_build_all_fixtures') &&
            $this->params->get('app_domain') != 'westfriesland.commonground.nu' && strpos($this->params->get('app_domain'), 'westfriesland.commonground.nu') == false
        ) {
            return false;
        }

        $rule = new Rule();
        $rule->setCode('vcs');
        $rule->setObject('VRC/request');
        $rule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'vcs', 'type'=>'request_conversions']));

        $condition = new Condition();
        $condition->setProperty('action');
        $condition->setValue('UPDATE');
        $condition->setOperation('==');

        $rule->addCondition($condition);

        $condition = new Condition();
        $condition->setProperty('@type');
        $condition->setValue('Request');
        $condition->setOperation('==');

        $rule->addCondition($condition);

        $manager->persist($rule);

        $manager->flush();

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

        $assentRule = new Rule();
        $assentRule->setCode('ias1');
        $assentRule->setObject('IRC/assent');
        if ($this->params->get('app_env') == 'prod') {
            $assentRule->setServiceEndpoint('https://westfriesland.commonground.nu/api/v1/ias/web_hooks');
        } else {
            $assentRule->setServiceEndpoint('https://dev.westfriesland.commonground.nu/api/v1/ias/web_hooks');
        }

        $condition = new Condition();
        $condition->setProperty('@type');
        $condition->setValue('Assent');
        $condition->setOperation('==');

        $assentRule->addCondition($condition);

        $condition = new Condition();
        $condition->setProperty('action');
        $condition->setValue('CREATE');
        $condition->setOperation('==');

        $assentRule->addCondition($condition);

        $manager->persist($assentRule);

        $manager->flush();
    }
}
