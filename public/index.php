<?php
/**
 * DecentraShare - Main Application Entry Point
 * Handles routing, session management, and wallet connection
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap application
use DecentraShare\Application;
use DecentraShare\Utils\Web3;

$app = Application::getInstance(__DIR__ . '/../src');

$config = $app->getConfig();
$ipfs = $app->getIPFS();
$contract = $app->getContract();

// Handle API requests and form submissions
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'upload' && isset($_FILES['file'])) {
    // Handle file upload
    header('Content-Type: application/json');
    
    if (!Web3::isWalletConnected()) {
        http_response_code(401);
        echo json_encode(['error' => 'Wallet not connected']);
        exit;
    }

    try {
        $file = $_FILES['file'];
        $tempPath = $file['tmp_name'];
        $fileName = $file['name'];
        $fileType = $file['type'];
        $fileSize = $file['size'];

        // Validate file
        if ($fileSize > 100 * 1024 * 1024) {
            throw new Exception('File size exceeds 100MB limit');
        }

        // Upload to IPFS
        $ipfsHash = $ipfs->uploadFile($tempPath);

        // Upload metadata to contract (would need signed transaction from frontend)
        $userAddress = Web3::getConnectedAddress();
        $result = $contract->uploadFileToContract($ipfsHash, $fileName, $fileType, $fileSize, $userAddress);

        echo json_encode([
            'success' => true,
            'ipfsHash' => $ipfsHash,
            'message' => 'File uploaded successfully'
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

if ($action === 'verify_wallet_signature') {
    // Handle wallet signature verification
    header('Content-Type: application/json');
    
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['address']) || !isset($data['signature']) || !isset($data['message'])) {
            throw new Exception('Missing required fields');
        }

        $result = Web3::verifyWalletSignature(
            $data['address'],
            $data['signature'],
            $data['message']
        );

        echo json_encode($result);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($action === 'disconnect') {
    // Handle wallet disconnection
    Web3::disconnectWallet();
    header('Location: /');
    exit;
}

if ($action === 'toggle_dark_mode') {
    // Toggle dark mode
    $_SESSION['dark_mode'] = !($_SESSION['dark_mode'] ?? false);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
    exit;
}

// Serve the main application
header('Content-Type: text/html; charset=utf-8');

// Check if IPFS is configured
$isIPFSConfigured = $app->isIPFSConfigured();

// Render main layout
require_once __DIR__ . '/../templates/layout.php';
