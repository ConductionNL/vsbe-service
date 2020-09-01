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

    public function checkConditions(Rule $rule, Result $result, array $object) : bool
    {
        $results = [];
        foreach($rule->getConditions() as $condition){
            if($condition->getProperty() == 'action'){
                $value = $result->getAction();
            }else{
                $property = explode('.', $condition->getProperty());
                $value = $this->recursiveGetValue($property, $object);
            }
            switch ($condition->getOperation()) {
                case '<=':
                    if ($value <= $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                case '>=':
                    if ($value >= $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                case '<':
                    if ($value < $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                case '>':
                    if ($value > $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                case '<>':
                    if ($value <> $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                case '!=':
                    if ($value != $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                case 'exists':
                    if ($value) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;
                default:
                    if ($value == $condition->getValue()) {
                        $results[] = true;
                    } else {
                        $results[] = false;
                    }
                    break;

            }
        }
        foreach($results as $result){
            if(!$result){
                return false;
            }
        }
        return true;
    }

    public function performChecks(Result $result)
    {
        $rules = $this->em->getRepository('App:Rule')->findAll();
        foreach ($rules as $rule) {
            if ($rule instanceof Rule) {
                $object = $this->commonGroundService->getResource($result->getObject());
                $resource['resource'] = $result->getObject();
                $resource[strtolower($object['@type'])] = $resource['resource'];

                if($this->checkConditions($rule, $result, $object)){
                    $this->runServices($rule, $result, $resource);
                }

            }
        }

        return $result;
    }

    public function runServices(Rule $rule, Result $result, $resource): Result
    {
        $res = $this->commonGroundService->createResource($resource, $rule->getServiceEndpoint());

        $uris = $result->getUris();
        if($uris != null){
            $uris[] = $res['result'];
        } else {
            $uris = [];
            $uris[] = $res['result'];
        }

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
