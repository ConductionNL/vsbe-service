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

    public function performChecks(Result $result){
        $rules = $this->em->getRepository('App:Rule')->findAll();
        foreach($rules as $rule){
            if($rule instanceof Rule) {
                $object = $this->commonGroundService->getResource($result->getObject());
                $resource[strtolower($object['@type'])] = $result->getObject();

                switch ($rule->getOperation()) {
                    case '<=':
                        if ($object[$rule->getProperty()] <= $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '>=':
                        if ($object[$rule->getProperty()] >= $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '<':
                        if ($object[$rule->getProperty()] < $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '>':
                        if ($object[$rule->getProperty()] > $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '<>':
                        if ($object[$rule->getProperty()] <> $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    case '!=':
                        if ($object[$rule->getProperty()] != $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;
                    default:
                        if ($object[$rule->getProperty()] == $rule->getValue()) {
                            $result = $this->runServices($rule, $result, $resource);
                        }
                        break;

                }
            }
        }
        return $result;
    }
    public function runServices(Rule $rule, Result $result, $resource) :Result
    {
        $res = $this->commonGroundService->createResource($resource, $rule->getServiceEndpoint());

        $uris = $result->getUris();
        $uris[] = $res['@id'];

        $result->setUris($uris);
        $result->addRule($rule);
        return $result;
    }
}
