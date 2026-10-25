<?php
/** Shared fiscal transport and legacy data reader. Keep syntax compatible with PHP 5.3+. */
class WebmaniaNFeSecurity
{
    const CREDENTIAL_PREFIX = 'wmbr:enc:v1:';
    private static $credentialsMigrated = false;

    private static function credentialEncryptor()
    {
        if (class_exists('Mage', false)) { return Mage::helper('core'); }
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Encryption\EncryptorInterface');
    }

    public static function encryptCredential($value)
    {
        if (!is_string($value) || $value === '') { return ''; }
        $cipher = self::credentialEncryptor()->encrypt($value);
        if (!is_string($cipher) || $cipher === '') { throw new \RuntimeException('Não foi possível proteger a credencial.'); }
        return self::CREDENTIAL_PREFIX . $cipher;
    }

    public static function decryptCredential($value)
    {
        if (!is_string($value)) { return ''; }
        if (strpos($value, self::CREDENTIAL_PREFIX) !== 0) { return $value; }
        try {
            $plain = @self::credentialEncryptor()->decrypt(substr($value, strlen(self::CREDENTIAL_PREFIX)));
            return is_string($plain) ? $plain : '';
        } catch (\Exception $exception) {
            // An unreadable cipher must never be sent as an API credential.
            return '';
        } catch (\Throwable $exception) {
            return '';
        }
    }

