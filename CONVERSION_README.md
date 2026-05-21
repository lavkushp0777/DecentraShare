# CONVERSION COMPLETE - TypeScript to PHP

## ✅ Conversion Status

This repository has been **completely converted from TypeScript/React to PHP**.

> **Note**: The original TypeScript/React code still exists but is no longer the primary implementation. The PHP version is now the main application.

## 📚 Documentation

- **[QUICKSTART.md](./QUICKSTART.md)** - Get started in 5 minutes ⭐ **START HERE**
- **[PHP_README.md](./PHP_README.md)** - Complete PHP documentation
- **[Conversion Guide](#conversion-guide)** - Technical details below

## 🚀 Quick Start

```bash
# 1. Install dependencies
composer install

# 2. Setup environment
cp .env.example .env

# 3. Start server
php -S localhost:8000 -t public

# 4. Open browser
# http://localhost:8000
```

## 📁 Project Structure

```
├── public/              # Web-accessible entry point
│   ├── index.php       # Main application  
│   ├── js/app.js       # Client-side logic
│   └── css/style.css   # Styles
├── src/                # PHP source code
│   └── Utils/          # Utility classes
│       ├── Web3.php
│       ├── IPFS.php
│       └── Contract.php
├── templates/          # PHP templates
│   ├── layout.php
│   ├── upload.php
│   ├── my-files.php
│   └── shared-files.php
├── config/            # Configuration
├── composer.json      # PHP dependencies
└── .env              # Environment variables
```

## 🔄 Conversion Guide

### What Changed

| Aspect | Before (TypeScript) | After (PHP) |
|--------|-------------------|-----------|
| **Runtime** | Node.js + Browser | PHP + Browser |
| **Frontend** | React Components | PHP Templates |
| **Styling** | Tailwind + CSS-in-JS | Tailwind CDN |
| **Build** | Vite | None (PHP Dev Server) |
| **State** | React Hooks | PHP Sessions |
| **HTTP Requests** | Fetch API | Guzzle HTTP |
| **Type Safety** | TypeScript | PHP Type Hints |

### Files Mapped

| TypeScript | PHP |
|-----------|-----|
| `src/App.tsx` | `public/index.php` + `templates/layout.php` |
| `src/components/FileUpload.tsx` | `templates/upload.php` |
| `src/components/FileViewer.tsx` | `templates/my-files.php` + `templates/shared-files.php` |
| `src/utils/web3.ts` | `src/Utils/Web3.php` |
| `src/utils/ipfs.ts` | `src/Utils/IPFS.php` |
| `src/utils/contract.ts` | `src/Utils/Contract.php` |
| `package.json` | `composer.json` |
| `vite.config.ts` | `.htaccess` + `config/contracts.php` |

## 🛠️ Technology Stack

### Backend
- **PHP 8.0+** - Server-side logic
- **Composer** - Dependency management
- **Guzzle** - HTTP client for IPFS/Web3 APIs
- **Symfony Dotenv** - Environment configuration

### Frontend
- **HTML/PHP** - Template rendering
- **Tailwind CSS** - Styling (CDN)
- **Vanilla JavaScript** - Client-side logic
- **MetaMask** - Web3 wallet

### Services
- **IPFS** - File storage (Pinata/Web3.Storage/Custom)
- **Ethereum** - Smart contracts (blockchain)
- **Web3** - Wallet connection

## 📋 Features

✅ Wallet connection via MetaMask  
✅ File upload to IPFS  
✅ File sharing between users  
✅ Public/Private file management  
✅ Dark mode theme  
✅ Responsive design  
✅ Session management  
✅ Environment configuration  

## 🔐 Security Features

- MetaMask signature verification
- PHP session management
- Environment variable protection
- File size validation
- CORS headers
- Input validation
- IPFS hash validation

## 🚀 Deployment

### Development
```bash
php -S localhost:8000 -t public
```

### Production (Apache)
- Point document root to `public/`
- Enable mod_rewrite
- Use `.htaccess` for routing

### Production (Nginx)
```nginx
root /path/to/decentrashare/public;
location / {
    try_files $uri /index.php?$query_string;
}
```

## ⚙️ Configuration

### Environment Variables (.env)
```env
# Application
APP_ENV=production
APP_DEBUG=false

# IPFS Provider
IPFS_PROVIDER=pinata
PINATA_API_KEY=your_key
PINATA_SECRET_KEY=your_secret

# Web3
CONTRACT_ADDRESS=0x...
WEB3_RPC_URL=https://...
```

### Supported IPFS Providers
1. **Pinata** (Recommended for beginners)
2. **Web3.Storage**
3. **Custom IPFS Node**

## 📖 API Reference

### POST /?action=verify_wallet_signature
Verify MetaMask signature
```json
{
  "address": "0x...",
  "signature": "0x...",
  "message": "..."
}
```

### POST /?action=upload
Upload file to IPFS
```
Content-Type: multipart/form-data
file: <binary file data>
```

### POST /?action=disconnect
Disconnect wallet session

## 🧪 Development

### Code Structure
```php
// PHP Classes are in src/Utils/
use DecentraShare\Utils\Web3;
use DecentraShare\Utils\IPFS;
use DecentraShare\Utils\Contract;

// Bootstrap application
$app = Application::getInstance(__DIR__ . '/../src');
$ipfs = $app->getIPFS();
```

### Adding Features
1. Create utility class in `src/Utils/`
2. Create template in `templates/`
3. Handle request in `public/index.php`
4. Add JavaScript handler in `public/js/app.js`

## 🐛 Troubleshooting

### Port Already in Use
```bash
php -S localhost:8001 -t public
```

### IPFS Configuration Error
- Verify `.env` file exists
- Check IPFS provider credentials
- Restart development server

### Wallet Connection Issues
- Install/update MetaMask
- Clear MetaMask cache
- Try different network
- Enable cookies in browser

### File Upload Failures
- Check file size (max 100MB)
- Verify IPFS API credentials
- Check browser console for errors
- Verify internet connection

## 🔗 Original Project

This is a conversion of the original TypeScript/React DecentraShare project. The original files are still in the repository:
- `src/App.tsx` - Original React app
- `src/components/` - Original React components
- `src/utils/` - Original TypeScript utilities
- `package.json` - Original npm dependencies
- `vite.config.ts` - Original Vite config

## 📝 License

MIT License

## 🤝 Contributing

Contributions welcome! This is a learning project demonstrating:
- TypeScript to PHP conversion
- React to template conversion
- Frontend and backend architecture
- Web3 integration
- IPFS integration

## ❓ FAQ

**Q: Why convert to PHP?**
A: To demonstrate cross-language conversion and provide a server-side rendered alternative.

**Q: Is this for production?**
A: Not yet. Would need database integration, better error handling, and comprehensive testing.

**Q: Can I use this commercially?**
A: Yes, under MIT license. See LICENSE file.

**Q: How do I go back to TypeScript?**
A: Original files exist. Run `npm install` and build with `npm run build`.

---

## 📚 Next Steps

1. ✅ Read [QUICKSTART.md](./QUICKSTART.md)
2. ✅ Setup development environment
3. ✅ Connect MetaMask wallet
4. ✅ Test file upload
5. → Deploy to production server
6. → Add database layer
7. → Implement user authentication

**Happy coding! 🚀**

For detailed information, see [PHP_README.md](./PHP_README.md)
