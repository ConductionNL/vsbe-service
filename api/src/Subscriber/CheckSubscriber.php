<?php

namespace App\Subscriber;

use App\Service\CheckService;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Component\Serializer\SerializerInterface;


class CheckSubscriber implements EventSubscriberInterface
{
    private $params;
    private $checkService;
    private $serializer;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CheckService $checkService, CommongroundService $commonGroundService, SerializerInterface $serializer)
    {
        $this->checkService = $checkService;
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
        $check = $event->getControllerResult();

    }
}
