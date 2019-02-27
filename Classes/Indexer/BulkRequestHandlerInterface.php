<?php
namespace Flowpack\ElasticSearch\ContentRepositoryAdaptor\Indexer;

use Flowpack\ElasticSearch\Domain\Model\Index;

interface BulkRequestHandlerInterface
{
    /**
     * @param array $bulkRequest
     * @param array $dimensions
     * @param Index $index
     * @return void
     * @throws \Exception
     */
    public function flush(array $bulkRequest, array $dimensions, Index $index);
}
