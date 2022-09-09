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
    private $contactManager;
    private $paginationManager;
    private $serializer;
    private $paginationManagerName;

    public function __construct(ContactManager $contactManager, PaginationManager $paginationManager, SerializerInterface $serializer, string $paginationManagerName)
    {
        $this->contactManager = $contactManager;
        $this->paginationManager = $paginationManager;
        $this->serializer = $serializer;
        $this->paginationManagerName = $paginationManagerName;
    }

    /**
     * Retrieve contact list.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
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
     * Create contact.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     * @RequestContent(constraints="createConstraints")
     */
    public function create(Request $request, $version, $protocol, array $requestContent): JsonResponse
    {
        $contact = $this->contactManager->create(
            $requestContent['channel'],
            $requestContent['name'],
            $requestContent['addresses']
        );

        return $this->json($contact, 201);
    }

    /**
     * Read contact details.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function read(Request $request, $version, $protocol, string $id): JsonResponse
    {
        if (!$contact = $this->contactManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Contact with resource identifier "%s" is not found.', $id));
        }

        return $this->json($contact);
    }

    /**
     * Update contact.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     * @RequestContent(constraints="updateConstraints")
     */
    public function update(Request $request, $version, $protocol, array $requestContent, string $id): JsonResponse
    {
        if (!$contact = $this->contactManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Contact with resource identifier "%s" is not found.', $id));
        }

        $contact->setAddresses($requestContent['addresses']);
        $this->contactManager->save($contact);

        return $this->json($contact);
    }

    /**
     * Delete contact.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function delete(Request $request, $version, $protocol, string $id): JsonResponse
    {
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

    private static function createConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'channel' => new Assert\Required(new Assert\NotBlank()),
            'name' => new Assert\Required(new Assert\NotBlank()),
            'addresses' => new Assert\Required(new Assert\All(new Assert\Collection([
                'value' => new Assert\Required(new Assert\NotBlank()),
                'name' => new Assert\Optional(new Assert\NotBlank()),
            ]))),
        ]);
    }

    private static function updateConstraints(): Assert\Collection
    {
        $constraints = self::createConstraints();
        unset($constraints->fields['channel'], $constraints->fields['name']);

        return $constraints;
    }
}
