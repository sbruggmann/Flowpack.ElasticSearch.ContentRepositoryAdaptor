<?php
namespace Flowpack\ElasticSearch\ContentRepositoryAdaptor\Indexer;

/*
 * This file is part of the Flowpack.ElasticSearch.ContentRepositoryAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Driver\RequestDriverInterface;
use Flowpack\ElasticSearch\ContentRepositoryAdaptor\ElasticSearchClient;
use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Indexer\Error\BulkIndexingError;
use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Indexer\Error\MalformedBulkRequestError;
use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Service\ErrorHandlingService;
use Flowpack\ElasticSearch\Domain\Model\Index;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class BulkRequestHandler implements BulkRequestHandlerInterface
{
    /**
     * @Flow\Inject
     * @var ErrorHandlingService
     */
    protected $errorHandlingService;

    /**
     * @Flow\Inject
     * @var ElasticSearchClient
     */
    protected $searchClient;

    /**
     * @var RequestDriverInterface
     * @Flow\Inject
     */
    protected $requestDriver;

    /**
     * @param array $bulkRequest
     * @param array $dimensions
     * @param Index $index
     * @return void
     * @throws \Exception
     */
    public function flush(array $bulkRequest, array $dimensions, Index $index)
    {
        if (count($bulkRequest) === 0) {
            return;
        }

        $payload = [];
        foreach ($bulkRequest as $bulkRequestTuple) {
            $tupleAsJson = '';
            foreach ($bulkRequestTuple['items'] as $bulkRequestItem) {
                $itemAsJson = json_encode($bulkRequestItem);
                if ($itemAsJson === false) {
                    $this->errorHandlingService->log(new MalformedBulkRequestError('Indexing Error: Bulk request item could not be encoded as JSON - ' . json_last_error_msg(), $bulkRequestItem));
                    continue 2;
                }
                $tupleAsJson .= $itemAsJson . chr(10);
            }
            $hash = $bulkRequestTuple['targetDimensions'];
            if (!isset($payload[$hash])) {
                $payload[$hash] = '';
            }
            $payload[$hash] .= $tupleAsJson;
        }

        foreach ($dimensions as $hash => $activeDimensions) {
            $this->searchClient->withDimensions(function () use (&$payload, $hash, $bulkRequest, $index) {
                $response = $this->requestDriver->bulk($index, $payload[$hash]);
                foreach ($response as $responseLine) {
                    if (isset($response['errors']) && $response['errors'] !== false) {
                        $this->errorHandlingService->log(new BulkIndexingError($bulkRequest, $responseLine));
                    }
                }
            }, $activeDimensions);
        }
    }
}
