<?php

namespace DecentraShare\Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Contract {
    private string $contractAddress;
    private array $contractABI;
    private Client $client;
    private string $rpcUrl;

    public function __construct(array $config) {
        $this->contractAddress = $config['contract']['address'];
        $this->contractABI = $config['contract']['abi'];
        $this->rpcUrl = $config['web3']['rpc_url'];
        $this->client = new Client();
    }

    /**
     * Get user files from contract
     * 
     * @param string $userAddress Ethereum address
     * @return array Array of file objects
     * @throws \Exception
     */
    public function getUserFiles(string $userAddress): array {
        try {
            if (!Web3::isValidEthereumAddress($userAddress)) {
                throw new \Exception('Invalid Ethereum address');
            }

            // Call smart contract view function
            $result = $this->callContractFunction('getUserFiles', [$userAddress]);

            // Process and return files
            return $this->processFileResults($result);

        } catch (\Exception $e) {
            throw new \Exception('Error loading files: ' . $e->getMessage());
        }
    }

    /**
     * Get shared files for a user
     * 
     * @param string $userAddress Ethereum address
     * @return array Array of shared file objects with metadata
     * @throws \Exception
     */
    public function getSharedFiles(string $userAddress): array {
        try {
            if (!Web3::isValidEthereumAddress($userAddress)) {
                throw new \Exception('Invalid Ethereum address');
            }

            // Call smart contract view function
            $result = $this->callContractFunction('getSharedFiles', [$userAddress]);

            // Process and return shared files
            return $this->processSharedFileResults($result);

        } catch (\Exception $e) {
            throw new \Exception('Error loading shared files: ' . $e->getMessage());
        }
    }

    /**
     * Upload file to contract
     * 
     * Note: In a real implementation, this would be called from frontend with signed transaction
     * PHP acts as a backend to verify and store the transaction metadata
     * 
     * @param string $ipfsHash
     * @param string $fileName
     * @param string $fileType
     * @param int $fileSize
     * @param string $userAddress
     * @return array Transaction details
     */
    public function uploadFileToContract(string $ipfsHash, string $fileName, string $fileType, int $fileSize, string $userAddress): array {
        try {
            if (!IPFS::isValidIPFSHash($ipfsHash)) {
                throw new \Exception('Invalid IPFS hash');
            }

            if (!Web3::isValidEthereumAddress($userAddress)) {
                throw new \Exception('Invalid Ethereum address');
            }

            // Store file metadata in database (would be implemented in real app)
            $fileMetadata = [
                'ipfsHash' => $ipfsHash,
                'fileName' => $fileName,
                'fileType' => $fileType,
                'fileSize' => $fileSize,
                'owner' => $userAddress,
                'uploadedAt' => time(),
                'isPublic' => true,
                'description' => ''
            ];

            // In a real implementation, this would be stored in a database
            // For now, we return the prepared transaction data

            return [
                'success' => true,
                'message' => 'File metadata prepared for blockchain upload',
                'metadata' => $fileMetadata
            ];

        } catch (\Exception $e) {
            throw new \Exception('Error uploading file: ' . $e->getMessage());
        }
    }

    /**
     * Delete file from contract
     * 
     * @param int $fileIndex
     * @param string $userAddress
     * @return array
     */
    public function deleteFile(int $fileIndex, string $userAddress): array {
        if (!Web3::isValidEthereumAddress($userAddress)) {
            throw new \Exception('Invalid Ethereum address');
        }

        return [
            'success' => true,
            'message' => 'File deletion prepared for blockchain',
            'fileIndex' => $fileIndex,
            'executor' => $userAddress
        ];
    }

    /**
     * Share file with recipient
     * 
     * @param string $recipientAddress
     * @param int $fileIndex
     * @param string $userAddress
     * @return array
     */
    public function shareFile(string $recipientAddress, int $fileIndex, string $userAddress): array {
        if (!Web3::isValidEthereumAddress($recipientAddress) || !Web3::isValidEthereumAddress($userAddress)) {
            throw new \Exception('Invalid Ethereum address');
        }

        return [
            'success' => true,
            'message' => 'File sharing prepared for blockchain',
            'recipient' => $recipientAddress,
            'fileIndex' => $fileIndex,
            'sharedBy' => $userAddress
        ];
    }

    /**
     * Revoke access to file
     * 
     * @param string $recipientAddress
     * @param int $fileIndex
     * @param string $userAddress
     * @return array
     */
    public function revokeAccess(string $recipientAddress, int $fileIndex, string $userAddress): array {
        if (!Web3::isValidEthereumAddress($recipientAddress) || !Web3::isValidEthereumAddress($userAddress)) {
            throw new \Exception('Invalid Ethereum address');
        }

        return [
            'success' => true,
            'message' => 'Access revocation prepared for blockchain',
            'recipient' => $recipientAddress,
            'fileIndex' => $fileIndex,
            'revokedBy' => $userAddress
        ];
    }

