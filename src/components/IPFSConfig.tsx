import React, { useState, useEffect } from 'react';
import { Settings, Check, AlertCircle, ExternalLink } from 'lucide-react';
import toast from 'react-hot-toast';

export const IPFSConfig: React.FC = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [provider, setProvider] = useState('pinata');
  const [credentials, setCredentials] = useState({
    pinataApiKey: '',
    pinataSecretKey: '',
    web3StorageToken: '',
    customApiUrl: '',
    customGatewayUrl: ''
  });
  const [isConfigured, setIsConfigured] = useState(false);

  useEffect(() => {
    // Check if IPFS is configured
    const currentProvider = import.meta.env.VITE_IPFS_PROVIDER || 'pinata';
    setProvider(currentProvider);
    
    const checkConfiguration = () => {
      switch (currentProvider) {
        case 'pinata':
          return !!(import.meta.env.VITE_PINATA_API_KEY && import.meta.env.VITE_PINATA_SECRET_KEY);
        case 'web3storage':
          return !!import.meta.env.VITE_WEB3_STORAGE_TOKEN;
        case 'custom':
          return !!import.meta.env.VITE_IPFS_API_URL;
        default:
          return false;
      }
    };

    setIsConfigured(checkConfiguration());
  }, []);

  const handleSave = () => {
    // In a real application, you would save these to a backend or local storage
    // For now, we'll just show instructions to the user
    toast.success('Configuration saved! Please restart the application for changes to take effect.');
    setIsOpen(false);
  };

  const ConfigStatus = () => (
    <div className="flex items-center gap-2">
      {isConfigured ? (
        <>
          <Check className="w-4 h-4 text-green-500" />
          <span className="text-sm text-green-600 dark:text-green-400">IPFS Configured</span>
        </>
      ) : (
        <>
          <AlertCircle className="w-4 h-4 text-orange-500" />
          <span className="text-sm text-orange-600 dark:text-orange-400">IPFS Not Configured</span>
        </>
      )}
    </div>
  );

  return (
    <>
      <button
        onClick={() => setIsOpen(true)}
        className="flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
      >
        <Settings className="w-4 h-4" />
        <ConfigStatus />
      </button>

      {isOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div className="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div className="p-6">
              <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100">
                  IPFS Configuration
                </h2>
                <button
                  onClick={() => setIsOpen(false)}
                  className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                  ×
                </button>
              </div>

              <div className="space-y-6">
                <div className="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                  <h3 className="font-medium text-blue-900 dark:text-blue-100 mb-2">
                    How to Configure IPFS
                  </h3>
                  <p className="text-sm text-blue-800 dark:text-blue-200 mb-3">
                    To use your own IPFS account, you need to update the environment variables in your .env file:
                  </p>
                  <ol className="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-decimal list-inside">
                    <li>Choose an IPFS provider (Pinata recommended for beginners)</li>
                    <li>Get your API credentials from the provider</li>
                    <li>Update the .env file with your credentials</li>
                    <li>Restart the application</li>
                  </ol>
                </div>

                <div className="space-y-4">
                  <h3 className="font-medium text-gray-900 dark:text-gray-100">
                    Provider Options
                  </h3>

                  <div className="space-y-4">
                    <div className="border dark:border-gray-700 rounded-lg p-4">
                      <div className="flex items-center justify-between mb-3">
                        <h4 className="font-medium text-gray-900 dark:text-gray-100">
                          Pinata (Recommended)
                        </h4>
                        <a
                          href="https://pinata.cloud"
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center gap-1 text-blue-600 hover:text-blue-700 text-sm"
                        >
                          Sign up <ExternalLink className="w-3 h-3" />
                        </a>
                      </div>
                      <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Easy to use, reliable, and has a generous free tier.
                      </p>
                      <div className="bg-gray-50 dark:bg-gray-700 p-3 rounded text-sm font-mono">
                        VITE_IPFS_PROVIDER=pinata<br />
                        VITE_PINATA_API_KEY=your_api_key<br />
                        VITE_PINATA_SECRET_KEY=your_secret_key
                      </div>
                    </div>

                    <div className="border dark:border-gray-700 rounded-lg p-4">
                      <div className="flex items-center justify-between mb-3">
                        <h4 className="font-medium text-gray-900 dark:text-gray-100">
                          Web3.Storage
                        </h4>
                        <a
                          href="https://web3.storage"
                          target="_blank"
                          rel="noopener noreferrer"
                          className="flex items-center gap-1 text-blue-600 hover:text-blue-700 text-sm"
                        >
                          Sign up <ExternalLink className="w-3 h-3" />
                        </a>
                      </div>
                      <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Free decentralized storage powered by Filecoin and IPFS.
                      </p>
                      <div className="bg-gray-50 dark:bg-gray-700 p-3 rounded text-sm font-mono">
                        VITE_IPFS_PROVIDER=web3storage<br />
                        VITE_WEB3_STORAGE_TOKEN=your_token
                      </div>
                    </div>

                    <div className="border dark:border-gray-700 rounded-lg p-4">
                      <h4 className="font-medium text-gray-900 dark:text-gray-100 mb-3">
                        Custom IPFS Node
                      </h4>
                      <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Use your own IPFS node or a custom gateway.
                      </p>
                      <div className="bg-gray-50 dark:bg-gray-700 p-3 rounded text-sm font-mono">
                        VITE_IPFS_PROVIDER=custom<br />
                        VITE_IPFS_API_URL=https://your-node.com:5001<br />
                        VITE_IPFS_GATEWAY_URL=https://your-gateway.com/ipfs/
                      </div>
                    </div>
                  </div>
                </div>

                <div className="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                  <h4 className="font-medium text-yellow-900 dark:text-yellow-100 mb-2">
                    Important Notes
                  </h4>
                  <ul className="text-sm text-yellow-800 dark:text-yellow-200 space-y-1 list-disc list-inside">
                    <li>Never commit your API keys to version control</li>
                    <li>Keep your .env file secure and private</li>
                    <li>Restart the application after changing environment variables</li>
                    <li>Test your configuration by uploading a small file first</li>
                  </ul>
                </div>
              </div>

              <div className="flex justify-end gap-3 mt-6 pt-6 border-t dark:border-gray-700">
                <button
                  onClick={() => setIsOpen(false)}
                  className="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
};