<?php

namespace Oka\Notifier\ServerBundle\Controller;

use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Oka\Notifier\ServerBundle\Service\ContactManager;
use Oka\PaginationBundle\Pagination\PaginationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class ContactController
{
    public function __construct(
        private ContactManager $contactManager,
        private PaginationManager $paginationManager,
        private SerializerInterface $serializer,
        private string $paginationManagerName,
    ) {
    }

    /**
     * Retrieve contact list.
     *
     * @param string $version
     * @param string $protocol
     */
    #[AccessControl(version: 'v1', protocol: 'rest', formats: ['json'])]
    public function list(Request $request, $version, $protocol): JsonResponse
    {
        try {
            /** @var \Oka\PaginationBundle\Pagination\Page $page */
            $page = $this->paginationManager->paginate($this->paginationManagerName, $request, [], ['channel' => 'ASC']);
        } catch (\Oka\PaginationBundle\Exception\PaginationException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse(
            $this->serializer->serialize($page->toArray(), 'json', ['groups' => $request->query->has('details') ? ['details'] : ['summary']]),
            $page->getPageNumber() > 1 ? 206 : 200,
            [],
            true
        );
    }

    /**
     * Create or update a contact.
     *
     * @param string $version
     * @param string $protocol
     */
    #[AccessControl(version: 'v1', protocol: 'rest', formats: ['json'])]
    #[RequestContent(constraints: 'createOrUpdateConstraints')]
    public function createOrUpdate(Request $request, $version, $protocol, array $requestContent): JsonResponse
    {
        $addresses = $requestContent['addresses'];
        unset($requestContent['addresses']);

        /** @var \Oka\Notifier\ServerBundle\Model\ContactInterface $contact */
        if (!$contact = $this->contactManager->findOneBy($requestContent)) {
            $contact = $this->contactManager->create(
                $requestContent['channel'],
                $requestContent['name'],
                $addresses
            );
        } else {
            $contact->setAddresses($addresses);
            $this->contactManager->save($contact);
            $statusCode = 200;
        }

        return $this->json($contact, $statusCode ?? 201);
    }

    /**
     * Read a contact details.
     *
     * @param string $version
     * @param string $protocol
     */
    #[AccessControl(version: 'v1', protocol: 'rest', formats: ['json'])]
    public function read(Request $request, $version, $protocol, string $id): JsonResponse
    {
        /** @var \Oka\Notifier\ServerBundle\Model\ContactInterface $contact */
        if (!$contact = $this->contactManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Contact with resource identifier "%s" is not found.', $id));
        }

        return $this->json($contact);
    }

    /**
     * Delete a contact.
     *
     * @param string $version
     * @param string $protocol
     */
    #[AccessControl(version: 'v1', protocol: 'rest', formats: ['json'])]
    public function delete(Request $request, $version, $protocol, string $id): JsonResponse
    {
        /** @var \Oka\Notifier\ServerBundle\Model\ContactInterface $contact */
        if (!$contact = $this->contactManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Contact with resource identifier "%s" is not found.', $id));
        }

        $this->contactManager->remove($contact);

        return new JsonResponse(null, 204);
    }

    private function json($data, int $statusCode = 200, array $headers = [], array $context = []): JsonResponse
    {
        $context = [
            AbstractObjectNormalizer::GROUPS => ['details'],
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            ...$context,
        ];

        return new JsonResponse($this->serializer->serialize($data, 'json', $context), $statusCode, $headers, true);
    }

    private static function createOrUpdateConstraints(): Assert\Collection
    {
        return new Assert\Collection(fields: [
            'channel' => new Assert\Required(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)])),
            'name' => new Assert\Required(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)])),
            'addresses' => new Assert\Required(new Assert\All(new Assert\Collection(fields: [
                'value' => new Assert\Required(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)])),
                'name' => new Assert\Optional(new Assert\Sequentially([new Assert\NotBlank(), new Assert\Length(max: 255)])),
            ]))),
        ]);
    }
}
