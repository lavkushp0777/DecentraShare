<?php
/**
 * Shared Files Template
 * Equivalent to FileViewer.tsx for shared files
 */

use DecentraShare\Utils\Web3;

$address = Web3::getConnectedAddress();

// TODO: Get shared files from contract
$sharedFiles = [];
?>

<div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
        Shared Files
    </h2>

    <?php if (empty($sharedFiles)): ?>
        <div class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.839 10.319 13.158 7 16.5 7c2.126 0 4.088.855 5.514 2.252M5 19H1.72m16.78-4.368A8.995 8.995 0 0019.5 15c2.126 0 4.088.855 5.514 2.252m2.986-4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33m2.986 4.617c.537.63.856 1.466.856 2.365 0 3.314-2.686 6-6 6-2.346 0-4.362-1.355-5.514-3.33"/>
            </svg>
            <p class="text-gray-600 dark:text-gray-400">No files shared with you yet</p>
        </div>
    <?php else: ?>
        <div class="grid gap-4">
            <?php foreach ($sharedFiles as $index => $file): ?>
                <?php if ($file['hasAccess'] !== false): // Filter out revoked access ?>
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
                                        Shared by <?php echo Web3::formatAddress($file['sharedBy'] ?? ''); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-500">
                                        <?php echo date('M d, Y', $file['sharedAt'] ?? 0); ?>
                                    </p>
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
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
