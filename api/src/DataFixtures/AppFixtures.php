<?php

namespace App\DataFixtures;

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
        $rule->setProperty('action');
        $rule->setValue('CREATE');
        $rule->setOperation('==');
        $rule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'vcs', 'type'=>'request_conversions']));

        $manager->persist($rule);

        $manager->flush();

        if(strpos($this->params->get('app_domain'), 'westfriesland.commonground.nu') !== false ||
            $this->params->get('app_domain') == 'westfriesland.commonground.nu'){
            $wfRule = new Rule();
            $rule->setCode('wfs');
            $rule->setObject('VRC/request');
            $rule->setProperty('properties.gemeente');
            $rule->setValue('');
            $rule->setOperation('exists');
            $rule->setServiceEndpoint($this->commonGroundService->cleanUrl(['component'=>'wfs', 'type'=>'request_conversions']));

            $manager->persist($rule);

            $manager->flush();
        }
    }
}
