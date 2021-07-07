<?php

namespace Oka\Notifier\ServerBundle\Controller;

use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Oka\Notifier\Message\Address;
use Oka\Notifier\Message\Notification;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\AmqpExt\AmqpStamp;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationController
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Create notification
     *
     * @param string $version
     * @param string $protocol
     * @AccessControl(version="v1", protocol="rest", formats="json")
     * @RequestContent(constraints="createConstraints")
     */
    public function create(Request $request, $version, $protocol, array $requestContent): JsonResponse
    {
        foreach ($requestContent['notifications'] as $notification) {
            $attributes = $notification['attributes'] ?? [];
            $sender = Address::create($notification['sender']);
            $receiver = Address::create($notification['receiver']);

            $this->bus->dispatch(
                new Notification($notification['channels'], $sender, $receiver, $notification['message'], $notification['title'] ?? null, $attributes),
                [new AmqpStamp(null, AMQP_NOPARAM, ['delivery_mode' => AMQP_DURABLE, 'priority' => $attributes['priority'] ?? 0])]
            );
        }

        return new JsonResponse(null, 204);
    }

    private static function createConstraints(): Assert\Collection
    {
        $addressConstriants = new Assert\Callback(['callback' => function ($object, ExecutionContextInterface $context, $payload) {
            if (true === is_array($object)) {
                $constraints = new Assert\Collection([
                    'name' => new Assert\Optional(new Assert\NotBlank()),
                    'value' => new Assert\Required(new Assert\NotBlank())
                ]);
            } else {
                $constraints = new Assert\NotBlank();
            }

            $validator = $context->getValidator()->inContext($context);
            $validator->validate($object, $constraints);
        }]);

        return new Assert\Collection([
            'notifications' => new Assert\All(
                new Assert\Collection([
                    'channels' => new Assert\Required(new Assert\All(new Assert\NotBlank())),
                    'sender' => new Assert\Required($addressConstriants),
                    'receiver' => new Assert\Required($addressConstriants),
                    'message' => new Assert\Required(new Assert\NotBlank()),
                    'title' => new Assert\Optional(new Assert\NotBlank()),
                    'attributes' => new Assert\Optional(new Assert\Type(['type' => 'array']))
            ])
            )
        ]);
    }
}
