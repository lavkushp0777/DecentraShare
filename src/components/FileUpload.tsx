import React, { useCallback, useState } from 'react';
import { useDropzone } from 'react-dropzone';
import { Upload, File, X } from 'lucide-react';
import { uploadToIPFS } from '../utils/ipfs';
import { uploadFileToContract } from '../utils/contract';
import toast from 'react-hot-toast';
import { ethers } from 'ethers';

interface FileUploadProps {
  signer: ethers.Signer;
  onFileUploaded: () => void;
}

export const FileUpload: React.FC<FileUploadProps> = ({ signer, onFileUploaded }) => {
  const [uploadProgress, setUploadProgress] = useState<number>(0);
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);

  const onDrop = useCallback(async (acceptedFiles: File[]) => {
    if (acceptedFiles[0].size > 100 * 1024 * 1024) {
      toast.error('File size must be less than 100MB');
      return;
    }
    const file = acceptedFiles[0];
    setSelectedFile(file);
  }, []);

  const handleUpload = async () => {
    if (!selectedFile || !signer) return;

    try {
      setIsUploading(true);
      setUploadProgress(0);
      
      // Upload to IPFS
      const ipfsHash = await uploadToIPFS(selectedFile, (progress) => {
        setUploadProgress(progress);
      });

      toast.success('File uploaded to IPFS successfully!');

      try {
        // Upload reference to smart contract
        const txHash = await uploadFileToContract(
          signer,
          ipfsHash,
          selectedFile.name,
          selectedFile.type,
          selectedFile.size
        );
        toast.success('File reference added to blockchain!');
        console.log('Transaction Hash:', txHash);
        onFileUploaded();
      } catch (error) {
        console.error('Contract error:', error);
        toast.error('Failed to add file reference to blockchain');
      }

      setSelectedFile(null);
      setUploadProgress(0);
    } catch (error) {
      console.error('Upload error:', error);
      toast.error('Failed to upload file to IPFS');
    } finally {
      setIsUploading(false);
    }
  };

  const { getRootProps, getInputProps, isDragActive } = useDropzone({ 
    onDrop,
    multiple: false,
    maxSize: 100 * 1024 * 1024 // 100MB
  });

  return (
    <div className="space-y-6">
      {!selectedFile ? (
        <div
          {...getRootProps()}
          className={`border-2 border-dashed rounded-xl p-12 text-center cursor-pointer transition-all duration-200
            ${isDragActive 
              ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
              : 'border-gray-300 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-600'
            }`}
        >
          <input {...getInputProps()} />
          <Upload className={`w-16 h-16 mx-auto mb-4 ${
            isDragActive ? 'text-blue-500' : 'text-gray-400'
          }`} />
          <p className={`text-xl font-medium mb-2 ${
            isDragActive ? 'text-blue-600 dark:text-blue-400' : 'text-gray-600 dark:text-gray-300'
          }`}>
            {isDragActive ? 'Drop the file here' : 'Drag & drop your file here'}
          </p>
          <p className="text-sm text-gray-500 dark:text-gray-400">
            Maximum file size: 100MB
          </p>
        </div>
      ) : (
        <div className="space-y-6">
          <div className="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6">
            <div className="flex items-start justify-between">
              <div className="flex items-center gap-4">
                <div className="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                  <File className="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                  <h3 className="font-medium text-gray-900 dark:text-gray-100">
                    {selectedFile.name}
                  </h3>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {(selectedFile.size / 1024 / 1024).toFixed(2)} MB
                  </p>
                  <p className="text-sm text-gray-500 dark:text-gray-400">
                    {selectedFile.type || 'Unknown type'}
                  </p>
                </div>
              </div>
              <button 
                onClick={() => setSelectedFile(null)}
                className="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-full transition-colors"
              >
                <X className="w-5 h-5 text-gray-500 dark:text-gray-400" />
              </button>
            </div>
          </div>

          <button
            onClick={handleUpload}
            disabled={isUploading}
            className="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-blue-500/30"
          >
            {isUploading ? 'Uploading...' : 'Upload to IPFS & Blockchain'}
          </button>

          {uploadProgress > 0 && uploadProgress < 100 && (
            <div>
              <div className="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div
                  className="h-full bg-blue-600 rounded-full transition-all duration-300"
                  style={{ width: `${uploadProgress}%` }}
                />
              </div>
              <p className="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center">
                Uploading: {Math.round(uploadProgress)}%
              </p>
            </div>
          )}
        </div>
      )}
    </div>
  );
};