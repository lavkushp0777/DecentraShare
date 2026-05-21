# DecentraShare PHP - Quick Start Guide

## What Changed?

This is a complete conversion of the TypeScript/React application to PHP. The functionality remains the same, but the technology stack is different:

- **Frontend**: Pure PHP templates + Tailwind CSS (CDN) + Vanilla JavaScript
- **Backend**: PHP 8.0+ with Guzzle HTTP client
- **Build Tool**: No build step needed (PHP development server ready)
- **State Management**: PHP Sessions instead of React hooks
- **Database**: Not implemented (future enhancement)

## Prerequisites

Before starting, make sure you have:
- PHP 8.0 or higher
- Composer package manager
- MetaMask browser extension
- An IPFS provider (Pinata, Web3.Storage, or custom node)

Check PHP version:
```bash
php --version
```

Check Composer:
```bash
composer --version
```

## 5-Minute Setup

### Step 1: Install Dependencies
```bash
composer install
```

### Step 2: Create Environment File
```bash
cp .env.example .env
```

### Step 3: Configure IPFS (Choose One)

**Option A: Pinata (Easiest)**
```bash
# Edit .env
IPFS_PROVIDER=pinata
PINATA_API_KEY=your_key_here
PINATA_SECRET_KEY=your_secret_here
```
Sign up at https://pinata.cloud

**Option B: Web3.Storage**
```bash
IPFS_PROVIDER=web3storage
WEB3_STORAGE_TOKEN=your_token_here
```
Sign up at https://web3.storage

**Option C: Local IPFS Node**
```bash
IPFS_PROVIDER=custom
IPFS_API_URL=http://localhost:5001
IPFS_GATEWAY_URL=http://localhost:8080/ipfs/
```

### Step 4: Start Server
```bash
php -S localhost:8000 -t public
```

### Step 5: Open in Browser
```
http://localhost:8000
```

### Step 6: Connect Wallet
1. Click "Connect Wallet"
2. Approve in MetaMask
3. Sign the verification message
4. You're ready to share files!

## Project Structure Overview

```
public/
  ├── index.php          ← Main entry point
  ├── js/app.js          ← Client-side logic
  └── css/style.css      ← Custom styles

src/
  └── Utils/
      ├── Web3.php       ← Wallet handling
      ├── IPFS.php       ← File uploads
      └── Contract.php   ← Blockchain interaction

templates/
  ├── layout.php         ← Main page layout
  ├── upload.php         ← Upload form
  ├── my-files.php       ← Your files
  └── shared-files.php   ← Shared with you

config/
  └── contracts.php      ← Smart contract ABI
```

## Common Tasks

### Restart Development Server
```bash
# Stop current server with Ctrl+C, then:
php -S localhost:8000 -t public
```

### Update Dependencies
```bash
composer update
```

### Check PHP Syntax
```bash
php -l public/index.php
php -l src/Utils/Web3.php
```

### View Error Logs
Errors are shown in the browser and PHP error logs

## Features Quick Tour

### 1. Upload Files
- Select a file (max 100MB)
- It gets uploaded to IPFS
- File hash is displayed
- In production: would be stored on blockchain

### 2. View My Files
- See all your uploaded files
- Download from IPFS gateway
- Share with other users
- Toggle between public/private

### 3. Manage Shared Files
- View files shared with you
- Download shared files
- See who shared each file

## Troubleshooting

### "IPFS Provider not configured"
- Make sure `.env` file exists
- Check IPFS_PROVIDER is set to 'pinata', 'web3storage', or 'custom'
- Verify API keys are correct

### "Cannot connect wallet"
- Make sure MetaMask is installed
- Check you're on correct Ethereum network
- Try clearing MetaMask cache
- Reload the page

### "File upload failed"
- Check file size (max 100MB)
- Verify IPFS credentials are valid
- Check browser console for errors

### "Port 8000 already in use"
```bash
# Use different port
php -S localhost:8001 -t public
```

## Upgrading to Production

### Web Server Setup (Apache)

1. Configure document root to `public/`
2. Enable mod_rewrite
3. Update `.env` with production values
4. Set `APP_ENV=production` in `.env`

### Web Server Setup (Nginx)

```nginx
server {
    listen 80;
    server_name decentrashare.example.com;
    root /path/to/decentrashare/public;

    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

### Environment Setup

```bash
# Production .env
APP_ENV=production
APP_DEBUG=false
SESSION_COOKIE_SECURE=true
SESSION_COOKIE_HTTPONLY=true

# Use real contract address and RPC
CONTRACT_ADDRESS=0x70dD105c6D5F4be9aa803618abfCbBC5Fa1B1B82
WEB3_RPC_URL=https://eth-mainnet.alchemyapi.io/v2/YOUR_KEY
```

## Development Tips

### Adding a New Page
1. Create template in `templates/new-page.php`
2. Add link in `templates/layout.php`
3. Handle routing in `public/index.php`

### Adding API Endpoint
1. Add `if ($action === 'my-action')` in `public/index.php`
2. Handle the request and return JSON
3. Call from JavaScript with `fetch()`

### Using PHP for Configuration
Everything is in PHP arrays in `config/contracts.php`

### Testing File Upload
```bash
# Using curl
curl -F "action=upload" -F "file=@myfile.txt" http://localhost:8000
```

## Next Steps

1. ✅ Setup and run locally
2. Deploy to web server
3. Configure custom domain
4. Set up SSL certificate
5. Implement database for storage
6. Add user accounts
7. Add more advanced sharing options

## Getting Help

- Check `.env` configuration 
- Review error messages in browser console
- Check PHP error logs
- Refer to `PHP_README.md` for detailed docs
- Original TypeScript project: Check GitHub

## Converting Back to TypeScript?

The conversion was mostly structural. Key differences that would need attention:
- State management (React hooks instead of PHP sessions)
- Build process (Vite compilation)
- Type safety (TypeScript vs PHP)
- Component reusability (React components vs templates)

## License

MIT License - same as original project

---

**Enjoy using DecentraShare! 🚀**

Questions? Create an issue on the repository.
