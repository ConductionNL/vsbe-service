<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Component\Serializer\SerializerInterface;


class WebHookSubscriber implements EventSubscriberInterface
{
    private $params;

    private $serializer;
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommongroundService $commonGroundService, SerializerInterface $serializer)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
        $this->serializer = $serializer;
    }
}
