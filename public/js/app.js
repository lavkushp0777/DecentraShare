/**
 * DecentraShare - Main Application JavaScript
 * Handles wallet connection, dark mode, and UI interactions
 */

class DecentraShare {
    constructor() {
        this.provider = null;
        this.signer = null;
        this.address = null;
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.checkWalletConnection();
    }

    setupEventListeners() {
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => this.toggleDarkMode());
        }

        // Connect wallet button
        const connectBtn = document.getElementById('connectWallet');
        if (connectBtn) {
            connectBtn.addEventListener('click', () => this.connectWallet());
        }

        // IPFS Config button
        const ipfsConfigBtn = document.getElementById('ipfsConfig');
        if (ipfsConfigBtn) {
            ipfsConfigBtn.addEventListener('click', () => this.openIPFSConfig());
        }
    }

    toggleDarkMode() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        
        if (isDark) {
            html.classList.remove('dark');
        } else {
            html.classList.add('dark');
        }

        // Save preference to server
        fetch('/?action=toggle_dark_mode', { method: 'POST' });
    }

    async connectWallet() {
        try {
            // Check if MetaMask is installed
            if (typeof window.ethereum === 'undefined') {
                this.showNotification('Please install MetaMask', 'error');
                window.open('https://metamask.io/', '_blank');
                return;
            }

            // Request wallet accounts
            const accounts = await window.ethereum.request({ method: 'eth_requestAccounts' });
            const address = accounts[0];

            // Create message to sign
            const message = `Sign this message to connect your wallet to DecentraShare.\n\nAddress: ${address}\nTime: ${new Date().toISOString()}`;

            // Request signature
            const signature = await window.ethereum.request({
                method: 'personal_sign',
                params: [message, address]
            });

            // Send signature to server for verification
            const response = await fetch('/?action=verify_wallet_signature', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    address: address,
                    signature: signature,
                    message: message
                })
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Wallet connected successfully!', 'success');
                this.address = address;
                
                // Reload page to show connected state
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                this.showNotification('Failed to connect wallet: ' + (result.error || 'Unknown error'), 'error');
            }

        } catch (error) {
            console.error('Wallet connection error:', error);
            
            if (error.code === -32002) {
                this.showNotification('Please check your wallet - there\'s a pending connection request', 'error');
            } else if (error.code === 'INVALID_ARGUMENT') {
                this.showNotification('Transaction rejected', 'error');
            } else {
                this.showNotification('Failed to connect wallet: ' + error.message, 'error');
            }
        }
    }

    async checkWalletConnection() {
        // Check if we have an active session with a connected wallet
        // The server will handle this via PHP sessions
    }

    openIPFSConfig() {
        this.showNotification('IPFS Configuration - Configure in .env file', 'info');
    }

    showNotification(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        const colors = {
            'error': 'bg-red-500',
            'success': 'bg-green-500',
            'info': 'bg-blue-500'
        };

        toast.className = `${colors[type] || colors['info']} text-white px-4 py-3 rounded-lg mb-2 shadow-lg`;
        toast.textContent = message;

        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    async uploadFile(file, onProgress) {
        if (!this.address) {
            this.showNotification('Wallet not connected', 'error');
            return null;
        }

        const formData = new FormData();
        formData.append('action', 'upload');
        formData.append('file', file);

        try {
            const xhr = new XMLHttpRequest();

            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    if (onProgress) {
                        onProgress(percentComplete);
                    }
                }
            });

            // Handle completion
            return new Promise((resolve, reject) => {
                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                resolve(response.ipfsHash);
                            } else {
                                reject(new Error(response.error || 'Upload failed'));
                            }
                        } catch (e) {
                            reject(e);
                        }
                    } else {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            reject(new Error(response.error || 'Upload failed'));
                        } catch (e) {
                            reject(new Error('Upload failed: ' + xhr.status));
                        }
                    }
                });

                xhr.addEventListener('error', () => {
                    reject(new Error('Upload failed'));
                });

                xhr.open('POST', '/');
                xhr.send(formData);
            });

        } catch (error) {
            this.showNotification('Upload error: ' + error.message, 'error');
            return null;
        }
    }
}

// Initialize application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.app = new DecentraShare();
});

// Utility functions
function formatAddress(address) {
    if (!address) return '';
    return address.slice(0, 6) + '...' + address.slice(-4);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

function formatDate(timestamp) {
    return new Date(timestamp * 1000).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}
