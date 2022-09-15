<?php

namespace Oka\Notifier\ServerBundle\Controller;

use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\Notifier\ServerBundle\Service\MessageManager;
use Oka\PaginationBundle\Pagination\PaginationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class MessageController
{
    private $messageManager;
    private $paginationManager;
    private $serializer;
    private $paginationManagerName;

    public function __construct(MessageManager $messageManager, PaginationManager $paginationManager, SerializerInterface $serializer, string $paginationManagerName)
    {
        $this->messageManager = $messageManager;
        $this->paginationManager = $paginationManager;
        $this->serializer = $serializer;
        $this->paginationManagerName = $paginationManagerName;
    }

    /**
     * Retrieve message list.
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
            $page = $this->paginationManager->paginate($this->paginationManagerName, $request, [], ['issuedAt' => 'DESC']);
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
     * Read message details.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function read(Request $request, $version, $protocol, string $id): JsonResponse
    {
        if (!$message = $this->messageManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Message with resource identifier "%s" is not found.', $id));
        }

        return new JsonResponse($this->serializer->serialize($message, 'json', ['groups' => ['details']]), 200, [], true);
    }

    /**
     * Delete message.
     *
     * @param string $version
     * @param string $protocol
     *
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function delete(Request $request, $version, $protocol, string $id): JsonResponse
    {
        if (!$message = $this->messageManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Message with resource identifier "%s" is not found.', $id));
        }

        $this->messageManager->remove($message);

        return new JsonResponse(null, 204);
    }
}
