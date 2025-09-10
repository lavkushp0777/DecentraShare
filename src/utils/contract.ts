import { ethers } from 'ethers';

const CONTRACT_ADDRESS = '0x70dD105c6D5F4be9aa803618abfCbBC5Fa1B1B82';

const CONTRACT_ABI = [
  {
    "inputs": [
      {
        "internalType": "string",
        "name": "_ipfsHash",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "_fileName",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "_description",
        "type": "string"
      },
      {
        "internalType": "string",
        "name": "_fileType",
        "type": "string"
      },
      {
        "internalType": "uint256",
        "name": "_fileSize",
        "type": "uint256"
      },
      {
        "internalType": "bool",
        "name": "_isPublic",
        "type": "bool"
      }
    ],
    "name": "uploadFile",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_fileIndex",
        "type": "uint256"
      }
    ],
    "name": "deleteFile",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_recipient",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_fileIndex",
        "type": "uint256"
      }
    ],
    "name": "shareFile",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_recipient",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_fileIndex",
        "type": "uint256"
      }
    ],
    "name": "revokeAccess",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_recipient",
        "type": "address"
      },
      {
        "internalType": "uint256",
        "name": "_fileIndex",
        "type": "uint256"
      }
    ],
    "name": "grantAccess",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_fileIndex",
        "type": "uint256"
      }
    ],
    "name": "getSharedFileRecipients",
    "outputs": [
      {
        "internalType": "address[]",
        "name": "recipients",
        "type": "address[]"
      },
      {
        "internalType": "bool[]",
        "name": "accessStatus",
        "type": "bool[]"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_user",
        "type": "address"
      }
    ],
    "name": "getUserFiles",
    "outputs": [
      {
        "components": [
          {
            "internalType": "string",
            "name": "ipfsHash",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "fileName",
            "type": "string"
          },
          {
            "internalType": "uint256",
            "name": "timestamp",
            "type": "uint256"
          },
          {
            "internalType": "address",
            "name": "owner",
            "type": "address"
          },
          {
            "internalType": "bool",
            "name": "isPublic",
            "type": "bool"
          },
          {
            "internalType": "string",
            "name": "description",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "fileType",
            "type": "string"
          },
          {
            "internalType": "uint256",
            "name": "fileSize",
            "type": "uint256"
          }
        ],
        "internalType": "struct FileSharing.File[]",
        "name": "",
        "type": "tuple[]"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "address",
        "name": "_user",
        "type": "address"
      }
    ],
    "name": "getSharedFiles",
    "outputs": [
      {
        "components": [
          {
            "internalType": "string",
            "name": "ipfsHash",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "fileName",
            "type": "string"
          },
          {
            "internalType": "uint256",
            "name": "timestamp",
            "type": "uint256"
          },
          {
            "internalType": "address",
            "name": "owner",
            "type": "address"
          },
          {
            "internalType": "bool",
            "name": "isPublic",
            "type": "bool"
          },
          {
            "internalType": "string",
            "name": "description",
            "type": "string"
          },
          {
            "internalType": "string",
            "name": "fileType",
            "type": "string"
          },
          {
            "internalType": "uint256",
            "name": "fileSize",
            "type": "uint256"
          }
        ],
        "internalType": "struct FileSharing.File[]",
        "name": "files",
        "type": "tuple[]"
      },
      {
        "internalType": "address[]",
        "name": "sharedBy",
        "type": "address[]"
      },
      {
        "internalType": "uint256[]",
        "name": "sharedAt",
        "type": "uint256[]"
      },
      {
        "internalType": "bool[]",
        "name": "hasAccess",
        "type": "bool[]"
      }
    ],
    "stateMutability": "view",
    "type": "function"
  },
  {
    "inputs": [
      {
        "internalType": "uint256",
        "name": "_fileIndex",
        "type": "uint256"
      }
    ],
    "name": "toggleFileVisibility",
    "outputs": [],
    "stateMutability": "nonpayable",
    "type": "function"
  }
];

export const getContract = async (signer: ethers.Signer) => {
  try {
    return new ethers.Contract(CONTRACT_ADDRESS, CONTRACT_ABI, signer);
  } catch (error) {
    console.error('Error getting contract:', error);
    throw error;
  }
};

export const uploadFileToContract = async (
  signer: ethers.Signer,
  ipfsHash: string,
  fileName: string,
  fileType: string,
  fileSize: number
) => {
  try {
    const contract = await getContract(signer);
    const description = ''; // Optional description
    const isPublic = true; // Default to public
    
    const tx = await contract.uploadFile(
      ipfsHash,
      fileName,
      description,
      fileType,
      fileSize,
      isPublic,
      { gasLimit: 5000000 }
    );
    await tx.wait();
    return tx.hash;
  } catch (error) {
    console.error('Error uploading file to contract:', error);
    throw error;
  }
};

