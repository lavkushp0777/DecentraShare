import axios from 'axios';

// Get configuration from environment variables
const IPFS_PROVIDER = import.meta.env.VITE_IPFS_PROVIDER || 'pinata';
const PINATA_API_KEY = import.meta.env.VITE_PINATA_API_KEY;
const PINATA_SECRET_KEY = import.meta.env.VITE_PINATA_SECRET_KEY;
const WEB3_STORAGE_TOKEN = import.meta.env.VITE_WEB3_STORAGE_TOKEN;
const IPFS_API_URL = import.meta.env.VITE_IPFS_API_URL;
const IPFS_GATEWAY_URL = import.meta.env.VITE_IPFS_GATEWAY_URL || 'https://gateway.pinata.cloud/ipfs/';

export const uploadToIPFS = async (file: File, onProgress?: (progress: number) => void): Promise<string> => {
  switch (IPFS_PROVIDER) {
    case 'pinata':
      return uploadToPinata(file, onProgress);
    case 'web3storage':
      return uploadToWeb3Storage(file, onProgress);
    case 'custom':
      return uploadToCustomIPFS(file, onProgress);
    default:
      throw new Error(`Unsupported IPFS provider: ${IPFS_PROVIDER}`);
  }
};

const uploadToPinata = async (file: File, onProgress?: (progress: number) => void): Promise<string> => {
  if (!PINATA_API_KEY || !PINATA_SECRET_KEY) {
    throw new Error('Pinata API credentials not configured. Please set VITE_PINATA_API_KEY and VITE_PINATA_SECRET_KEY in your .env file');
  }

  try {
    const formData = new FormData();
    formData.append('file', file);

    // Add metadata
    const metadata = JSON.stringify({
      name: file.name,
      keyvalues: {
        uploadedAt: new Date().toISOString(),
        size: file.size,
        type: file.type
      }
    });
    formData.append('pinataMetadata', metadata);

    // Add options
    const options = JSON.stringify({
      cidVersion: 1,
      wrapWithDirectory: false
    });
    formData.append('pinataOptions', options);

    const response = await axios.post(
      'https://api.pinata.cloud/pinning/pinFileToIPFS',
      formData,
      {
        headers: {
          'pinata_api_key': PINATA_API_KEY,
          'pinata_secret_api_key': PINATA_SECRET_KEY,
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          if (progressEvent.total && onProgress) {
            const progress = (progressEvent.loaded / progressEvent.total) * 100;
            onProgress(progress);
          }
        }
      }
    );

    if (response.status !== 200) {
      throw new Error('Failed to upload to Pinata');
    }

    return response.data.IpfsHash;
  } catch (error) {
    console.error('Error uploading to Pinata:', error);
    throw new Error('Failed to upload to Pinata: ' + (error as Error).message);
  }
};

const uploadToWeb3Storage = async (file: File, onProgress?: (progress: number) => void): Promise<string> => {
  if (!WEB3_STORAGE_TOKEN) {
    throw new Error('Web3.Storage token not configured. Please set VITE_WEB3_STORAGE_TOKEN in your .env file');
  }

  try {
    const formData = new FormData();
    formData.append('file', file);

    const response = await axios.post(
      'https://api.web3.storage/upload',
      formData,
      {
        headers: {
          'Authorization': `Bearer ${WEB3_STORAGE_TOKEN}`,
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          if (progressEvent.total && onProgress) {
            const progress = (progressEvent.loaded / progressEvent.total) * 100;
            onProgress(progress);
          }
        }
      }
    );

    if (response.status !== 200) {
      throw new Error('Failed to upload to Web3.Storage');
    }

    return response.data.cid;
  } catch (error) {
    console.error('Error uploading to Web3.Storage:', error);
    throw new Error('Failed to upload to Web3.Storage: ' + (error as Error).message);
  }
};

const uploadToCustomIPFS = async (file: File, onProgress?: (progress: number) => void): Promise<string> => {
  if (!IPFS_API_URL) {
    throw new Error('Custom IPFS API URL not configured. Please set VITE_IPFS_API_URL in your .env file');
  }

  try {
    const formData = new FormData();
    formData.append('file', file);

    const response = await axios.post(
      `${IPFS_API_URL}/api/v0/add`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        onUploadProgress: (progressEvent) => {
          if (progressEvent.total && onProgress) {
            const progress = (progressEvent.loaded / progressEvent.total) * 100;
            onProgress(progress);
          }
        }
      }
    );

    if (response.status !== 200) {
      throw new Error('Failed to upload to custom IPFS node');
    }

    return response.data.Hash;
  } catch (error) {
    console.error('Error uploading to custom IPFS:', error);
    throw new Error('Failed to upload to custom IPFS: ' + (error as Error).message);
  }
};

export const getIPFSUrl = (hash: string): string => {
  return `${IPFS_GATEWAY_URL}${hash}`;
};