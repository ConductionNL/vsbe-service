<?php

namespace App\Service;

use App\Entity\Result;
use App\Entity\Rule;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;

class ResultService
{
    private $em;
    private $commonGroundService;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService)
    {
        $this->em = $em;
        $this->commonGroundService = $commonGroundService;
    }

    public function performChecks(Result $result)
    {
        $rules = $this->em->getRepository('App:Rule')->findAll();
        foreach ($rules as $rule) {
            if ($rule instanceof Rule) {
                $object = $this->commonGroundService->getResource($result->getObject());
                $resource[strtolower($object['@type'])] = $result->getObject();

                if($rule->getProperty() == 'action'){
                    $value = $result->getAction();
                }else{
                    $property = explode('.', $rule->getProperty());
                    $value = $this->recursiveGetValue($property, $object);
                }



                switch ($rule->getOperation()) {
                    case '<=':
                        if ($value <= $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '>=':
                        if ($value >= $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '<':
                        if ($value < $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '>':
                        if ($value > $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '<>':
                        if ($value <> $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '!=':
                        if ($value != $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case 'exists':
                        if ($value) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    default:
                        if ($value == $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;

                }
            }
        }

        return $result;
    }

    public function runServices(Rule $rule, Result $result, $resource): Result
    {
        $res = $this->commonGroundService->createResource($resource, $rule->getServiceEndpoint());

        $uris = $result->getUris();
        $uris[] = $res['result'];

        $result->setUris($uris);
        $result->addRule($rule);

        return $result;
    }

    public function recursiveGetValue(array $property, array $resource){
        $sub = array_shift($property);
        $value = null;
        if(
            key_exists($sub,$resource) &&
            is_array($resource[$sub])
        )
        {
            $value = $this->recursiveGetValue($property, $resource[$sub]);
        }
        elseif(key_exists($sub, $resource))
        {
            $value = $resource[$sub];
        }
        return $value;
    }
}
