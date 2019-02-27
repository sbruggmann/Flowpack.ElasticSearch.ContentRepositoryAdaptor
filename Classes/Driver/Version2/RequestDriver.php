<?php
namespace Flowpack\ElasticSearch\ContentRepositoryAdaptor\Driver\Version2;

/*
 * This file is part of the Flowpack.ElasticSearch.ContentRepositoryAdaptor package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\ElasticSearch\ContentRepositoryAdaptor\Driver\Version1;
use Neos\Flow\Annotations as Flow;

/**
 * Request driver for Elasticsearch version 2.x
 *
 * @Flow\Scope("singleton")
 */
class RequestDriver extends Version1\RequestDriver
{
}
