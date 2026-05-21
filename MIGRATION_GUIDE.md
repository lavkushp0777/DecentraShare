# TypeScript to PHP Migration Guide

## For Developers

This guide explains the technical details of converting the DecentraShare application from TypeScript/React to PHP.

---

## 1. State Management Migration

### React Hooks → PHP Sessions

**BEFORE (React):**
```typescript
const [address, setAddress] = useState<string | null>(null);
const [isDarkMode, setIsDarkMode] = useState(false);
const [userFiles, setUserFiles] = useState<any[]>([]);

const handleConnect = async () => {
  const { address, signer } = await connectWallet();
  setAddress(address);
  setSigner(signer);
};
```

**AFTER (PHP):**
```php
// State stored in PHP session
$_SESSION['wallet_address'] = $address;
$_SESSION['dark_mode'] = true;

// State accessed directly in templates
$address = $_SESSION['wallet_address'] ?? null;
$isDarkMode = $_SESSION['dark_mode'] ?? false;

// Wallet connection handled with JavaScript
const signature = await window.ethereum.request({...});
fetch('/?action=verify_wallet_signature', {...});
```

---

## 2. Component Architecture

### React Components → PHP Templates

**BEFORE (React - FileUpload.tsx):**
```typescript
interface FileUploadProps {
  signer: ethers.Signer;
  onFileUploaded: () => void;
}

export const FileUpload: React.FC<FileUploadProps> = ({ signer, onFileUploaded }) => {
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);

  const handleUpload = async () => {
    const ipfsHash = await uploadToIPFS(selectedFile);
    onFileUploaded();
  };

  return (
    <div className="space-y-6">
      {/* JSX content */}
    </div>
  );
};
```

**AFTER (PHP - templates/upload.php):**
```php
<?php
// Template receives data from parent
$isDarkMode = $_SESSION['dark_mode'] ?? false;
?>

<div class="space-y-6">
  <h2 class="text-xl font-semibold">Upload File to IPFS</h2>
  <!-- HTML/PHP template content -->
  <form id="uploadForm">
    <input type="file" id="fileInput" />
    <button type="button" id="uploadBtn">Upload</button>
  </form>
</div>

<script>
// Event listeners handle user interactions
document.getElementById('uploadBtn').addEventListener('click', async () => {
  const file = document.getElementById('fileInput').files[0];
  await app.uploadFile(file);
});
</script>
```

---

## 3. Data Flow Changes

### React Props → Template Variables

**BEFORE (React):**
```typescript
interface FileViewerProps {
  files: File[];
  onToggleVisibility?: (index: number) => void;
  signer?: ethers.Signer;
}

const FileViewer: React.FC<FileViewerProps> = ({ files, onToggleVisibility, signer }) => {
  return (
    <div>
      {files.map((file, index) => (
        <FileCard key={index} file={file} onToggle={onToggleVisibility} />
      ))}
    </div>
  );
};
```

**AFTER (PHP):**
```php
<?php
// Data passed as template variables by including script
$files = $_SESSION['user_files'] ?? [];
$isDarkMode = $_SESSION['dark_mode'] ?? false;
?>

<div>
  <?php foreach ($files as $index => $file): ?>
    <div class="file-card">
      <!-- File display -->
    </div>
  <?php endforeach; ?>
</div>
```

---

## 4. API Communication

### ethers.js → Custom PHP + Guzzle

**BEFORE (TypeScript - web3.ts):**
```typescript
import { ethers } from 'ethers';

export const connectWallet = async () => {
  const provider = new ethers.BrowserProvider(window.ethereum);
  const accounts = await provider.send("eth_requestAccounts", []);
  return { address: accounts[0], signer: await provider.getSigner() };
};

export const getUserFiles = async (signer: ethers.Signer, address: string) => {
  const contract = new ethers.Contract(address, ABI, signer);
  return await contract.getUserFiles(address);
};
```

**AFTER (PHP - src/Utils/Web3.php):**
```php
namespace DecentraShare\Utils;

class Web3 {
  public static function verifyWalletSignature(string $address, string $signature, string $message): array {
    if (!self::isValidEthereumAddress($address)) {
      throw new Exception('Invalid address');
    }
    $_SESSION['wallet_address'] = $address;
    return ['success' => true, 'address' => $address];
  }

  public static function isValidEthereumAddress(string $address): bool {
    return preg_match('/^0x[a-fA-F0-9]{40}$/', $address) === 1;
  }
}

// In src/Utils/Contract.php:
class Contract {
  public function getUserFiles(string $userAddress): array {
    // Call smart contract or return mock data
    return $this->callContractFunction('getUserFiles', [$userAddress]);
  }
}
```

---

## 5. HTTP Controllers

### React Hooks → PHP Request Handler

**BEFORE (React - useEffect):**
```typescript
useEffect(() => {
  const loadUserFiles = async () => {
    try {
      setIsLoading(true);
      const files = await getUserFiles(signer, address);
      setUserFiles(files);
    } catch (error) {
      toast.error('Failed to load files');
    } finally {
      setIsLoading(false);
    }
  };

  if (address && signer) {
    loadUserFiles();
  }
}, [address, signer]);
```