export const deleteFile = async (
  signer: ethers.Signer,
  fileIndex: number
) => {
  try {
    const contract = await getContract(signer);
    const tx = await contract.deleteFile(fileIndex, { gasLimit: 5000000 });
    await tx.wait();
    return tx.hash;
  } catch (error) {
    console.error('Error deleting file:', error);
    throw error;
  }
};

export const shareFile = async (
  signer: ethers.Signer,
  recipientAddress: string,
  fileIndex: number
) => {
  try {
    const contract = await getContract(signer);
    const tx = await contract.shareFile(recipientAddress, fileIndex, { gasLimit: 5000000 });
    await tx.wait();
    return tx.hash;
  } catch (error) {
    console.error('Error sharing file:', error);
    throw error;
  }
};

export const revokeAccess = async (
  signer: ethers.Signer,
  recipientAddress: string,
  fileIndex: number
) => {
  try {
    const contract = await getContract(signer);
    const tx = await contract.revokeAccess(recipientAddress, fileIndex, { gasLimit: 5000000 });
    await tx.wait();
    return tx.hash;
  } catch (error: any) {
    console.error('Error revoking access:', error);
    if (error.code === 'INSUFFICIENT_FUNDS') {
      throw new Error('Insufficient funds to execute the transaction');
    } else if (error.message?.includes('gas required exceeds allowance')) {
      throw new Error('Transaction requires more gas than allowed');
    } else if (error.code === 'ACTION_REJECTED') {
      throw new Error('Transaction was rejected by user');
    }
    throw error;
  }
};

export const grantAccess = async (
  signer: ethers.Signer,
  recipientAddress: string,
  fileIndex: number
) => {
  try {
    const contract = await getContract(signer);
    const tx = await contract.grantAccess(recipientAddress, fileIndex, { gasLimit: 5000000 });
    await tx.wait();
    return tx.hash;
  } catch (error) {
    console.error('Error granting access:', error);
    throw error;
  }
};

export const getSharedFileRecipients = async (
  signer: ethers.Signer,
  fileIndex: number
) => {
  try {
    const contract = await getContract(signer);
    const [recipients, accessStatus] = await contract.getSharedFileRecipients(fileIndex);
    return recipients.map((recipient: string, index: number) => ({
      address: recipient,
      hasAccess: accessStatus[index]
    }));
  } catch (error) {
    console.error('Error getting shared file recipients:', error);
    return [];
  }
};

export const getUserFiles = async (signer: ethers.Signer, address: string) => {
  try {
    const contract = await getContract(signer);
    const files = await contract.getUserFiles(address);

    if (!files || !Array.isArray(files)) {
      return [];
    }

    return files.map((file: any) => ({
      ipfsHash: String(file.ipfsHash),
      fileName: String(file.fileName),
      timestamp: Number(file.timestamp),
      owner: String(file.owner),
      isPublic: Boolean(file.isPublic),
      description: String(file.description),
      fileType: String(file.fileType),
      fileSize: Number(file.fileSize)
    }));
  } catch (error) {
    console.error('Error getting user files:', error);
    return [];
  }
};

export const getSharedFiles = async (signer: ethers.Signer, address: string) => {
  try {
    const contract = await getContract(signer);
    const [files, sharedBy, sharedAt, hasAccess] = await contract.getSharedFiles(address);

    if (!files || !Array.isArray(files)) {
      return [];
    }

    return files.map((file: any, index: number) => ({
      ipfsHash: String(file.ipfsHash),
      fileName: String(file.fileName),
      timestamp: Number(file.timestamp),
      owner: String(file.owner),
      isPublic: Boolean(file.isPublic),
      description: String(file.description),
      fileType: String(file.fileType),
      fileSize: Number(file.fileSize),
      sharedBy: String(sharedBy[index]),
      sharedAt: Number(sharedAt[index]),
      hasAccess: Boolean(hasAccess[index])
    }));
  } catch (error) {
    console.error('Error getting shared files:', error);
    return [];
  }
};

export const toggleFileVisibility = async (
  signer: ethers.Signer,
  fileIndex: number
) => {
  try {
    const contract = await getContract(signer);
    const tx = await contract.toggleFileVisibility(fileIndex, { gasLimit: 5000000 });
    await tx.wait();
    return tx.hash;
  } catch (error) {
    console.error('Error toggling file visibility:', error);
    throw error;
  }
};