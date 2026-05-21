<?php

namespace DecentraShare;

use Symfony\Component\Dotenv\Dotenv;
use DecentraShare\Utils\Web3;
use DecentraShare\Utils\IPFS;
use DecentraShare\Utils\Contract;

class Application {
    private static ?self $instance = null;
    private array $config = [];
    private Web3 $web3;
    private IPFS $ipfs;
    private Contract $contract;
    private string $basePath;

    private function __construct(string $basePath) {
        $this->basePath = rtrim($basePath, '/');
        $this->loadEnvironment();
        $this->loadConfig();
        $this->initializeServices();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(string $basePath = __DIR__): self {
        if (self::$instance === null) {
            self::$instance = new self($basePath);
        }
        return self::$instance;
    }

    /**
     * Load environment variables
     */
    private function loadEnvironment(): void {
        try {
            $dotenv = new Dotenv();
            $envFile = $this->basePath . '/../.env';
            
            if (file_exists($envFile)) {
                $dotenv->loadEnv($envFile);
            }
        } catch (\Exception $e) {
            error_log('Warning: Could not load .env file: ' . $e->getMessage());
        }
    }

    /**
     * Load configuration
     */
    private function loadConfig(): void {
        $configFile = $this->basePath . '/../config/contracts.php';
        
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            throw new \RuntimeException('Configuration file not found: ' . $configFile);
        }
    }

    /**
     * Initialize services
     */
    private function initializeServices(): void {
        $this->web3 = new Web3($this->config['web3']['rpc_url']);
        $this->ipfs = new IPFS($this->config['ipfs']);
        $this->contract = new Contract($this->config);
    }

    /**
     * Get configuration
     */
    public function getConfig(): array {
        return $this->config;
    }

    /**
     * Get Web3 utility
     */
    public function getWeb3(): Web3 {
        return $this->web3;
    }

    /**
     * Get IPFS utility
     */
    public function getIPFS(): IPFS {
        return $this->ipfs;
    }

    /**
     * Get Contract utility
     */
    public function getContract(): Contract {
        return $this->contract;
    }

    /**
     * Get base path
     */
    public function getBasePath(): string {
        return $this->basePath;
    }

    /**
     * Check if IPFS is configured
     */
    public function isIPFSConfigured(): bool {
        $provider = $this->config['ipfs']['provider'];
        
        return match ($provider) {
            'pinata' => !empty($this->config['ipfs']['pinata']['api_key']) 
                     && !empty($this->config['ipfs']['pinata']['secret_key']),
            'web3storage' => !empty($this->config['ipfs']['web3storage']['token']),
            'custom' => !empty($this->config['ipfs']['custom']['api_url']),
            default => false
        };
    }

    /**
     * Get current IPFS provider
     */
    public function getIPFSProvider(): string {
        return $this->config['ipfs']['provider'];
    }

    /**
     * Get contract address
     */
    public function getContractAddress(): string {
        return $this->config['contract']['address'];
    }

    /**
     * Check if wallet is connected (from session)
     */
    public function isWalletConnected(): bool {
        return Web3::isWalletConnected();
    }

    /**
     * Get connected address from session
     */
    public function getConnectedAddress(): ?string {
        return Web3::getConnectedAddress();
    }
}
