<?php
/**
 * File Upload Template
 * Equivalent to FileUpload.tsx
 */
?>

<div class="space-y-6">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">Upload File to IPFS</h2>

    <!-- Drag & Drop Zone -->
    <div id="dropZone" class="border-2 border-dashed rounded-xl p-12 text-center cursor-pointer transition-all duration-200 border-gray-300 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-600">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <p class="text-xl font-medium mb-2 text-gray-600 dark:text-gray-300">
            Drag & drop your file here
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Maximum file size: 100MB
        </p>
        <input type="file" id="fileInput" class="hidden" accept="*/*" />
    </div>

    <!-- File Preview -->
    <div id="filePreview" class="space-y-6" style="display: none;">
        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p id="fileName" class="font-medium text-gray-900 dark:text-gray-100"></p>
                        <p id="fileSize" class="text-sm text-gray-600 dark:text-gray-400"></p>
                    </div>
                </div>
                <button type="button" id="removeFile" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div id="uploadProgress" style="display: none;" class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Uploading...</span>
                <span id="progressPercent" class="text-sm text-gray-600 dark:text-gray-400">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div id="progressBar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
            </div>
        </div>

        <!-- Upload Button -->
        <button type="button" id="uploadBtn" class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium">
            Upload to IPFS
        </button>
    </div>

    <!-- Success Message -->
    <div id="successMessage" style="display: none;" class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
        <p class="text-green-800 dark:text-green-200">File uploaded successfully!</p>
        <p id="ipfsHashDisplay" class="text-sm text-green-700 dark:text-green-300 mt-2 break-all font-mono"></p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const filePreview = document.getElementById('filePreview');
    const uploadProgress = document.getElementById('uploadProgress');
    const successMessage = document.getElementById('successMessage');
    const uploadBtn = document.getElementById('uploadBtn');
    const removeFile = document.getElementById('removeFile');
    let selectedFile = null;

    // Click to select file
    dropZone.addEventListener('click', () => fileInput.click());

    // Drag and drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        handleFileSelect(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', (e) => {
        handleFileSelect(e.target.files);
    });

    function handleFileSelect(files) {
        if (files.length === 0) return;

        const file = files[0];
        const maxSize = 100 * 1024 * 1024; // 100MB

        if (file.size > maxSize) {
            showNotification('File size must be less than 100MB', 'error');
            return;
        }

        selectedFile = file;

        // Update preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        dropZone.style.display = 'none';
        filePreview.style.display = 'block';
    }

    removeFile.addEventListener('click', () => {
        selectedFile = null;
        fileInput.value = '';
        dropZone.style.display = 'block';
        filePreview.style.display = 'none';
        uploadProgress.style.display = 'none';
        successMessage.style.display = 'none';
    });

    uploadBtn.addEventListener('click', async () => {
        if (!selectedFile) return;

        uploadProgress.style.display = 'block';
        successMessage.style.display = 'none';
        uploadBtn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('file', selectedFile);

        try {
            // TODO: Send to PHP backend for IPFS upload
            showNotification('Upload feature requires wallet signature', 'info');
        } catch (error) {
            showNotification('Upload failed: ' + error.message, 'error');
        } finally {
            uploadBtn.disabled = false;
        }
    });
});

function showNotification(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const bgColor = type === 'error' ? 'bg-red-500' : type === 'success' ? 'bg-green-500' : 'bg-blue-500';
    
    toast.className = `${bgColor} text-white px-4 py-3 rounded-lg mb-2 shadow-lg`;
    toast.textContent = message;
    
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
