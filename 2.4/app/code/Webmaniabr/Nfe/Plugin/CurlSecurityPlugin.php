<?php declare(strict_types=1);

namespace Webmaniabr\Nfe\Plugin;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

/**
 * Security plugin for CURL requests in WebmaniaBR NFe module
 */
class CurlSecurityPlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $allowedHosts = [
        'webmaniabr.com',
        'api.webmaniabr.com',
        'nfe.webmaniabr.com'
    ];

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Apply security measures before making HTTP requests
     *
     * @param Curl $subject
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException
     */
    public function aroundRequest(Curl $subject, callable $proceed, string $method, string $uri, array $params = [])
    {
        // Validate URL security
        if (!$this->isUrlSafe($uri)) {
            $this->logger->error('WebmaniaBR NFe: Blocked unsafe URL request: ' . $this->sanitizeUrl($uri));
            throw new \InvalidArgumentException('Request to unsafe URL blocked for security reasons.');
        }

        // Apply security headers and options
        $this->applySecurityOptions($subject);

        // Proceed with request
        $result = $proceed($method, $uri, $params);

        // After logic
        $this->logAfterRequest($subject, $method, $uri);

        return $result;
    }

    /**
     * Log successful API responses for audit
     *
     * @param Curl $subject
     * @param mixed $result
     * @param string $method
     * @param string $uri
     * @return mixed
     */
    private function logAfterRequest(Curl $subject, string $method, string $uri): void
    {
        $status = $subject->getStatus();

        if ($this->isWebmaniaBrApiCall($uri)) {
            $this->logger->info(sprintf(
                'WebmaniaBR NFe API Call: %s %s - Status: %d',
                $method,
                $this->sanitizeUrl($uri),
                $status
            ));

            if ($status >= 400) {
                $this->logger->warning(sprintf(
                    'WebmaniaBR NFe API Error: %s %s - Status: %d - Response: %s',
                    $method,
                    $this->sanitizeUrl($uri),
                    $status,
                    substr($subject->getBody(), 0, 500)
                ));
            }
        }
    }

    /**
     * Check if URL is safe for requests
     *
     * @param string $url
     * @return bool
     */
    private function isUrlSafe(string $url): bool
    {
        $parsedUrl = parse_url($url);
        
        if (!$parsedUrl || !isset($parsedUrl['host'])) {
            return false;
        }

        // Check if it's a WebmaniaBR API call
        if ($this->isWebmaniaBrApiCall($url)) {
            return $this->isAllowedHost($parsedUrl['host']);
        }

        // Allow other HTTPS requests (for general Magento functionality)
        return isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https';
    }

    /**
     * Check if this is a WebmaniaBR API call
     *
     * @param string $url
     * @return bool
     */
    private function isWebmaniaBrApiCall(string $url): bool
    {
        return strpos($url, 'webmaniabr.com') !== false;
    }

    /**
     * Check if host is in allowed list
     *
     * @param string $host
     * @return bool
     */
    private function isAllowedHost(string $host): bool
    {
        foreach ($this->allowedHosts as $allowedHost) {
            if ($host === $allowedHost || substr($host, -strlen($allowedHost) - 1) === '.' . $allowedHost) {
                return true;
            }
        }
        return false;
    }

    /**
     * Apply security options to CURL
     *
     * @param Curl $curl
     * @return void
     */
    private function applySecurityOptions(Curl $curl): void
    {
        // Security headers
        $curl->addHeader('User-Agent', 'WebmaniaBR-Magento-NFe/4.0.0');
        $curl->addHeader('X-Requested-With', 'WebmaniaBR-NFe-Module');
        
        // Security CURL options
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 10);
        $curl->setOption(CURLOPT_MAXREDIRS, 3);
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $curl->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $curl->setOption(CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        $curl->setOption(CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTPS);
        
        // Disable dangerous options
        $curl->setOption(CURLOPT_FOLLOWLOCATION, false);
        $curl->setOption(CURLOPT_UNRESTRICTED_AUTH, false);
    }

    /**
     * Sanitize URL for logging (remove sensitive data)
     *
     * @param string $url
     * @return string
     */
    private function sanitizeUrl(string $url): string
    {
        // Remove or mask sensitive query parameters
        $parsedUrl = parse_url($url);
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
            
            // Mask sensitive parameters
            $sensitiveParams = ['consumer_key', 'consumer_secret', 'access_token', 'token'];
            foreach ($sensitiveParams as $param) {
                if (isset($queryParams[$param])) {
                    $queryParams[$param] = '***MASKED***';
                }
            }
            
            $parsedUrl['query'] = http_build_query($queryParams);
        }

        return $this->buildUrl($parsedUrl);
    }

    /**
     * Build URL from parsed components
     *
     * @param array $parsedUrl
     * @return string
     */
    private function buildUrl(array $parsedUrl): string
    {
        $url = '';
        
        if (isset($parsedUrl['scheme'])) {
            $url .= $parsedUrl['scheme'] . '://';
        }
        
        if (isset($parsedUrl['host'])) {
            $url .= $parsedUrl['host'];
        }
        
        if (isset($parsedUrl['port'])) {
            $url .= ':' . $parsedUrl['port'];
        }
        
        if (isset($parsedUrl['path'])) {
            $url .= $parsedUrl['path'];
        }
        
        if (isset($parsedUrl['query'])) {
            $url .= '?' . $parsedUrl['query'];
        }
        
        return $url;
    }
}
