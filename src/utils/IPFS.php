<?php

namespace DecentraShare\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class IPFS {
    private string $provider;
    private array $config;
    private Client $client;

    public function __construct(array $config) {
        $this->config = $config;
        $this->provider = $config['provider'];
        $this->client = new Client();
    }

    /**
     * Upload file to IPFS
     * 
     * @param string $filePath Path to file to upload
     * @param callable|null $onProgress Progress callback
     * @return string IPFS hash
     * @throws \Exception
     */
    public function uploadFile(string $filePath, callable $onProgress = null): string {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found: ' . $filePath);
        }

        return match ($this->provider) {
            'pinata' => $this->uploadToPinata($filePath, $onProgress),
            'web3storage' => $this->uploadToWeb3Storage($filePath, $onProgress),
            'custom' => $this->uploadToCustomIPFS($filePath, $onProgress),
            default => throw new \Exception('Unsupported IPFS provider: ' . $this->provider)
        };
    }

    /**
     * Upload file to Pinata
     * 
     * @param string $filePath
     * @param callable|null $onProgress
     * @return string IPFS hash
     * @throws \Exception
     */
    private function uploadToPinata(string $filePath, callable $onProgress = null): string {
        $apiKey = $this->config['pinata']['api_key'];
        $secretKey = $this->config['pinata']['secret_key'];

        if (empty($apiKey) || empty($secretKey)) {
            throw new \Exception('Pinata API credentials not configured');
        }

        try {
            $fileHandle = fopen($filePath, 'r');
            $fileName = basename($filePath);
            $fileSize = filesize($filePath);

            $metadata = json_encode([
                'name' => $fileName,
                'keyvalues' => [
                    'uploadedAt' => date('c'),
                    'size' => $fileSize
                ]
            ]);

            $options = json_encode([
                'cidVersion' => 1,
                'wrapWithDirectory' => false
            ]);

            $response = $this->client->post(
                $this->config['pinata']['api_url'],
                [
                    'headers' => [
                        'pinata_api_key' => $apiKey,
                        'pinata_secret_api_key' => $secretKey
                    ],
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $fileHandle,
                            'filename' => $fileName
                        ],
                        [
                            'name' => 'pinataMetadata',
                            'contents' => $metadata
                        ],
                        [
                            'name' => 'pinataOptions',
                            'contents' => $options
                        ]
                    ]
                ]
            );

            fclose($fileHandle);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['IpfsHash'])) {
                if ($onProgress) {
                    $onProgress(100);
                }
                return $body['IpfsHash'];
            }

            throw new \Exception('Failed to get IPFS hash from Pinata response');

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to upload to Pinata: ' . $e->getMessage());
        }
    }

    /**
     * Upload file to Web3.Storage
     * 
     * @param string $filePath
     * @param callable|null $onProgress
     * @return string IPFS hash
     * @throws \Exception
     */
    private function uploadToWeb3Storage(string $filePath, callable $onProgress = null): string {
        $token = $this->config['web3storage']['token'];

        if (empty($token)) {
            throw new \Exception('Web3.Storage token not configured');
        }

        try {
            $fileHandle = fopen($filePath, 'r');
            $fileName = basename($filePath);

            $response = $this->client->post(
                $this->config['web3storage']['api_url'],
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token
                    ],
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $fileHandle,
                            'filename' => $fileName
                        ]
                    ]
                ]
            );

            fclose($fileHandle);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['cid'])) {
                if ($onProgress) {
                    $onProgress(100);
                }
                return $body['cid'];
            }

            throw new \Exception('Failed to get IPFS hash from Web3.Storage response');

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to upload to Web3.Storage: ' . $e->getMessage());
        }
    }

    /**
     * Upload file to custom IPFS node
     * 
     * @param string $filePath
     * @param callable|null $onProgress
     * @return string IPFS hash
     * @throws \Exception
     */
    private function uploadToCustomIPFS(string $filePath, callable $onProgress = null): string {
        try {
            $fileHandle = fopen($filePath, 'r');
            $fileName = basename($filePath);

            $response = $this->client->post(
                $this->config['custom']['api_url'] . '/api/v0/add',
                [
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $fileHandle,
                            'filename' => $fileName
                        ]
                    ]
                ]
            );

            fclose($fileHandle);

            $body = json_decode($response->getBody()->getContents(), true);

            if (isset($body['Hash'])) {
                if ($onProgress) {
                    $onProgress(100);
                }
                return $body['Hash'];
            }

            throw new \Exception('Failed to get IPFS hash from custom node');

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to upload to custom IPFS: ' . $e->getMessage());
        }
    }

    /**
     * Get IPFS gateway URL for a hash
     * 
     * @param string $ipfsHash
     * @return string Full gateway URL
     */
    public function getIPFSUrl(string $ipfsHash): string {
        $gatewayUrl = $this->config['custom']['gateway_url'];
        return rtrim($gatewayUrl, '/') . '/' . ltrim($ipfsHash, '/');
    }

    /**
     * Validate IPFS hash format
     * 
     * @param string $hash
     * @return bool
     */
    public static function isValidIPFSHash(string $hash): bool {
        // CIDv0 (base58): 46 characters starting with Qm
        // CIDv1 (base32): variable length
        return preg_match('/^(Qm|bafy)[a-zA-Z0-9]+$/', $hash) === 1;
    }

    /**
     * Download file from IPFS
     * 
     * @param string $ipfsHash
     * @param string $outputPath
     * @return bool
     * @throws \Exception
     */
    public function downloadFile(string $ipfsHash, string $outputPath): bool {
        if (!self::isValidIPFSHash($ipfsHash)) {
            throw new \Exception('Invalid IPFS hash format');
        }

        try {
            $url = $this->getIPFSUrl($ipfsHash);
            $response = $this->client->get($url);

            file_put_contents($outputPath, $response->getBody());
            return true;

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to download from IPFS: ' . $e->getMessage());
        }
    }
}
