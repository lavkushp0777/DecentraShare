import React, { useState, useEffect } from 'react';
import { FileUpload } from './components/FileUpload';
import { FileViewer } from './components/FileViewer';
import { IPFSConfig } from './components/IPFSConfig';
import { connectWallet } from './utils/web3';
import { getUserFiles, getSharedFiles, toggleFileVisibility } from './utils/contract';
import { Wallet, Sun, Moon, FolderOpen, Share2, Upload, LogOut } from 'lucide-react';
import toast, { Toaster } from 'react-hot-toast';
import { ethers } from 'ethers';

function App() {
  const [address, setAddress] = useState<string | null>(null);
  const [signer, setSigner] = useState<ethers.Signer | null>(null);
  const [isDarkMode, setIsDarkMode] = useState(false);
  const [activeTab, setActiveTab] = useState<'upload' | 'folders' | 'shared'>('upload');
  const [userFiles, setUserFiles] = useState<any[]>([]);
  const [sharedFiles, setSharedFiles] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [isConnectingWallet, setIsConnectingWallet] = useState(false);

  const handleConnect = async () => {
    if (isConnectingWallet) return;
    
    try {
      setIsConnectingWallet(true);
      const { address, signer } = await connectWallet();
      setAddress(address);
      setSigner(signer);
      toast.success('Wallet connected successfully!');
      loadUserFiles(address, signer);
      loadSharedFiles(address, signer);
    } catch (error) {
      // Check for specific error codes
      if (error && typeof error === 'object' && 'code' in error) {
        if (error.code === -32002) {
          toast.error('Please check your wallet - there\'s a pending connection request waiting for approval');
        } else {
          toast.error('Failed to connect wallet');
        }
      } else if (error && typeof error === 'object' && 'error' in error && error.error && typeof error.error === 'object' && 'code' in error.error) {
        if (error.error.code === -32002) {
          toast.error('Please check your wallet - there\'s a pending connection request waiting for approval');
        } else {
          toast.error('Failed to connect wallet');
        }
      } else {
        toast.error('Failed to connect wallet');
      }
      console.error(error);
    } finally {
      setIsConnectingWallet(false);
    }
  };

  const handleDisconnect = () => {
    setAddress(null);
    setSigner(null);
    setUserFiles([]);
    setSharedFiles([]);
    toast.success('Wallet disconnected');
  };

  const loadUserFiles = async (userAddress: string, userSigner: ethers.Signer) => {
    try {
      setIsLoading(true);
      const files = await getUserFiles(userSigner, userAddress);
      if (Array.isArray(files)) {
        setUserFiles(files);
      } else {
        setUserFiles([]);
        console.warn('Invalid files data received:', files);
      }
    } catch (error) {
      console.error('Error loading files:', error);
      toast.error('Failed to load files');
      setUserFiles([]);
    } finally {
      setIsLoading(false);
    }
  };

  const loadSharedFiles = async (userAddress: string, userSigner: ethers.Signer) => {
    try {
      const files = await getSharedFiles(userSigner, userAddress);
      if (Array.isArray(files)) {
        setSharedFiles(files);
      } else {
        setSharedFiles([]);
        console.warn('Invalid shared files data received:', files);
      }
    } catch (error) {
      console.error('Error loading shared files:', error);
      toast.error('Failed to load shared files');
      setSharedFiles([]);
    }
  };

  const handleToggleVisibility = async (fileIndex: number) => {
    if (!signer) return;

    try {
      await toggleFileVisibility(signer, fileIndex);
      toast.success('File visibility updated');
      if (address) {
        loadUserFiles(address, signer);
      }
    } catch (error) {
      console.error('Error toggling visibility:', error);
      toast.error('Failed to update file visibility');
    }
  };

  useEffect(() => {
    document.documentElement.classList.toggle('dark', isDarkMode);
  }, [isDarkMode]);

  const TabButton = ({ tab, icon: Icon, label }: { tab: typeof activeTab; icon: any; label: string }) => (
    <button
      onClick={() => setActiveTab(tab)}
      className={`flex items-center gap-2 px-6 py-3 rounded-lg transition-all duration-200 ${
        activeTab === tab
          ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30'
          : 'hover:bg-gray-100 dark:hover:bg-gray-800'
      }`}
    >
      <Icon className="w-5 h-5" />
      <span className="font-medium">{label}</span>
    </button>
  );

  return (
    <div className={`min-h-screen ${isDarkMode ? 'dark bg-gray-900' : 'bg-gray-50'}`}>
      <div className="max-w-7xl mx-auto px-4 py-8">
        <div className="flex flex-col gap-8">
          <div className="flex justify-between items-center">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                <Share2 className="w-6 h-6 text-white" />
              </div>
              <h1 className={`text-3xl font-bold ${isDarkMode ? 'text-white' : 'text-gray-900'}`}>
                DecentraShare
              </h1>
            </div>
            
            <div className="flex items-center gap-4">
              <IPFSConfig />
              <button
                onClick={() => setIsDarkMode(!isDarkMode)}
                className="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
              >
                {isDarkMode ? <Sun className="w-5 h-5" /> : <Moon className="w-5 h-5" />}
              </button>
              
              {!address ? (
                <button
                  onClick={handleConnect}
                  disabled={isConnectingWallet}
                  className={`flex items-center gap-2 px-6 py-2 rounded-lg transition-colors shadow-lg ${
                    isConnectingWallet
                      ? 'bg-gray-400 cursor-not-allowed'
                      : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/30'
                  } text-white`}
                >
                  {isConnectingWallet ? (
                    <>
                      <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                      Connecting...
                    </>
                  ) : (
                    <>
                      <Wallet className="w-5 h-5" />
                      Connect Wallet
                    </>
                  )}
                </button>
              ) : (
                <div className="flex items-center gap-2">
                  <div className="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    <div className="w-2 h-2 bg-green-500 rounded-full" />
                    {`${address.slice(0, 6)}...${address.slice(-4)}`}
                  </div>
                  <button
                    onClick={handleDisconnect}
                    className="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-lg shadow-red-500/30"
                  >
                    <LogOut className="w-5 h-5" />
                    Disconnect
                  </button>
                </div>
              )}
            </div>
          </div>

          {address && signer ? (
            <div className="space-y-6">
              <div className="flex gap-4 p-2 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
                <TabButton tab="upload" icon={Upload} label="Upload Files" />
                <TabButton tab="folders" icon={FolderOpen} label="My Files" />
                <TabButton tab="shared" icon={Share2} label="Shared Files" />
              </div>

              <div className="bg-white dark:bg-gray-800 rounded-2xl shadow-sm p-6">
                {activeTab === 'upload' && (
                  <FileUpload 
                    signer={signer} 
                    onFileUploaded={() => loadUserFiles(address, signer)} 
                  />
                )}
                {activeTab === 'folders' && (
                  <div className="space-y-6">
                    <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
                      My Files
                    </h2>
                    {isLoading ? (
                      <div className="text-center py-12">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p className="mt-4 text-gray-600 dark:text-gray-400">Loading files...</p>
                      </div>
                    ) : (
                      <FileViewer 
                        files={userFiles}
                        onToggleVisibility={handleToggleVisibility}
                        signer={signer}
                        showShareButton={true}
                        onFileDeleted={() => loadUserFiles(address, signer)}
                      />
                    )}
                  </div>
                )}
                {activeTab === 'shared' && (
                  <div className="space-y-6">
                    <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
                      Shared Files
                    </h2>
                    {isLoading ? (
                      <div className="text-center py-12">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                        <p className="mt-4 text-gray-600 dark:text-gray-400">Loading shared files...</p>
                      </div>
                    ) : (
                      <FileViewer 
                        files={sharedFiles}
                        signer={signer}
                      />
                    )}
                  </div>
                )}
              </div>
            </div>
          ) : (
            <div className="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl shadow-sm">
              <Share2 className="w-16 h-16 mx-auto text-gray-400 mb-6" />
              <h2 className={`text-2xl font-bold mb-2 ${isDarkMode ? 'text-white' : 'text-gray-900'}`}>
                Welcome to DecentraShare
              </h2>
              <p className={`text-xl mb-8 ${isDarkMode ? 'text-gray-300' : 'text-gray-600'}`}>
                Connect your wallet to start sharing files securely
              </p>
              <button
                onClick={handleConnect}
                disabled={isConnectingWallet}
                className={`flex items-center gap-2 px-8 py-3 rounded-lg transition-colors mx-auto shadow-lg ${
                  isConnectingWallet
                    ? 'bg-gray-400 cursor-not-allowed'
                    : 'bg-blue-600 hover:bg-blue-700 shadow-blue-500/30'
                } text-white`}
              >
                {isConnectingWallet ? (
                  <>
                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                    Connecting...
                  </>
                ) : (
                  <>
                    <Wallet className="w-5 h-5" />
                    Connect Wallet
                  </>
                )}
              </button>
            </div>
          )}
        </div>
      </div>
      <Toaster position="bottom-right" />
    </div>
  );
}

export default App;