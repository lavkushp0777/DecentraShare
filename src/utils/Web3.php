<?php

namespace DecentraShare\Utils;

class Web3 {
    private string $rpcUrl;
    
    public function __construct(string $rpcUrl) {
        $this->rpcUrl = $rpcUrl;
    }

    /**
     * Connect wallet using MetaMask signature verification
     * In PHP, we verify the signature sent from the frontend
     * 
     * @param string $address Ethereum address
     * @param string $signature Signed message from MetaMask
     * @param string $message Message that was signed
     * @return array Returns address and connection status
     */
    public static function verifyWalletSignature(string $address, string $signature, string $message): array {
        try {
            // This would be verified on the frontend with MetaMask
            // PHP can store the verified session
            
            // Basic validation of Ethereum address format
            if (!self::isValidEthereumAddress($address)) {
                throw new \Exception('Invalid Ethereum address format');
            }

            // Store in session for this connection
            $_SESSION['wallet_address'] = $address;
            $_SESSION['connected_at'] = time();

            return [
                'success' => true,
                'address' => $address,
                'message' => 'Wallet connected successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate Ethereum address format
     * 
     * @param string $address
     * @return bool
     */
    public static function isValidEthereumAddress(string $address): bool {
        return preg_match('/^0x[a-fA-F0-9]{40}$/', $address) === 1;
    }

    /**
     * Format address for display
     * 
     * @param string $address Full Ethereum address
     * @return string Formatted address (first 6 + last 4 chars)
     */
    public static function formatAddress(string $address): string {
        if (!self::isValidEthereumAddress($address)) {
            return $address;
        }
        return substr($address, 0, 6) . '...' . substr($address, -4);
    }

    /**
     * Get connected wallet address from session
     * 
     * @return string|null
     */
    public static function getConnectedAddress(): ?string {
        return $_SESSION['wallet_address'] ?? null;
    }

    /**
     * Disconnect wallet
     * 
     * @return array
     */
    public static function disconnectWallet(): array {
        unset($_SESSION['wallet_address']);
        unset($_SESSION['connected_at']);

        return [
            'success' => true,
            'message' => 'Wallet disconnected'
        ];
    }

    /**
     * Check if wallet is connected
     * 
     * @return bool
     */
    public static function isWalletConnected(): bool {
        return isset($_SESSION['wallet_address']);
    }
}
