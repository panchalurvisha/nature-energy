# CDN v2 - Core PHP File Storage

A simple, secure CDN built with core PHP for serving images and videos with Next.js integration.

## 🏗️ Architecture

```
Next.js App
   |
   |--- AUTH_TOKEN (API)
   |
Core PHP CDN
   ├── Auth Middleware
   ├── Image Compression
   ├── Video Validation
   ├── Admin Login UI
   └── Public File Serving
```

## 📁 Folder Structure

```
cdn/
├── api/
│   ├── upload.php      # File upload API
│   ├── list.php        # File listing API
│   └── auth.php        # Auth check endpoint
│
├── admin/
│   ├── login.php       # Admin login page
│   ├── dashboard.php   # Admin dashboard
│   └── logout.php      # Logout script
│
├── uploads/
│   ├── images/         # Uploaded images
│   ├── videos/         # Uploaded videos
│   └── .htaccess       # Security rules
│
├── lib/
│   ├── ImageCompressor.php  # Image compression
│   └── Auth.php             # Authentication middleware
│
├── config/
│   └── config.php      # Configuration settings
│
├── .htaccess           # URL routing
└── README.md
```

## 🚀 Setup Instructions

### 1. Configure Settings

Edit `config/config.php`:

```php
return [
    'base_url' => 'https://your-cdn-domain.com',  // Change to your domain
    'auth_token' => 'CHANGE_THIS_TO_RANDOM_LONG_TOKEN',  // Generate secure token
    'admin' => [
        'username' => 'admin',  // Change username
        'password' => password_hash('your-secure-password', PASSWORD_BCRYPT)  // Change password
    ],
    // ... other settings
];
```

### 2. Set Permissions

```bash
# Make uploads directory writable
chmod 777 uploads/
chmod 777 uploads/images/
chmod 777 uploads/videos/
```

### 3. Web Server Configuration

#### Apache (.htaccess already included)

Ensure `mod_rewrite` is enabled and `AllowOverride All` is set.

#### Nginx

Add to your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ ^/uploads/ {
    # Security: Prevent PHP execution
    location ~ \.php$ {
        deny all;
    }
}
```

## 🔐 Security Features

- **Token-based API authentication** with Bearer tokens
- **Admin session management** with secure login
- **File type validation** (images: jpg, jpeg, png, webp; videos: mp4, webm)
- **File size limits** (200MB default)
- **Upload directory protection** (PHP execution disabled)
- **Automatic image compression** (60-80% size reduction)

## 📡 API Usage

### Upload File

```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('type', 'image'); // or 'video'

fetch('https://your-cdn-domain.com/api/upload.php', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer YOUR_AUTH_TOKEN'
    },
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('File URL:', data.url);
    }
});
```

### List Files

```javascript
fetch('https://your-cdn-domain.com/api/list.php?type=image', {
    headers: {
        'Authorization': 'Bearer YOUR_AUTH_TOKEN'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Files:', data.files);
});
```

### Check Auth

```javascript
fetch('https://your-cdn-domain.com/api/auth.php', {
    headers: {
        'Authorization': 'Bearer YOUR_AUTH_TOKEN'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Auth status:', data.success);
});
```

## 🖼️ Image Features

- **Automatic compression** on upload
- **Quality optimization** (70% JPEG, 6 PNG compression)
- **Format support**: JPEG, PNG, WebP
- **Size reduction**: 60-80% without visible quality loss

## 🎥 Video Features

- **Direct storage** (no re-encoding for MVP)
- **Format support**: MP4, WebM
- **Stream-ready** files
- **Future FFMPEG integration** ready

## 🔧 Admin Panel

Access at: `https://your-cdn-domain.com/admin/`

Default login:
- Username: `admin`
- Password: `admin@123`

**⚠️ Change default credentials immediately!**

Features:
- Upload files via web interface
- Browse uploaded images and videos
- View file URLs and sizes
- Secure logout

## 🌐 Next.js Integration

Create a CDN utility in your Next.js app:

```javascript
// utils/cdn.js
const CDN_CONFIG = {
    baseUrl: 'https://your-cdn-domain.com',
    authToken: 'YOUR_AUTH_TOKEN'
};

export const uploadToCdn = async (file, type = 'image') => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', type);

    const response = await fetch(`${CDN_CONFIG.baseUrl}/api/upload.php`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${CDN_CONFIG.authToken}`
        },
        body: formData
    });

    return response.json();
};

export const getCdnFiles = async (type = 'image') => {
    const response = await fetch(`${CDN_CONFIG.baseUrl}/api/list.php?type=${type}`, {
        headers: {
            'Authorization': `Bearer ${CDN_CONFIG.authToken}`
        }
    });

    return response.json();
};
```

## 📝 Example Next.js Component

```jsx
import { uploadToCdn, getCdnFiles } from '../utils/cdn';

export default function ImageUploader() {
    const handleUpload = async (e) => {
        const file = e.target.files[0];
        const result = await uploadToCdn(file, 'image');
        
        if (result.success) {
            console.log('Uploaded:', result.url);
        }
    };

    return (
        <div>
            <input type="file" onChange={handleUpload} accept="image/*" />
        </div>
    );
}
```

## 🔍 Monitoring & Maintenance

- Check upload directory sizes regularly
- Monitor API usage and authentication attempts
- Update auth tokens periodically
- Backup uploaded files
- Review error logs

## 🚨 Important Notes

1. **Change default credentials** before production
2. **Generate strong auth tokens** (use `openssl rand -base64 32`)
3. **Set proper file permissions** on uploads directory
4. **Configure HTTPS** for production
5. **Regular backups** of uploaded content
6. **Monitor disk space** usage

## 📈 Future Enhancements

- [ ] FFMPEG video transcoding
- [ ] Image resizing/thumbnails
- [ ] CDN caching headers
- [ ] File expiration/deletion
- [ ] Usage analytics
- [ ] Multi-user support
- [ ] Cloud storage integration

## 📞 Support

For issues and questions, check the error logs and ensure:
- PHP extensions: GD (for images), FileInfo
- Proper directory permissions
- Correct web server configuration
- Valid auth token usage