**AFTER (PHP - public/index.php):**
```php
// Handle POST request
if ($action === 'upload' && isset($_FILES['file'])) {
  try {
    if (!Web3::isWalletConnected()) {
      http_response_code(401);
      echo json_encode(['error' => 'Not connected']);
      exit;
    }

    $file = $_FILES['file'];
    $ipfsHash = $ipfs->uploadFile($file['tmp_name']);

    echo json_encode([
      'success' => true,
      'ipfsHash' => $ipfsHash
    ]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
  }
  exit;
}

// Render template
require_once __DIR__ . '/../templates/layout.php';
```

---

## 6. Styling Approach

### Tailwind + CSS Modules → Tailwind CDN

**BEFORE (React):**
```typescript
// vite.config.ts
import tailwindcss from 'tailwindcss'
import autoprefixer from 'autoprefixer'

// src/index.css
@tailwind base;
@tailwind components;
@tailwind utilities;

// Component usage
<div className={`px-6 py-3 rounded-lg ${activeTab === 'upload' ? 'bg-blue-600' : 'hover:bg-gray-100'}`}>
```

**AFTER (PHP):**
```php
<!-- templates/layout.php -->
<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class'
    }
  </script>
</head>

<!-- templates/upload.php -->
<div class="px-6 py-3 rounded-lg <?php echo $activeTab === 'upload' ? 'bg-blue-600' : 'hover:bg-gray-100'; ?>">
```

---

## 7. IPFS Integration

### Axios → Guzzle HTTP

**BEFORE (TypeScript - ipfs.ts):**
```typescript
import axios from 'axios';

const uploadToPinata = async (file: File): Promise<string> => {
  const formData = new FormData();
  formData.append('file', file);

  const response = await axios.post(
    'https://api.pinata.cloud/pinning/pinFileToIPFS',
    formData,
    {
      headers: {
        'pinata_api_key': PINATA_API_KEY,
        'pinata_secret_api_key': PINATA_SECRET_KEY
      }
    }
  );

  return response.data.IpfsHash;
};
```

**AFTER (PHP - src/Utils/IPFS.php):**
```php
use GuzzleHttp\Client;

private function uploadToPinata(string $filePath): string {
  $client = new Client();
  $fileHandle = fopen($filePath, 'r');

  $response = $client->post(
    'https://api.pinata.cloud/pinning/pinFileToIPFS',
    [
      'headers' => [
        'pinata_api_key' => $this->config['pinata']['api_key'],
        'pinata_secret_api_key' => $this->config['pinata']['secret_key']
      ],
      'multipart' => [
        [
          'name' => 'file',
          'contents' => $fileHandle
        ]
      ]
    ]
  );

  $body = json_decode($response->getBody()->getContents(), true);
  return $body['IpfsHash'];
}
```

---

## 8. Error Handling

### Try-Catch Patterns

**BEFORE (TypeScript):**
```typescript
try {
  await connectWallet();
  toast.success('Connected!');
} catch (error) {
  if (error.code === -32002) {
    toast.error('Pending request');
  } else {
    toast.error('Connection failed');
  }
  console.error(error);
}
```

**AFTER (PHP):**
```php
try {
  $result = Web3::verifyWalletSignature($address, $signature, $message);
  if ($result['success']) {
    // Success
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
```

---

## 9. Form Handling

### React Form State → PHP Forms + JavaScript

**BEFORE (React):**
```typescript
const [address, setAddress] = useState('');
const [isSharing, setIsSharing] = useState(false);

const handleShare = async () => {
  setIsSharing(true);
  try {
    await shareFile(signer, address, fileIndex);
  } finally {
    setIsSharing(false);
  }
};

return (
  <input 
    value={address} 
    onChange={(e) => setAddress(e.target.value)}
  />
);
```

**AFTER (PHP):**
```php
<!-- Form in template -->
<input type="text" id="recipientAddress" placeholder="0x..." />
<button id="shareBtn" type="button">Share</button>

<!-- JavaScript handling -->
<script>
document.getElementById('shareBtn').addEventListener('click', async () => {
  const address = document.getElementById('recipientAddress').value;
  const formData = new FormData();
  formData.append('action', 'share');
  formData.append('recipient', address);
  
  const response = await fetch('/', { method: 'POST', body: formData });
  const result = await response.json();
  
  if (result.success) {
    // Handle success
  }
});
</script>
```

---

## 10. Configuration Management

### Environment Variables

**BEFORE (TypeScript - .env.local):**
```
VITE_IPFS_PROVIDER=pinata
VITE_PINATA_API_KEY=...
VITE_PINATA_SECRET_KEY=...
```

**AFTER (PHP - .env):**
```env
IPFS_PROVIDER=pinata
PINATA_API_KEY=...
PINATA_SECRET_KEY=...
```

