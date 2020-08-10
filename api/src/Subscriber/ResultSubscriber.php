<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Result;
use App\Service\ResultService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class ResultSubscriber implements EventSubscriberInterface
{
    private $params;
    private $resultService;
    private $serializer;
    private $commonGroundService;
    private $em;

    public function __construct(ParameterBagInterface $params, ResultService $resultService, CommongroundService $commonGroundService, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->resultService = $resultService;
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->serializer = $serializer;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['check', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function Check(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $request = $event->getRequest();

        if ($request->getMethod() == Request::METHOD_POST && $result instanceof Result) {
            $result = $this->resultService->performChecks($result);
            $this->em->persist($result);
            $this->em->flush();
        }
    }
}
