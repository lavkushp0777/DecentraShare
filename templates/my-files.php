<?php
/**
 * My Files Template
 * Equivalent to FileViewer.tsx for user files
 */

use DecentraShare\Utils\Web3;

$address = Web3::getConnectedAddress();
$isDarkMode = $_SESSION['dark_mode'] ?? false;

// TODO: Get user files from contract
$userFiles = [];
?>

<div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
        My Files
    </h2>

    <?php if (empty($userFiles)): ?>
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">No files uploaded yet</p>
        </div>
    <?php else: ?>
        <div class="grid gap-4">
            <?php foreach ($userFiles as $index => $file): ?>
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4 flex-1">
                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 dark:text-gray-100 truncate">
                                    <?php echo htmlspecialchars($file['fileName']); ?>
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <?php echo number_format($file['fileSize'] / 1024 / 1024, 2); ?> MB
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                    <?php echo date('M d, Y', $file['timestamp']); ?>
                                </p>
                                <div class="mt-2 flex gap-2 flex-wrap">
                                    <span class="inline-block px-2 py-1 text-xs rounded-full <?php echo $file['isPublic'] ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'; ?>">
                                        <?php echo $file['isPublic'] ? 'Public' : 'Private'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <a href="<?php echo htmlspecialchars($file['ipfsHash']); ?>" target="_blank" class="p-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4m-4-6l6-6m0 0l-6 6m6-6v12"/>
                                </svg>
                            </a>
                            <button class="p-2 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors" title="Download">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </button>
                            <button class="p-2 text-gray-600 dark:text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors" title="Share">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.319 13.158 7 16.5 7c2.126 0 4.088.855 5.514 2.252M5 19H1.72m16.78-4.368A8.995 8.995 0 0019.5 15c2.126 0 4.088.855 5.514 2.252m2.986-4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33m2.986 4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33"/>
                                </svg>
                            </button>
                            <button class="p-2 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
