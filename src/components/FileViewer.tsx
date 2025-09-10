import React, { useState, useEffect } from 'react';
import { File, Download, ExternalLink, Eye, EyeOff, Share2, UserMinus, UserPlus, Trash2 } from 'lucide-react';
import { shareFile, getSharedFileRecipients, revokeAccess, grantAccess, deleteFile } from '../utils/contract';
import { getIPFSUrl } from '../utils/ipfs';
import { ethers } from 'ethers';
import toast from 'react-hot-toast';

interface FileViewerProps {
  files: {
    ipfsHash: string;
    fileName: string;
    timestamp: number;
    owner: string;
    isPublic: boolean;
    description: string;
    fileType: string;
    fileSize: number;
    sharedBy?: string;
    sharedAt?: number;
    hasAccess?: boolean;
  }[];
  onToggleVisibility?: (index: number) => void;
  signer?: ethers.Signer;
  showShareButton?: boolean;
  onFileDeleted?: () => void;
}

interface Recipient {
  address: string;
  hasAccess: boolean;
}

export const FileViewer: React.FC<FileViewerProps> = ({ 
  files, 
  onToggleVisibility, 
  signer,
  showShareButton = false,
  onFileDeleted
}) => {
  const [sharingIndex, setSharingIndex] = useState<number | null>(null);
  const [recipientAddress, setRecipientAddress] = useState('');
  const [recipients, setRecipients] = useState<{ [key: number]: Recipient[] }>({});
  const [showRecipients, setShowRecipients] = useState<number | null>(null);
  const [deletingIndex, setDeletingIndex] = useState<number | null>(null);
  const [isSharing, setIsSharing] = useState(false);

  // Filter out files where access has been revoked
  const accessibleFiles = files.filter(file => file.hasAccess !== false);

  const loadRecipients = async (fileIndex: number) => {
    if (!signer) return;
    try {
      const fileRecipients = await getSharedFileRecipients(signer, fileIndex);
      setRecipients(prev => ({ ...prev, [fileIndex]: fileRecipients }));
    } catch (error) {
      console.error('Error loading recipients:', error);
      toast.error('Failed to load recipients');
    }
  };

  const handleShare = async (index: number) => {
    if (!signer || !recipientAddress) {
      toast.error('Please enter a recipient address');
      return;
    }

    if (!ethers.isAddress(recipientAddress)) {
      toast.error('Invalid Ethereum address');
      return;
    }

    setIsSharing(true);

    try {
      // First attempt to share the file
      const tx = await shareFile(signer, recipientAddress, index);
      
      // Show pending toast
      toast.loading('Transaction pending...', { id: 'share-pending' });
      
      // Wait for transaction confirmation
      await tx.wait();
      
      // Update toast to success
      toast.success('File shared successfully!', { id: 'share-pending' });
      
      // Reset form
      setSharingIndex(null);
      setRecipientAddress('');
      loadRecipients(index);
    } catch (error: any) {
      console.error('Error sharing file:', error);
      
      // Handle specific error cases
      if (error.code === 'ACTION_REJECTED') {
        toast.error('Transaction was rejected');
      } else if (error.code === 4001) {
        toast.error('Transaction was rejected by user');
      } else if (error.message?.includes('insufficient funds')) {
        toast.error('Insufficient funds for transaction');
      } else if (error.message?.includes('gas required exceeds allowance')) {
        toast.error('Transaction would exceed gas limit');
      } else {
        toast.error('Failed to share file. Please try again.');
      }
    } finally {
      setIsSharing(false);
      toast.dismiss('share-pending');
    }
  };

  const handleDelete = async (index: number) => {
    if (!signer) return;
    try {
      setDeletingIndex(index);
      await deleteFile(signer, index);
      toast.success('File deleted successfully');
      if (onFileDeleted) {
        onFileDeleted();
      }
    } catch (error) {
      console.error('Error deleting file:', error);
      toast.error('Failed to delete file');
    } finally {
      setDeletingIndex(null);
    }
  };

  const handleRevokeAccess = async (fileIndex: number, recipientAddress: string) => {
    if (!signer) return;
    try {
      await revokeAccess(signer, recipientAddress, fileIndex);
      toast.success('Access revoked successfully');
      loadRecipients(fileIndex);
    } catch (error) {
      console.error('Error revoking access:', error);
      toast.error('Failed to revoke access');
    }
  };

  const handleGrantAccess = async (fileIndex: number, recipientAddress: string) => {
    if (!signer) return;
    try {
      await grantAccess(signer, recipientAddress, fileIndex);
      toast.success('Access granted successfully');
      loadRecipients(fileIndex);
    } catch (error) {
      console.error('Error granting access:', error);
      toast.error('Failed to grant access');
    }
  };

  const getFileType = (fileName: string) => {
    const extension = fileName.split('.').pop()?.toLowerCase();
    if (['jpg', 'jpeg', 'png', 'gif'].includes(extension || '')) return 'image';
    if (['pdf'].includes(extension || '')) return 'pdf';
    if (['mp4', 'webm'].includes(extension || '')) return 'video';
    if (['mp3', 'wav'].includes(extension || '')) return 'audio';
    return 'other';
  };

  const formatDate = (timestamp: number) => {
    return new Date(timestamp * 1000).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const handleDownload = async (url: string, fileName: string) => {
    try {
      const response = await fetch(url);
      const blob = await response.blob();
      const downloadUrl = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = downloadUrl;
      link.download = fileName;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
      console.error('Download failed:', error);
      toast.error('Failed to download file');
    }
  };

  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      {accessibleFiles.length === 0 ? (
        <div className="col-span-full text-center py-12">
          <File className="w-16 h-16 mx-auto text-gray-400 mb-4" />
          <p className="text-gray-500 dark:text-gray-400">No files found</p>
        </div>
      ) : (
        accessibleFiles.map((file, index) => (
          <div key={index} className="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div className="p-4">
              <div className="flex items-start justify-between mb-4">
                <div className="flex items-center gap-3">
                  <div className="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <File className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div>
                    <h3 className="font-medium text-gray-900 dark:text-gray-100 truncate max-w-[200px]">
                      {file.fileName}
                    </h3>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                      {formatDate(file.timestamp)}
                    </p>
                    <p className="text-sm text-gray-500 dark:text-gray-400">
                      {formatFileSize(file.fileSize)}
                    </p>
                    {file.sharedBy && (
                      <p className="text-sm text-gray-500 dark:text-gray-400">
                        Shared by: {`${file.sharedBy.slice(0, 6)}...${file.sharedBy.slice(-4)}`}
                      </p>
                    )}
                  </div>
                </div>
                <div className="flex gap-2">
                  {onToggleVisibility && (
                    <button
                      onClick={() => onToggleVisibility(index)}
                      className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                      title={file.isPublic ? 'Make Private' : 'Make Public'}
                    >
                      {file.isPublic ? (
                        <Eye className="w-5 h-5 text-green-500" />
                      ) : (
                        <EyeOff className="w-5 h-5 text-gray-500" />
                      )}
                    </button>
                  )}
                  {showShareButton && (
                    <>
                      <button
                        onClick={() => {
                          setSharingIndex(sharingIndex === index ? null : index);
                          setShowRecipients(null);
                        }}
                        className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                        title="Share File"
                      >
                        <Share2 className="w-5 h-5 text-blue-500" />
                      </button>
                      <button
                        onClick={() => {
                          setShowRecipients(showRecipients === index ? null : index);
                          setSharingIndex(null);
                          loadRecipients(index);
                        }}
                        className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                        title="Manage Recipients"
                      >
                        <UserPlus className="w-5 h-5 text-purple-500" />
                      </button>
                      <button
                        onClick={() => handleDelete(index)}
                        disabled={deletingIndex === index}
                        className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                        title="Delete File"
                      >
                        <Trash2 className={`w-5 h-5 ${
                          deletingIndex === index ? 'text-gray-400' : 'text-red-500'
                        }`} />
                      </button>
                    </>
                  )}
                  {file.sharedBy && (
                    <button
                      onClick={() => handleRevokeAccess(index, file.sharedBy!)}
                      className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors"
                      title="Revoke Access"
                    >
                      <UserMinus className="w-5 h-5 text-red-500" />
                    </button>
                  )}
                </div>
              </div>

              {sharingIndex === index && (
                <div className="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                  <input
                    type="text"
                    placeholder="Enter wallet address"
                    value={recipientAddress}
                    onChange={(e) => setRecipientAddress(e.target.value)}
                    className="w-full p-2 mb-2 border rounded-lg dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                    disabled={isSharing}
                  />
                  <button
                    onClick={() => handleShare(index)}
                    disabled={isSharing || !recipientAddress}
                    className={`w-full py-2 ${
                      isSharing || !recipientAddress
                        ? 'bg-blue-400 cursor-not-allowed'
                        : 'bg-blue-600 hover:bg-blue-700'
                    } text-white rounded-lg transition-colors`}
                  >
                    {isSharing ? 'Sharing...' : 'Share'}
                  </button>
                </div>
              )}

              {showRecipients === index && recipients[index] && (
                <div className="mb-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                  <h4 className="font-medium text-gray-900 dark:text-gray-100 mb-2">Recipients</h4>
                  {recipients[index].length === 0 ? (
                    <p className="text-sm text-gray-500 dark:text-gray-400">No recipients yet</p>
                  ) : (
                    <div className="space-y-2">
                      {recipients[index].map((recipient, rIndex) => (
                        <div key={rIndex} className="flex items-center justify-between">
                          <span className="text-sm text-gray-600 dark:text-gray-300">
                            {`${recipient.address.slice(0, 6)}...${recipient.address.slice(-4)}`}
                          </span>
                          <button
                            onClick={() => recipient.hasAccess 
                              ? handleRevokeAccess(index, recipient.address)
                              : handleGrantAccess(index, recipient.address)
                            }
                            className={`p-1 rounded-lg transition-colors ${
                              recipient.hasAccess
                                ? 'bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50'
                                : 'bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-800/50'
                            }`}
                          >
                            {recipient.hasAccess ? (
                              <UserMinus className="w-4 h-4 text-red-600 dark:text-red-400" />
                            ) : (
                              <UserPlus className="w-4 h-4 text-green-600 dark:text-green-400" />
                            )}
                          </button>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}

              {getFileType(file.fileName) === 'image' && (
                <div className="relative h-40 mb-4 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                  <img
                    src={getIPFSUrl(file.ipfsHash)}
                    alt={file.fileName}
                    className="w-full h-full object-cover"
                    onError={(e) => {
                      (e.target as HTMLImageElement).src = 'https://via.placeholder.com/400x300?text=Image+Not+Found';
                    }}
                  />
                </div>
              )}

              {getFileType(file.fileName) === 'video' && (
                <div className="relative h-40 mb-4 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
                  <video
                    src={getIPFSUrl(file.ipfsHash)}
                    controls
                    className="w-full h-full object-cover"
                  />
                </div>
              )}

              {getFileType(file.fileName) === 'audio' && (
                <div className="mb-4">
                  <audio
                    src={getIPFSUrl(file.ipfsHash)}
                    controls
                    className="w-full"
                  />
                </div>
              )}

              <div className="flex gap-2">
                <a
                  href={getIPFSUrl(file.ipfsHash)}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex-1 flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  <ExternalLink className="w-4 h-4" />
                  View
                </a>
                <button
                  onClick={() => handleDownload(getIPFSUrl(file.ipfsHash), file.fileName)}
                  className="flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                  <Download className="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        ))
      )}
    </div>
  );
};