    /**
     * Grant access to file
     * 
     * @param string $recipientAddress
     * @param int $fileIndex
     * @param string $userAddress
     * @return array
     */
    public function grantAccess(string $recipientAddress, int $fileIndex, string $userAddress): array {
        if (!Web3::isValidEthereumAddress($recipientAddress) || !Web3::isValidEthereumAddress($userAddress)) {
            throw new \Exception('Invalid Ethereum address');
        }

        return [
            'success' => true,
            'message' => 'Access grant prepared for blockchain',
            'recipient' => $recipientAddress,
            'fileIndex' => $fileIndex,
            'grantedBy' => $userAddress
        ];
    }

    /**
     * Get recipients of a shared file
     * 
     * @param int $fileIndex
     * @return array Recipients and their access status
     */
    public function getSharedFileRecipients(int $fileIndex): array {
        return [
            'fileIndex' => $fileIndex,
            'recipients' => [],
            'accessStatus' => []
        ];
    }

    /**
     * Toggle file visibility (public/private)
     * 
     * @param int $fileIndex
     * @param string $userAddress
     * @return array
     */
    public function toggleFileVisibility(int $fileIndex, string $userAddress): array {
        if (!Web3::isValidEthereumAddress($userAddress)) {
            throw new \Exception('Invalid Ethereum address');
        }

        return [
            'success' => true,
            'message' => 'File visibility toggle prepared for blockchain',
            'fileIndex' => $fileIndex,
            'owner' => $userAddress
        ];
    }

    /**
     * Call contract function via RPC
     * 
     * @param string $functionName
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    private function callContractFunction(string $functionName, array $parameters = []): mixed {
        try {
            // In a real implementation, use web3.php or ethers library
            // For now, return mock data for demonstration

            return match ($functionName) {
                'getUserFiles' => $this->mockGetUserFiles($parameters[0]),
                'getSharedFiles' => $this->mockGetSharedFiles($parameters[0]),
                default => throw new \Exception('Unknown function: ' . $functionName)
            };

        } catch (GuzzleException $e) {
            throw new \Exception('RPC call failed: ' . $e->getMessage());
        }
    }

    /**
     * Mock get user files (in production, use actual contract call)
     */
    private function mockGetUserFiles(string $userAddress): array {
        return [];
    }

    /**
     * Mock get shared files (in production, use actual contract call)
     */
    private function mockGetSharedFiles(string $userAddress): array {
        return [
            'files' => [],
            'sharedBy' => [],
            'sharedAt' => [],
            'hasAccess' => []
        ];
    }

    /**
     * Process file results
     */
    private function processFileResults(array $result): array {
        if (empty($result)) {
            return [];
        }

        $files = [];
        if (isset($result[0]) && is_array($result[0])) {
            foreach ($result[0] as $file) {
                $files[] = $this->normalizeFileData($file);
            }
        }

        return $files;
    }

    /**
     * Process shared file results
     */
    private function processSharedFileResults(array $result): array {
        if (empty($result)) {
            return [];
        }

        $files = [];
        $sharedFiles = $result['files'] ?? [];
        $sharedBy = $result['sharedBy'] ?? [];
        $sharedAt = $result['sharedAt'] ?? [];
        $hasAccess = $result['hasAccess'] ?? [];

        foreach ($sharedFiles as $index => $file) {
            $fileData = $this->normalizeFileData($file);
            $fileData['sharedBy'] = $sharedBy[$index] ?? null;
            $fileData['sharedAt'] = $sharedAt[$index] ?? null;
            $fileData['hasAccess'] = $hasAccess[$index] ?? true;

            // Filter out files where access has been revoked
            if ($fileData['hasAccess'] !== false) {
                $files[] = $fileData;
            }
        }

        return $files;
    }

    /**
     * Normalize file data structure
     */
    private function normalizeFileData(array $file): array {
        return [
            'ipfsHash' => $file['ipfsHash'] ?? '',
            'fileName' => $file['fileName'] ?? '',
            'timestamp' => (int)($file['timestamp'] ?? 0),
            'owner' => $file['owner'] ?? '',
            'isPublic' => (bool)($file['isPublic'] ?? false),
            'description' => $file['description'] ?? '',
            'fileType' => $file['fileType'] ?? '',
            'fileSize' => (int)($file['fileSize'] ?? 0)
        ];
    }

    /**
     * Get contract address
     */
    public function getContractAddress(): string {
        return $this->contractAddress;
    }

    /**
     * Get contract ABI
     */
    public function getContractABI(): array {
        return $this->contractABI;
    }
}
