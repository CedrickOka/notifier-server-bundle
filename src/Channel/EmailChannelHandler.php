<?php

namespace Oka\Notifier\ServerBundle\Channel;

use Oka\Notifier\Message\Notification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class EmailChannelHandler implements ChannelHandlerInterface
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function supports(Notification $notification): bool
    {
        return in_array(static::getName(), $notification->getChannels(), true);
    }

    public function send(Notification $notification): void
    {
        $email = new Email();
        $email->from(new Address($notification->getSender()->getValue(), $notification->getSender()->getName() ?? ''))
              ->to(new Address($notification->getReceiver()->getValue(), $notification->getReceiver()->getName() ?? ''));

        if (null !== $notification->getTitle()) {
            $email->subject($notification->getTitle());
        }

        $attributes = $notification->getAttributes();

        if (true === isset($attributes['attachments']) && true === is_array($attributes['attachments'])) {
            foreach ($attributes['attachments'] as $key => $value) {
                if (false === isset($value['path']) && false === isset($value['body'])) {
                    continue;
                }

                if (false === $value['inline'] ?? false) {
                    $method = false === isset($value['path']) ? 'attach' : 'attachFromPath';
                    $email->$method($value['path'] ?? base64_decode($value['body']), $value['name'] ?? null, $value['contentType'] ?? null);
                } else {
                    $method = false === isset($value['path']) ? 'embed' : 'embedFromPath';
                    $email->$method($value['path'] ?? base64_decode($value['body']), $value['name'] ?? null, $value['contentType'] ?? null);
                }

                if (true === isset($value['body'])) {
                    $attributes['attachments'][$key]['body'] = '[TRUNCATED]';
                }
            }

            $notification->setAttributes($attributes);
        }

        if (true === isset($attributes['headers']) && true === is_array($attributes['headers'])) {
            $headers = $email->getHeaders();

            foreach ($attributes['headers'] as $name => $value) {
                $headers->addHeader($name, $value);
            }
        }

        if ('text' === ($attributes['bodyFormat'] ?? 'text')) {
            $email->text($notification->getMessage());
        } else {
            $email->html($notification->getMessage());
        }

        $this->mailer->send($email);
    }

    public static function getName(): string
    {
        return 'email';
    }
}
