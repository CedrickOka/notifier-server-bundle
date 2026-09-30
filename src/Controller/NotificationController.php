<?php

namespace Oka\Notifier\ServerBundle\Controller;

use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Oka\Notifier\Message\Notification;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class NotificationController
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    /**
     * Create a notification.
     *
     * @param string $version
     * @param string $protocol
     */
    #[AccessControl(version: 'v1', protocol: 'rest', formats: ['json'])]
    #[RequestContent(constraints: 'createConstraints')]
    public function create(Request $request, $version, $protocol, array $requestContent): JsonResponse
    {
        foreach ($requestContent['notifications'] as $notification) {
            $this->bus->dispatch(
                Notification::create($notification),
                [new AmqpStamp(null, AMQP_NOPARAM, ['delivery_mode' => AMQP_DURABLE])]
            );
        }

        return new JsonResponse(null, 204);
    }

    private static function createConstraints(): Assert\Collection
    {
        $addressConstraints = new Assert\Callback(function ($object, ExecutionContextInterface $context, $payload) {
            if (true === is_array($object)) {
                $constraints = new Assert\Collection(fields: [
                    'name' => new Assert\Optional(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)])),
                    'value' => new Assert\Required(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)])),
                ]);
            } else {
                $constraints = new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)]);
            }

            $validator = $context->getValidator()->inContext($context);
            $validator->validate($object, $constraints);
        });

        return new Assert\Collection(fields: [
            'notifications' => new Assert\All(
                new Assert\Collection(fields: [
                    'channels' => new Assert\Required(new Assert\All(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)]))),
                    'sender' => new Assert\Required($addressConstraints),
                    'receiver' => new Assert\Required($addressConstraints),
                    'message' => new Assert\Required(new Assert\NotBlank()),
                    'title' => new Assert\Optional(new Assert\NotBlank()),
                    'attributes' => new Assert\Optional(new Assert\Collection(fields: [], allowExtraFields: true)),
                ])
            ),
        ]);
    }
}
