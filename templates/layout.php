<?php
/**
 * Main application layout template
 * Equivalent to App.tsx
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use DecentraShare\Utils\Web3;

$isConnected = Web3::isWalletConnected();
$address = Web3::getConnectedAddress();
$isDarkMode = $_SESSION['dark_mode'] ?? false;
$activeTab = $_GET['tab'] ?? 'upload';
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $isDarkMode ? 'dark' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DecentraShare - Decentralized File Sharing</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>
<body class="<?php echo $isDarkMode ? 'dark bg-gray-900' : 'bg-gray-50'; ?>">
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="flex flex-col gap-8">
                <!-- Header -->
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.319 13.158 7 16.5 7c2.126 0 4.088.855 5.514 2.252M5 19H1.72m16.78-4.368A8.995 8.995 0 0019.5 15c2.126 0 4.088.855 5.514 2.252m2.986-4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33m2.986 4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33"/>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold <?php echo $isDarkMode ? 'text-white' : 'text-gray-900'; ?>">
                            DecentraShare
                        </h1>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- IPFS Config Button -->
                        <button id="ipfsConfig" class="flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm">Config</span>
                        </button>

                        <!-- Dark Mode Toggle -->
                        <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <?php if ($isDarkMode): ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1m-16 0H1m15.364 1.636l-.707.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            <?php else: ?>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                            <?php endif; ?>
                        </button>

                        <!-- Wallet Connection Button -->
                        <?php if (!$isConnected): ?>
                            <button id="connectWallet" class="flex items-center gap-2 px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-lg shadow-blue-500/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a7 7 0 013-6.17V3a1 1 0 011-1h12a1 1 0 011 1v3.17A7 7 0 1121 21h-1v-2a1 1 0 00-1-1h-4a1 1 0 00-1 1v2h-5v-2a1 1 0 00-1-1H4a1 1 0 00-1 1v2H2v-3a3 3 0 001-3v-1z"/>
                                </svg>
                                Connect Wallet
                            </button>
                        <?php else: ?>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span><?php echo \DecentraShare\Utils\Web3::formatAddress($address); ?></span>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <button type="submit" name="action" value="disconnect" class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-lg shadow-red-500/30">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Disconnect
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Main Content Area -->
                <?php if ($isConnected): ?>
                    <div class="space-y-6">
                        <!-- Tab Navigation -->
                        <div class="flex gap-4 p-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                            <a href="?tab=upload" class="flex items-center gap-2 px-6 py-3 rounded-lg transition-all duration-200 <?php echo $activeTab === 'upload' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="font-medium">Upload Files</span>
                            </a>
                            <a href="?tab=folders" class="flex items-center gap-2 px-6 py-3 rounded-lg transition-all duration-200 <?php echo $activeTab === 'folders' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                </svg>
                                <span class="font-medium">My Files</span>
                            </a>
                            <a href="?tab=shared" class="flex items-center gap-2 px-6 py-3 rounded-lg transition-all duration-200 <?php echo $activeTab === 'shared' ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.319 13.158 7 16.5 7c2.126 0 4.088.855 5.514 2.252M5 19H1.72m16.78-4.368A8.995 8.995 0 0019.5 15c2.126 0 4.088.855 5.514 2.252m2.986-4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33m2.986 4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33"/>
                                </svg>
                                <span class="font-medium">Shared Files</span>
                            </a>
                        </div>

                        <!-- Tab Content -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                            <?php
                            switch ($activeTab) {
                                case 'upload':
                                    include 'upload.php';
                                    break;
                                case 'folders':
                                    include 'my-files.php';
                                    break;
                                case 'shared':
                                    include 'shared-files.php';
                                    break;
                                default:
                                    include 'upload.php';
                            }
                            ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Welcome Screen -->
                    <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.319 13.158 7 16.5 7c2.126 0 4.088.855 5.514 2.252M5 19H1.72m16.78-4.368A8.995 8.995 0 0019.5 15c2.126 0 4.088.855 5.514 2.252m2.986-4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33m2.986 4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33"/>
                        </svg>
                        <h2 class="text-2xl font-bold mb-2 <?php echo $isDarkMode ? 'text-white' : 'text-gray-900'; ?>">
                            Welcome to DecentraShare
                        </h2>
                        <p class="text-xl mb-8 <?php echo $isDarkMode ? 'text-gray-300' : 'text-gray-600'; ?>">
                            Connect your wallet to start sharing files securely
                        </p>
                        <form method="POST">
                            <button type="submit" name="action" value="connect" class="flex items-center gap-2 px-8 py-3 rounded-lg mx-auto shadow-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-blue-500/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10a7 7 0 013-6.17V3a1 1 0 011-1h12a1 1 0 011 1v3.17A7 7 0 1121 21h-1v-2a1 1 0 00-1-1h-4a1 1 0 00-1 1v2h-5v-2a1 1 0 00-1-1H4a1 1 0 00-1 1v2H2v-3a3 3 0 001-3v-1z"/>
                                </svg>
                                Connect Wallet
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-right p-4"></div>

    <script src="/js/app.js"></script>
</body>
</html>