**Access patterns:**
```php
// In PHP
$provider = getenv('IPFS_PROVIDER');

// In JavaScript
// Configuration comes via PHP template
const config = window.appConfig || {};
```

---

## 11. Session Management

### Memory State → PHP Sessions

**BEFORE (React):**
```typescript
const [isConnected, setIsConnected] = useState(false);
const [userData, setUserData] = useState(null);

// Cleared on page refresh
```

**AFTER (PHP):**
```php
<?php
session_start();

// Persists across requests
$_SESSION['wallet_address'] = $address;
$_SESSION['user_data'] = $data;
$_SESSION['connected_at'] = time();

// Access on next page load
$address = $_SESSION['wallet_address'] ?? null;
```

---

## 12. Async Operations

### Promises → PHP async + JavaScript Promises

**BEFORE (TypeScript):**
```typescript
const loadFiles = async () => {
  setIsLoading(true);
  try {
    const files = await contract.getUserFiles(address);
    setUserFiles(files);
  } catch (error) {
    setError(error.message);
  } finally {
    setIsLoading(false);
  }
};
```

**AFTER (PHP - Java)**
```php
// Server-side (PHP)
public function getUserFiles(string $address): array {
  // Synchronous operation
  $contract = new Contract($this->config);
  return $contract->getUserFiles($address);
}

// Client-side (JavaScript)
const loadFiles = async () => {
  document.body.classList.add('loading');
  try {
    const response = await fetch('/?action=get_user_files', {
      method: 'POST',
      body: JSON.stringify({ address })
    });
    const data = await response.json();
    // Display files
  } catch (error) {
    console.error(error);
  } finally {
    document.body.classList.remove('loading');
  }
};
```

---

## 13. Type Safety

### TypeScript Types → PHP Type Hints

**BEFORE (TypeScript):**
```typescript
interface UserFile {
  ipfsHash: string;
  fileName: string;
  owner: string;
  fileSize: number;
  timestamp: number;
  isPublic: boolean;
}

function processFile(file: UserFile): void { }
```

**AFTER (PHP):**
```php
// PHP 8 uses type hints
function uploadFileToContract(
  string $ipfsHash, 
  string $fileName, 
  string $fileType,
  int $fileSize,
  string $userAddress
): array {
  // Implementation
}

// Array-based "types" in config
$file = [
  'ipfsHash' => $ipfsHash,
  'fileName' => $fileName,
  'owner' => $userAddress,
  'fileSize' => $fileSize,
  'timestamp' => time(),
  'isPublic' => true
];
```

---

## 14. Build & Deployment

### Vite → PHP dev server

**BEFORE (TypeScript):**
```bash
npm install
npm run dev      # Starts Vite dev server
npm run build    # Builds production bundle
npm run preview  # Previews production build
```

**AFTER (PHP):**
```bash
composer install                              # Install PHP dependencies
php -S localhost:8000 -t public              # Development server
# No build step needed!

# For production: Configure Apache/Nginx
# Deploy to web server with PHP enabled
```

---

## 15. Development Comparison

| Task | TypeScript | PHP |
|------|-----------|-----|
| **Setup** | `npm install` | `composer install` |
| **Dev Server** | `npm run dev` | `php -S localhost:8000` |
| **File Changes** | Auto-reload | F5 refresh |
| **Type Errors** | Build time | Runtime |
| **Testing** | Jest/Vitest | PHPUnit |
| **Debugging** | DevTools | Browser + error logs |
| **Deployment** | Build → Deploy artifacts | Deploy source directly |

---

## Key Takeaways

1. **State**: Memory hooks → Sessions
2. **Views**: JSX → PHP templates
3. **Styling**: Compiled → CDN-based
4. **HTTP**: Fetch → Guzzle
5. **Async**: Promises → PHP + JavaScript
6. **Validation**: TypeScript → Runtime checks
7. **Deployment**: Build artifact → Source code
8. **Sessions**: Lost on refresh → Persisted

---

## Gotchas & Solutions

### Gotcha 1: State Lost on Refresh
```php
// Solution: Use PHP sessions
$_SESSION['wallet_address'] = $address;
```

### Gotcha 2: No Hot Module Reload
```bash
# Solution: Just refresh your browser (F5)
```

### Gotcha 3: Type Safety
```php
// Solution: Add runtime checks
if (!is_array($data)) throw new Exception('Invalid data');
```

### Gotcha 4: Client Signature Verification
```javascript
// JavaScript must send signed data to PHP for verification
const signature = await window.ethereum.request({...});
fetch('/?action=verify', { body: { signature } });
```

---

## Resources

- [PHP Manual](https://www.php.net/manual/)
- [Guzzle HTTP Documentation](https://docs.guzzlephp.org/)
- [MetaMask Documentation](https://docs.metamask.io/)
- [IPFS Documentation](https://docs.ipfs.io/)
- [Tailwind CSS](https://tailwindcss.com/)

---

**Happy migrating! 🚀**
