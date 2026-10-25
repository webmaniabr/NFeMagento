<?php declare(strict_types=1);

namespace Webmaniabr\Nfe\Console\Command;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\ModuleList;
use Psr\Log\LoggerInterface;

/**
 * Console command to validate WebmaniaBR NFe compatibility with Magento 2.4.8
 */
class ValidateCompatibility extends Command
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param ResourceConnection $resourceConnection
     * @param ModuleList $moduleList
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        ResourceConnection $resourceConnection,
        ModuleList $moduleList,
        LoggerInterface $logger,
        $name = null
    ) {
        $this->productMetadata = $productMetadata;
        $this->resourceConnection = $resourceConnection;
        $this->moduleList = $moduleList;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $this->setName('webmaniabr:nfe:validate-compatibility');
        $this->setDescription('Validate WebmaniaBR NFe module compatibility with current Magento version');
        
        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>WebmaniaBR NFe - Compatibility Validation</info>');
        $output->writeln('==========================================');

        try {
            // Check Magento version
            $this->checkMagentoVersion($output);
            
            // Check PHP version
            $this->checkPhpVersion($output);
            
            // Check required extensions
            $this->checkPhpExtensions($output);
            
            // Check database tables
            $this->checkDatabaseTables($output);
            
            // Check module version
            $this->checkModuleVersion($output);
            
            // Check file permissions
            $this->checkFilePermissions($output);
            
            $output->writeln('');
            $output->writeln('<info>✓ Validation completed successfully!</info>');
            
        } catch (\Exception $e) {
            $output->writeln('<error>Error during validation: ' . $e->getMessage() . '</error>');
            $this->logger->error('WebmaniaBR NFe Validation Error: ' . $e->getMessage());
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Check Magento version compatibility
     *
     * @param OutputInterface $output
     * @throws \Exception
     */
    private function checkMagentoVersion(OutputInterface $output): void
    {
        $version = $this->productMetadata->getVersion();
        $edition = $this->productMetadata->getEdition();
        
        $output->writeln(sprintf('<info>Magento Version:</info> %s %s', $edition, $version));
        
        if (version_compare($version, '2.4.8', '<')) {
            $output->writeln('<comment>⚠ Warning: This module is optimized for Magento 2.4.8+</comment>');
            $output->writeln('<comment>  Current version may have compatibility issues.</comment>');
        } else {
            $output->writeln('<info>✓ Magento version is compatible</info>');
        }
    }

    /**
     * Check PHP version compatibility
     *
     * @param OutputInterface $output
     */
    private function checkPhpVersion(OutputInterface $output): void
    {
        $phpVersion = PHP_VERSION;
        $output->writeln(sprintf('<info>PHP Version:</info> %s', $phpVersion));
        
        if (version_compare($phpVersion, '8.3.0', '<')) {
            $output->writeln('<error>✗ PHP 8.3.0+ is required for Magento 2.4.8 compatibility</error>');
        } elseif (version_compare($phpVersion, '8.4.0', '>=')) {
            $output->writeln('<info>✓ PHP version is optimal for Magento 2.4.8</info>');
        } else {
            $output->writeln('<info>✓ PHP version is compatible</info>');
        }
    }

    /**
     * Check required PHP extensions
     *
     * @param OutputInterface $output
     */
    private function checkPhpExtensions(OutputInterface $output): void
    {
        $requiredExtensions = ['curl', 'json', 'openssl', 'mbstring'];
        $output->writeln('<info>PHP Extensions:</info>');
        
        $table = new Table($output);
        $table->setHeaders(['Extension', 'Status']);
        
        foreach ($requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $status = $loaded ? '<info>✓ Loaded</info>' : '<error>✗ Missing</error>';
            $table->addRow([$extension, $status]);
        }
        
        $table->render();
    }

    /**
     * Check database tables
     *
     * @param OutputInterface $output
     */
    private function checkDatabaseTables(OutputInterface $output): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tables = [
            'webmaniabrnfe_orders_nfe',
            'webmaniabrnfe_orders_transportadora', 
            'webmaniabrnfe_secret_keys'
        ];
        
        $output->writeln('<info>Database Tables:</info>');
        
        $table = new Table($output);
        $table->setHeaders(['Table', 'Status', 'Records']);
        
        foreach ($tables as $tableName) {
            $fullTableName = $connection->getTableName($tableName);
            $exists = $connection->isTableExists($fullTableName);
            
            if ($exists) {
                $count = $connection->fetchOne("SELECT COUNT(*) FROM {$fullTableName}");
                $status = '<info>✓ Exists</info>';
                $records = (string)$count;
            } else {
                $status = '<error>✗ Missing</error>';
                $records = 'N/A';
            }
            
            $table->addRow([$tableName, $status, $records]);
        }
        
        $table->render();
    }

    /**
     * Check module version
     *
     * @param OutputInterface $output
     */
    private function checkModuleVersion(OutputInterface $output): void
    {
        $moduleInfo = $this->moduleList->getOne('Webmaniabr_Nfe');
        $version = $moduleInfo['setup_version'] ?? 'Unknown';
        
        $output->writeln(sprintf('<info>Module Version:</info> %s', $version));
        
        if (version_compare($version, '4.0.0', '>=')) {
            $output->writeln('<info>✓ Module version is compatible with Magento 2.4.8</info>');
        } else {
            $output->writeln('<comment>⚠ Consider upgrading to version 4.0.0+ for best compatibility</comment>');
        }
    }

    /**
     * Check file permissions for log files
     *
     * @param OutputInterface $output
     */
    private function checkFilePermissions(OutputInterface $output): void
    {
        $logFile = BP . '/var/log/webmaniabr_nfe.log';
        
        $output->writeln('<info>File Permissions:</info>');
        
        if (file_exists($logFile)) {
            $writable = is_writable($logFile);
            $status = $writable ? '<info>✓ Writable</info>' : '<error>✗ Not writable</error>';
            $output->writeln(sprintf('Log file: %s', $status));
            
            if (!$writable) {
                $output->writeln('<comment>Run: chmod 664 ' . $logFile . '</comment>');
            }
        } else {
            $output->writeln('<comment>⚠ Log file does not exist yet (will be created on first use)</comment>');
        }
        
        // Check var/log directory
        $logDir = BP . '/var/log';
        if (is_writable($logDir)) {
            $output->writeln('<info>✓ Log directory is writable</info>');
        } else {
            $output->writeln('<error>✗ Log directory is not writable</error>');
            $output->writeln('<comment>Run: chmod 775 ' . $logDir . '</comment>');
        }
    }
}