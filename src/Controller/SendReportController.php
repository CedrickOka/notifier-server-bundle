<?php

namespace Oka\Notifier\ServerBundle\Controller;

use Oka\InputHandlerBundle\Annotation\AccessControl;
use Oka\InputHandlerBundle\Annotation\RequestContent;
use Oka\Notifier\ServerBundle\Service\SendReportManager;
use Oka\PaginationBundle\Pagination\PaginationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Cedrick Oka Baidai <okacedrick@gmail.com>
 */
class SendReportController
{
    private $reportManager;
    private $paginationManager;
    private $paginationManagerName;
    private $serializer;

    public function __construct(SendReportManager $reportManager, PaginationManager $paginationManager, SerializerInterface $serializer, string $paginationManagerName)
    {
        $this->reportManager = $reportManager;
        $this->paginationManager = $paginationManager;
        $this->serializer = $serializer;
        $this->paginationManagerName = $paginationManagerName;
    }

    /**
     * Retrieve send report list
     *
     * @param string $version
     * @param string $protocol
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
     * Read send report details
     *
     * @param string $version
     * @param string $protocol
     * @AccessControl(version="v1", protocol="rest", formats="json")
     */
    public function read(Request $request, $version, $protocol, string $id): JsonResponse
    {
        if (!$report = $this->reportManager->find($id)) {
            throw new NotFoundHttpException(sprintf('Send report with resource identifier "%s" is not found.', $id));
        }

        return new JsonResponse($this->serializer->serialize($report, 'json', ['groups' => ['details']]), 200, [], true);
    }
}
