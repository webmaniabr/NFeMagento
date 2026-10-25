<?php declare(strict_types=1);

namespace Webmaniabr\Nfe\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

/**
 * System cache type for WebmaniaBR NFe module
 */
class Type extends TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'webmaniabr_nfe';

    /**
     * The tag of cache type, used to rollback caches
     */
    public const CACHE_TAG = 'WEBMANIABR_NFE';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }
}