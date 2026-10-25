<?php declare(strict_types=1);

namespace Webmaniabr\Nfe\Helper;
require_once __DIR__ . '/Security.php';

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Security validator helper for WebmaniaBR NFe module
 */
class SecurityValidator extends AbstractHelper
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var array
     */
    private $allowedIpRanges = [
        '177.67.84.0/24',  // WebmaniaBR IP range
        '45.4.84.0/24',    // Additional WebmaniaBR range
    ];

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param EncryptorInterface $encryptor
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        EncryptorInterface $encryptor,
        RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Validate API credentials
     *
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $accessToken
     * @param string $accessTokenSecret
     * @return bool
     */
    public function validateCredentials(
        string $consumerKey,
        string $consumerSecret,
        string $accessToken,
        string $accessTokenSecret
    ): bool {
        // Check credential format
        if (!$this->isValidCredentialFormat($consumerKey) ||
            !$this->isValidCredentialFormat($consumerSecret) ||
            !$this->isValidCredentialFormat($accessToken) ||
            !$this->isValidCredentialFormat($accessTokenSecret)) {
            
            $this->logger->warning('WebmaniaBR NFe: Invalid credential format detected');
            return false;
        }

        // Additional validation can be added here
        return true;
    }

    /**
     * Validate credential format
     *
     * @param string $credential
     * @return bool
     */
    private function isValidCredentialFormat(string $credential): bool
    {
        // Basic format validation
        return !empty($credential) && 
               strlen($credential) >= 10 && 
               strlen($credential) <= 255 &&
               preg_match('/^[a-zA-Z0-9\-_]+$/', $credential);
    }

    /**
     * Validate webhook IP address
     *
     * @param string|null $ipAddress
     * @return bool
     */
    public function validateWebhookIp(?string $ipAddress = null): bool
    {
        $ip = $ipAddress ?? $this->remoteAddress->getRemoteAddress();
        
        if (!$ip) {
            $this->logger->warning('WebmaniaBR NFe: No IP address provided for webhook validation');
            return false;
        }

        foreach ($this->allowedIpRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        $this->logger->warning(sprintf(
            'WebmaniaBR NFe: Webhook request from unauthorized IP: %s',
            $ip
        ));

        return false;
    }

    /**
     * Check if IP is in range
     *
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $mask] = explode('/', $range);
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Validate order data for NFe emission
     *
     * @param array $orderData
     * @return array
     */
    public function validateOrderData(array $orderData): array
    {
        $errors = [];

        // Required fields validation
        $requiredFields = [
            'customer' => ['name', 'email'],
            'billing' => ['street', 'city', 'region', 'postcode'],
            'items' => []
        ];

        foreach ($requiredFields as $section => $fields) {
            if (!isset($orderData[$section])) {
                $errors[] = "Missing required section: {$section}";
                continue;
            }

            foreach ($fields as $field) {
                if (empty($orderData[$section][$field])) {
                    $errors[] = "Missing required field: {$section}.{$field}";
                }
            }
        }

        // Validate customer document
        if (isset($orderData['customer']['taxvat'])) {
            $document = \WebmaniaNFeSecurity::normalizeDocument($orderData['customer']['taxvat']);
            if (!$this->isValidCpfCnpj($document)) {
                $errors[] = 'Invalid customer document (CPF/CNPJ)';
            }
        }

        // Validate postal code
        if (isset($orderData['billing']['postcode'])) {
            $postcode = preg_replace('/[^0-9]/', '', $orderData['billing']['postcode']);
            if (!preg_match('/^\d{8}$/', $postcode)) {
                $errors[] = 'Invalid postal code format';
            }
        }

        return $errors;
    }

    /**
     * Validate CPF or CNPJ
     *
     * @param string $document
     * @return bool
     */
    private function isValidCpfCnpj(string $document): bool
    {
        $document = \WebmaniaNFeSecurity::normalizeDocument($document);

        if (preg_match('/\A[0-9]{11}\z/', $document)) {
            return $this->isValidCpf($document);
        } elseif (strlen($document) === 14) {
            return $this->isValidCnpj($document);
        }

        return false;
    }

    /**
     * Validate CPF
     *
     * @param string $cpf
     * @return bool
     */
    private function isValidCpf(string $cpf): bool
    {
        // Check for invalid sequences
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calculate first digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * (10 - $i);
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;

        // Calculate second digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * (11 - $i);
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return (int)$cpf[9] === $digit1 && (int)$cpf[10] === $digit2;
    }

    /**
     * Validate CNPJ
     *
     * @param string $cnpj
     * @return bool
     */
    private function isValidCnpj(string $cnpj): bool
    {
        return \WebmaniaNFeSecurity::validCnpj($cnpj);
    }

    /**
     * Sanitize input data
     *
     * @param array $data
     * @return array
     */
    public function sanitizeData(array $data): array
    {
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
                // FILTER_SANITIZE_STRING foi removido no PHP 8.1+
                // Substitui por strip_tags + sanitização básica
                $value = strip_tags($value);
                $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
            }
        });

        return $data;
    }

    /**
     * Encrypt sensitive data
     *
     * @param string $data
     * @return string
     */
    public function encryptSensitiveData(string $data): string
    {
        return $this->encryptor->encrypt($data);
    }

    /**
     * Decrypt sensitive data
     *
     * @param string $encryptedData
     * @return string
     */
    public function decryptSensitiveData(string $encryptedData): string
    {
        return $this->encryptor->decrypt($encryptedData);
    }

    /**
     * Log security event
     *
     * @param string $event
     * @param array $context
     * @return void
     */
    public function logSecurityEvent(string $event, array $context = []): void
    {
        $this->logger->info(sprintf('WebmaniaBR NFe Security Event: %s', $event), $context);
    }
}