    public static function migrateCredentials()
    {
        if (self::$credentialsMigrated) { return; }
        $legacy = class_exists('Mage', false);
        $resource = $legacy ? Mage::getSingleton('core/resource') :
            \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $legacy ? $resource->getConnection('core_write') : $resource->getConnection();
        $table = $resource->getTableName($legacy ? 'core/config_data' : 'core_config_data');
        $prefix = $legacy ? 'nfe/section_one/' : 'webmaniabr_nfe_configs/group_access_token_configs/';
        $paths = array();
        foreach (array('consumer_key', 'consumer_secret', 'access_token', 'access_token_secret', 'certificadoa1') as $field) {
            $paths[] = $prefix . $field;
        }
        $rows = $connection->fetchAll($connection->select()->from($table, array('config_id', 'value'))->where('path IN (?)', $paths));
        $changed = false;
        foreach ($rows as $row) {
            $value = $row['value'];
            if (!is_string($value) || $value === '' || strpos($value, self::CREDENTIAL_PREFIX) === 0) { continue; }
            // Compare the old value as well: do not overwrite a concurrent configuration save.
            $affected = $connection->update($table, array('value' => self::encryptCredential($value)),
                array('config_id = ?' => (int) $row['config_id'], 'value = ?' => $value));
            $changed = $affected > 0 || $changed;
        }
        if ($changed) {
            if ($legacy) { Mage::app()->getCacheInstance()->cleanType('config'); }
            else { \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Cache\TypeListInterface')->cleanType('config'); }
        }
        self::$credentialsMigrated = true;
    }


    /** Normalize document punctuation without discarding letters or invalid characters. */
    public static function normalizeDocument($document)
    {
        if (!is_string($document) && !is_int($document)) { return ''; }
        return strtoupper(str_replace(array('.', '/', '-', ' ', "\t", "\r", "\n"), '', (string) $document));
    }

    /** Validate both numeric and alphanumeric CNPJ using the official modulo-11 rule. */
    public static function validCnpj($cnpj)
    {
        $cnpj = self::normalizeDocument($cnpj);
        if (!preg_match('/\A[A-Z0-9]{12}[0-9]{2}\z/', $cnpj) || preg_match('/\A([0-9])\1{13}\z/', $cnpj)) {
            return false;
        }
        // Receita Federal: character value is ASCII minus 48; DVs remain numeric.
        for ($length = 12; $length <= 13; $length++) {
            $sum = 0;
            $weight = 2;
            for ($i = $length - 1; $i >= 0; $i--) {
                $sum += (ord($cnpj[$i]) - 48) * $weight;
                $weight = $weight === 9 ? 2 : $weight + 1;
            }
            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;
            if ((int) $cnpj[$length] !== $digit) { return false; }
        }
        return true;
    }

    public static function normalizeCnpjFields($data, $depth = 0)
    {
        if (!is_array($data) || $depth >= 32) { return $data; }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::normalizeCnpjFields($value, $depth + 1);
            } elseif ((is_string($value) || is_int($value)) && is_string($key) && (in_array(strtolower($key), array('cnpj', 'cnpj_fabricante', 'cnpj_intermediador', 'cnpj_credenciadora', 'cnpj_beneficiario'), true) ||
                (strtolower($key) === 'documento' && self::validCnpj($value)))) {
                $data[$key] = self::normalizeDocument($value);
            }
        }
        return $data;
    }

    public static function normalizeAccessKey($key)
    {
        $normalized = self::normalizeDocument($key);
        // Preserve NF-e keys and the 50-character national NFS-e identifiers.
        if (preg_match('/\A(?:[0-9]{6}[A-Z0-9]{12}[0-9]{26}|[A-Z0-9]{50})\z/', $normalized)) { return $normalized; }
        // Historical records and NFS-e identifiers may contain fewer numeric positions.
        return preg_replace('/[^0-9]/', '', is_scalar($key) ? (string) $key : '');
    }

    public static function validUrl($url, $document = false)
    {
        if (!is_string($url) || preg_match('/[\x00-\x20\x7f\\\\<>\x22\x27]/', $url)) {
            return false;
        }
        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts['scheme'], $parts['host'], $parts['path']) ||
            $parts['scheme'] !== 'https' || isset($parts['user']) || isset($parts['pass']) ||
            isset($parts['fragment']) || (isset($parts['port']) && $parts['port'] !== 443)) {
            return false;
        }
        $host = strtolower($parts['host']);
        $path = $parts['path'];
        if (preg_match('#(?:%|/\.{1,2}(?:/|$)|//)#', $path)) {
            return false;
        }
        if ($document) {
            return in_array($host, array('nfe.webmaniabr.com', 'nfe.webmania.com.br', 'webmaniabr.com', 'webmania.com.br'), true) &&
                (bool) preg_match('#^/(danfe|xmlnfe)/[a-zA-Z0-9/_-]+$#D', $path);
        }
        return in_array($host, array('webmaniabr.com', 'webmania.com.br'), true) && !isset($parts['query']) &&
            (bool) preg_match('#^/api/1/nfe/(sefaz|certificado|emissao|consulta|cancelar|inutilizar|cartacorrecao|devolucao|complementar)/$#D', $path);
    }

    public static function error($message, $uncertain = false)
    {
        return (object) array('error' => $message, 'request_uncertain' => $uncertain);
    }

    public static function request($method, $url, $data, $credentials, $document = false)
    {
        if (!self::validUrl($url, $document) || !in_array($method, array('GET', 'POST', 'PUT'), true)) {
            return self::error('Destino de comunicação não autorizado.');
        }
        $headers = array('Accept: application/json', 'Content-Type: application/json');
        $fields = array('consumer_key' => 'X-Consumer-Key', 'consumer_secret' => 'X-Consumer-Secret',
            'access_token' => 'X-Access-Token', 'access_token_secret' => 'X-Access-Token-Secret');
        foreach ($fields as $field => $header) {
            $value = isset($credentials[$field]) ? $credentials[$field] : '';
            if (!is_string($value) || $value === '' || preg_match('/[\x00-\x20\x7f]/', $value)) {
                return self::error('Credenciais de acesso inválidas.');
            }
            $headers[] = $header . ': ' . $value;
        }
        if (!function_exists('curl_init')) {
            return self::error('cURL não está disponível.');
        }
        $json = json_encode(self::normalizeCnpjFields($data));
        if (!$document && $json === false) {
            return self::error('Dados da nota fiscal inválidos.');
        }
        $body = '';
        $limit = $document ? 10485760 : 2097152;
        $handle = curl_init();
        if ($handle === false) {
            return self::error('Não foi possível iniciar a conexão.');
        }
        $options = array(
            CURLOPT_URL => $url, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $document ? 30 : (preg_match('#/(sefaz|certificado)/$#', $url) ? 15 : 300),
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_MAXREDIRS => 0,
            CURLOPT_WRITEFUNCTION => function ($handle, $chunk) use (&$body, $limit) {
                if (strlen($body) + strlen($chunk) > $limit) { return 0; }
                $body .= $chunk;
                return strlen($chunk);
            }
        );
        if (defined('CURLOPT_PROTOCOLS_STR')) {
            $options[constant('CURLOPT_PROTOCOLS_STR')] = 'https';
        } else {
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        }
        if (!$document) { $options[CURLOPT_POSTFIELDS] = $json; }
        curl_setopt_array($handle, $options);
        $result = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        // Read transport metadata before releasing the native handle. Unknown metadata
        // remains uncertain: a zero negotiation time alone does not prove no dispatch.
        $errno = $result === false && function_exists('curl_errno') ? curl_errno($handle) : 0;
        $requestSize = curl_getinfo($handle, CURLINFO_REQUEST_SIZE);
        $pretransfer = curl_getinfo($handle, CURLINFO_PRETRANSFER_TIME);
        $notSent = $result === false && $status === 0 &&
            (is_int($requestSize) || is_float($requestSize)) && (float) $requestSize === 0.0 &&
            (in_array($errno, array(5, 6, 7, 35, 60), true) ||
                ($errno === 28 && (is_int($pretransfer) || is_float($pretransfer)) && (float) $pretransfer === 0.0));

        // PHP 8 frees CurlHandle automatically; curl_close() is deprecated in PHP 8.5.
        if (is_resource($handle)) { curl_close($handle); }
        if (!$document && $result !== false && in_array($status, array(400, 401, 403, 404, 405, 422), true)) {
            $rejection = json_decode($body);
            if (is_object($rejection) && isset($rejection->error)) { return $body; }
        }
        if ($result === false || $status < 200 || $status >= 300) {
            return self::error($notSent ? 'Não foi possível enviar a solicitação à Webmania. Tente novamente.' :
                'Não foi possível confirmar a resposta da Webmania. Consulte a nota antes de tentar emitir novamente.', $method !== 'GET' && !$notSent);
        }
        return $body;
    }

    public static function jsonRequest($method, $url, $data, $credentials)
    {
        if (function_exists('set_time_limit')) { @set_time_limit(300); }
        elseif (function_exists('ini_set')) { @ini_set('max_execution_time', '300'); }
        $body = self::request($method, $url, $data, $credentials);
        if (is_object($body)) { return $body; }
        $response = json_decode($body);
        if (!is_object($response) || json_last_error() !== JSON_ERROR_NONE ||
            (!isset($response->error) && !isset($response->status) && !isset($response->expiration))) {
            return self::error('Resposta inválida da Webmania. Consulte a nota antes de tentar emitir novamente.', $method !== 'GET');
        }
        $response = self::redact($response, $credentials);
        if (isset($response->error)) {
            $response->error = is_scalar($response->error) ? htmlspecialchars((string) $response->error, ENT_QUOTES, 'UTF-8') : 'A Webmania recusou a solicitação.';
        } elseif (strpos($url, '/emissao/') !== false || strpos($url, '/consulta/') !== false) {
            if (!isset($response->status) || !self::validStatus($response->status) ||
                ($response->status !== 'reprovado' && (!isset($response->uuid) || !self::validUuid($response->uuid)))) {
                return self::error('Resposta de nota fiscal inválida. Consulte a nota antes de tentar emitir novamente.', $method !== 'GET');
            }
            // Optional API fields vary across synchronous/asynchronous responses.
            // A rejected (reprovado) response may omit uuid; callers read it unconditionally.
            foreach (array('uuid', 'chave', 'recibo', 'nfe', 'serie', 'xml', 'danfe', 'danfe_simples', 'danfe_etiqueta') as $field) {
                if (!isset($response->$field) || !is_scalar($response->$field)) { $response->$field = ''; }
            }
            foreach (array('xml', 'danfe', 'danfe_simples', 'danfe_etiqueta') as $field) {
                if (!self::validUrl($response->$field, true)) { $response->$field = ''; }
            }
        }
        return self::redact($response, $credentials);
    }

    private static function redact($value, $credentials)
    {
        if (is_object($value) || is_array($value)) {
            foreach ($value as $key => $item) {
                if (is_object($value)) { $value->$key = self::redact($item, $credentials); }
                else { $value[$key] = self::redact($item, $credentials); }
            }
        } elseif (is_string($value)) {
            foreach ($credentials as $secret) {
                if (is_string($secret) && strlen($secret) >= 8) { $value = str_replace($secret, '[redacted]', $value); }
            }
        }
        return $value;
    }

    public static function download($url, $credentials)
    {
        $body = self::request('GET', $url, array(), $credentials, true);
        return is_string($body) && substr($body, 0, 5) === '%PDF-' ? $body : false;
    }

    public static function validUuid($uuid)
    {
        return is_string($uuid) && (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/iD', $uuid);
    }

    public static function validStatus($status)
    {
        return is_string($status) && in_array($status, array('aprovado', 'reprovado', 'cancelado', 'denegado', 'processamento', 'processando', 'contingencia', 'pendente'), true);
    }

    public static function equals($expected, $received)
    {
        if (!is_string($expected) || $expected === '' || !is_string($received) || strlen($expected) !== strlen($received)) { return false; }
        if (function_exists('hash_equals')) { return hash_equals($expected, $received); }
        $diff = 0;
        for ($i = 0, $length = strlen($expected); $i < $length; $i++) { $diff |= ord($expected[$i]) ^ ord($received[$i]); }
        return $diff === 0;
    }

    public static function randomToken()
    {
        if (function_exists('random_bytes')) { return bin2hex(random_bytes(32)); }
        $strong = false;
        $bytes = openssl_random_pseudo_bytes(32, $strong);
        if (!$strong || $bytes === false) { throw new RuntimeException('Gerador seguro indisponível.'); }
        return bin2hex($bytes);
    }

    /** Decode only scalar/array data, without invoking PHP object deserialization. */
    public static function decode($raw)
    {
        if (is_array($raw)) { return $raw; }
        if (!is_string($raw) || $raw === '' || strlen($raw) > 2097152) { return array(); }
        $candidates = array($raw);
        $decoded = base64_decode($raw, true);
        if (is_string($decoded) && $decoded !== '') {
            $candidates[] = $decoded;
            // Older OpenCart releases double-encoded serialized bytes with utf8_encode().
            $latin = self::legacyDecode($decoded);
            if ($latin !== false && $latin !== $decoded) { $candidates[] = $latin; }
        }
        foreach ($candidates as $candidate) {
            $json = json_decode($candidate, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($json)) { return $json; }
            $offset = 0;
            try {
                $value = self::readValue($candidate, $offset, 0);
                if (is_array($value) && $offset === strlen($candidate)) { return $value; }
            } catch (UnexpectedValueException $e) { /* Try the next legacy representation. */ }
        }
        return array();
    }

    /** Normalize historical fiscal records before they reach HTML or PDF callers. */
    public static function invoices($raw)
    {
        $invoices = array();
        foreach (self::decode($raw) as $record) {
            if (!is_array($record)) { continue; }
            foreach (array('uuid', 'status', 'chave_acesso', 'chave', 'documento', 'data', 'n_nfe', 'n_recibo', 'n_serie') as $key) {
                $record[$key] = isset($record[$key]) && is_scalar($record[$key]) ? (string) $record[$key] : '';
            }
            if (!self::validStatus($record['status'])) { $record['status'] = ''; }
            foreach (array('n_nfe', 'n_recibo', 'n_serie') as $key) {
                $record[$key] = preg_replace('/[^0-9]/', '', $record[$key]);
            }
            $record['chave_acesso'] = self::normalizeAccessKey($record['chave_acesso']);
            $record['chave'] = self::normalizeAccessKey($record['chave']);
            $document = self::normalizeDocument($record['documento']);
            $record['documento'] = preg_match('/\A(?:[A-Z0-9]{12}[0-9]{2}|[0-9]{11})\z/', $document) ? $document : preg_replace('/[^0-9]/', '', $record['documento']);
            foreach (array('url_xml', 'url_danfe', 'url_danfe_simplificada', 'url_danfe_simples', 'url_danfe_etiqueta') as $key) {
                $record[$key] = isset($record[$key]) && self::validUrl($record[$key], true) ? $record[$key] : '';
            }
            $invoices[] = $record;
        }
        return $invoices;
    }

    private static function readValue($raw, &$offset, $depth)
    {
        if ($depth > 32 || $offset >= strlen($raw)) { throw new UnexpectedValueException(); }
        $type = $raw[$offset++];
        if ($type === 'N' && substr($raw, $offset++, 1) === ';') { return null; }
        if (substr($raw, $offset++, 1) !== ':') { throw new UnexpectedValueException(); }
        if ($type === 's') {
            $end = strpos($raw, ':', $offset);
            if ($end === false) { throw new UnexpectedValueException(); }
            $length = substr($raw, $offset, $end - $offset);
            if (!ctype_digit($length) || strlen($length) > 7 || (int) $length > 2097152 || substr($raw, $end + 1, 1) !== '"') { throw new UnexpectedValueException(); }
            $offset = $end + 2;
            $value = substr($raw, $offset, (int) $length);
            $offset += (int) $length;
            if (substr($raw, $offset, 2) !== '";') { throw new UnexpectedValueException(); }
            $offset += 2;
            return $value;
        }
        if ($type === 'a') {
            $end = strpos($raw, ':{', $offset);
            if ($end === false) { throw new UnexpectedValueException(); }
            $count = substr($raw, $offset, $end - $offset);
            if (!ctype_digit($count) || strlen($count) > 5 || (int) $count > 10000) { throw new UnexpectedValueException(); }
            $offset = $end + 2;
            $value = array();
            for ($i = 0; $i < (int) $count; $i++) {
                $key = self::readValue($raw, $offset, $depth + 1);
                if (!is_int($key) && !is_string($key)) { throw new UnexpectedValueException(); }
                $value[$key] = self::readValue($raw, $offset, $depth + 1);
            }
            if (substr($raw, $offset++, 1) !== '}') { throw new UnexpectedValueException(); }
            return $value;
        }
        if (in_array($type, array('b', 'i', 'd'), true)) {
            $end = strpos($raw, ';', $offset);
            if ($end === false) { throw new UnexpectedValueException(); }
            $value = substr($raw, $offset, $end - $offset);
            $offset = $end + 1;
            if ($type === 'b' && ($value === '0' || $value === '1')) { return $value === '1'; }
            if ($type === 'i' && preg_match('/^-?\d{1,19}$/D', $value)) { return (int) $value; }
            if ($type === 'd' && is_numeric($value)) { return (float) $value; }
        }
        throw new UnexpectedValueException();
    }

    public static function legacyEncode($value)
    {
        // Exact ISO-8859-1 byte mapping, independent of optional mbstring/iconv extensions.
        return preg_replace_callback('/[\x80-\xff]/', function ($m) {
            $byte = ord($m[0]);
            return chr(0xc0 | ($byte >> 6)) . chr(0x80 | ($byte & 0x3f));
        }, $value);
    }

    public static function legacyDecode($value)
    {
        if (preg_match('/[^\x00-\x7f\xc2\xc3\x80-\xbf]/', $value)) { return false; }
        return preg_replace_callback('/[\xc2\xc3][\x80-\xbf]/', function ($m) {
            return chr(((ord($m[0][0]) & 0x1f) << 6) | (ord($m[0][1]) & 0x3f));
        }, $value);
    }

    /** Use a private, unpredictable directory and delete files after streaming. */
    public static function privateDirectory()
    {
        $directory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wmbr-nfe-' . self::randomToken();
        if (!mkdir($directory, 0700)) { throw new RuntimeException('Não foi possível preparar os documentos fiscais.'); }
        return $directory . DIRECTORY_SEPARATOR;
    }
}
