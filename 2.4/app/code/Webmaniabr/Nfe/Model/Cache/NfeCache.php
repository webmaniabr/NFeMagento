<?php declare(strict_types=1);

namespace Webmaniabr\Nfe\Model\Cache;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Cache manager for WebmaniaBR NFe data
 */
class NfeCache
{
    /**
     * Cache lifetime in seconds (1 hour)
     */
    private const CACHE_LIFETIME = 3600;

    /**
     * @var Config
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $cache
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $cache,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Get cached NFe data
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        try {
            $data = $this->cache->load($this->getCacheKey($key));
            return $data ? $this->serializer->unserialize($data) : null;
        } catch (\Exception $e) {
            $this->logger->error('WebmaniaBR NFe Cache Get Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store NFe data in cache
     *
     * @param string $key
     * @param array $data
     * @param int|null $lifetime
     * @return bool
     */
    public function set(string $key, array $data, ?int $lifetime = null): bool
    {
        try {
            $serializedData = $this->serializer->serialize($data);
            $tags = ['WEBMANIABR_NFE'];
            $lifetime = $lifetime ?? self::CACHE_LIFETIME;
            
            return $this->cache->save($serializedData, $this->getCacheKey($key), $tags, $lifetime);
        } catch (\Exception $e) {
            $this->logger->error('WebmaniaBR NFe Cache Set Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove specific cache entry
     *
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool
    {
        try {
            return $this->cache->remove($this->getCacheKey($key));
        } catch (\Exception $e) {
            $this->logger->error('WebmaniaBR NFe Cache Remove Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all NFe cache
     *
     * @return bool
     */
    public function clean(): bool
    {
        try {
            return $this->cache->clean(['WEBMANIABR_NFE']);
        } catch (\Exception $e) {
            $this->logger->error('WebmaniaBR NFe Cache Clean Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate cache key with prefix
     *
     * @param string $key
     * @return string
     */
    private function getCacheKey(string $key): string
    {
        return 'webmaniabr_nfe_' . md5($key);
    }

    /**
     * Cache API response
     *
     * @param int $orderId
     * @param array $response
     * @return bool
     */
    public function cacheApiResponse(int $orderId, array $response): bool
    {
        return $this->set("api_response_{$orderId}", $response, 1800); // 30 minutes
    }

    /**
     * Get cached API response
     *
     * @param int $orderId
     * @return array|null
     */
    public function getCachedApiResponse(int $orderId): ?array
    {
        return $this->get("api_response_{$orderId}");
    }

    /**
     * Cache order NFe status
     *
     * @param int $orderId
     * @param string $status
     * @return bool
     */
    public function cacheOrderStatus(int $orderId, string $status): bool
    {
        return $this->set("order_status_{$orderId}", ['status' => $status, 'cached_at' => time()]);
    }

    /**
     * Get cached order status
     *
     * @param int $orderId
     * @return string|null
     */
    public function getCachedOrderStatus(int $orderId): ?string
    {
        $data = $this->get("order_status_{$orderId}");
        return $data['status'] ?? null;
    }
}