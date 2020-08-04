<?php

namespace App\Subscriber;

use App\Service\ResultService;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Component\Serializer\SerializerInterface;


class ResultSubscriber implements EventSubscriberInterface
{
    private $params;
    private $resultService;
    private $serializer;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, ResultService $resultService, CommongroundService $commonGroundService, SerializerInterface $serializer)
    {
        $this->resultService = $resultService;
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->serializer = $serializer;
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

    }
}
