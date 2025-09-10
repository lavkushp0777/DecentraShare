import { ethers } from 'ethers';

export const connectWallet = async () => {
  try {
    if (!window.ethereum) {
      throw new Error('Please install MetaMask');
    }

    const provider = new ethers.BrowserProvider(window.ethereum);
    const accounts = await provider.send("eth_requestAccounts", []);
    const signer = await provider.getSigner();
    
    return { 
      address: accounts[0],
      signer 
    };
  } catch (error) {
    console.error('Error connecting wallet:', error);
    throw error;
  }
};