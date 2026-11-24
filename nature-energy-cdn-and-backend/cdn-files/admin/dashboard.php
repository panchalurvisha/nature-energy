<?php
require_once __DIR__ . '/../lib/Auth.php';
Auth::checkAdmin();

$config = require __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDN </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .hover-scale { transition: transform 0.2s; }
        .hover-scale:hover { transform: scale(1.02); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-blue-600  text-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-cloud text-2xl"></i>
                    <h1 class="text-2xl font-bold">CDN Console</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- <span class="text-sm opacity-90">File Storage Manager</span> -->
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Images</p>
                        <p class="text-2xl font-bold text-gray-800" id="imageCount">-</p>
                        <p class="text-xs text-gray-400 mt-1" id="imageSize">0 Bytes</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-image text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Videos</p>
                        <p class="text-2xl font-bold text-gray-800" id="videoCount">-</p>
                        <p class="text-xs text-gray-400 mt-1" id="videoSize">0 Bytes</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-video text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6 hover-scale">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Storage</p>
                        <p class="text-2xl font-bold text-gray-800" id="storageUsed">-</p>
                        <p class="text-xs text-gray-400 mt-1" id="storageBreakdown">Loading...</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-hdd text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button onclick="showTab('upload')" class="tab-btn py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600" data-tab="upload">
                        <i class="fas fa-upload mr-2"></i>Upload Files
                    </button>
                    <button onclick="showTab('images')" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="images">
                        <i class="fas fa-images mr-2"></i>Images
                    </button>
                    <button onclick="showTab('videos')" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="videos">
                        <i class="fas fa-film mr-2"></i>Videos
                    </button>
                    <button onclick="showTab('api')" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="api">
                        <i class="fas fa-code mr-2"></i>API Docs
                    </button>
                </nav>
            </div>

            <!-- Upload Tab -->
            <div id="upload" class="tab-content active p-6">
                <div class="max-w-2xl mx-auto">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-cloud-upload-alt mr-3 text-blue-600"></i>
                        Upload New File
                    </h2>
                    
                    <form id="uploadForm" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">File Type</label>
                            <div class="flex space-x-4">
                                <button type="button" id="imageTypeBtn" class="file-type-btn flex-1 flex items-center justify-center space-x-2 px-4 py-2 border border-blue-500 rounded-lg text-blue-600 bg-blue-50 font-medium hover:bg-blue-100 transition-colors" data-type="image">
                                    <i class="fas fa-image"></i>
                                    <span>Image</span>
                                </button>
                                <button type="button" id="videoTypeBtn" class="file-type-btn flex-1 flex items-center justify-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors" data-type="video">
                                    <i class="fas fa-video"></i>
                                    <span>Video</span>
                                </button>
                                <input type="hidden" id="fileType" name="type" value="image">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Choose File</label>
                            <div class="relative">
                                <input type="file" id="fileInput" name="file" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Supported: Images (JPG, PNG, WebP) | Videos (MP4, WebM) | Max size: 200MB</p>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition-all duration-200 flex items-center justify-center space-x-2">
                            <i class="fas fa-upload"></i>
                            <span>Upload File</span>
                        </button>
                    </form>
                    
                    <div id="uploadResult" class="mt-6"></div>
                </div>
            </div>

            <!-- Images Tab -->
            <div id="images" class="tab-content p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-images mr-3 text-blue-600"></i>
                    Image Gallery
                </h2>
                <div id="imagesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>Loading images...</p>
                    </div>
                </div>
            </div>

            <!-- Videos Tab -->
            <div id="videos" class="tab-content p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-film mr-3 text-purple-600"></i>
                    Video Library
                </h2>
                <div id="videosList" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-full text-center py-12 text-gray-500">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>Loading videos...</p>
                    </div>
                </div>
            </div>

            <!-- API Documentation Tab -->
            <div id="api" class="tab-content p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-code mr-3 text-green-600"></i>
                    API Documentation
                </h2>
                
                <!-- Authentication -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4 flex items-center">
                        <i class="fas fa-key mr-2"></i>
                        Authentication
                    </h3>
                    <div class="bg-white rounded-lg p-4 border border-blue-100">
                        <p class="text-sm text-gray-700 mb-2">All API requests must include this header:</p>
                        <code class="block bg-gray-800 text-green-400 p-3 rounded text-sm font-mono">
                            Authorization: Bearer <?= $config['auth_token'] ?>
                        </code>
                        <button onclick="copyToClipboard('Bearer <?= $config['auth_token'] ?>')" class="mt-3 bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-copy mr-2"></i>Copy Token
                        </button>
                    </div>
                </div>

                <!-- API Endpoints -->
                <div class="space-y-6">
                    <!-- Upload Endpoint -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium mr-3">POST</span>
                                    Upload File
                                </h3>
                                <code class="text-sm bg-gray-100 px-3 py-1 rounded">/api/upload.php</code>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Request Body (multipart/form-data):</h4>
                                    <div class="bg-gray-50 rounded p-3 text-sm">
                                        <div class="mb-2"><strong>file</strong>: File to upload</div>
                                        <div><strong>type</strong>: "image" or "video"</div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Response:</h4>
                                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-sm overflow-x-auto"><code>{
  "success": true,
  "url": "http://localhost/files-storage/uploads/images/filename.jpg"
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List Files Endpoint -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium mr-3">GET</span>
                                    List Files
                                </h3>
                                <code class="text-sm bg-gray-100 px-3 py-1 rounded">/api/list.php</code>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Query Parameters:</h4>
                                    <div class="bg-gray-50 rounded p-3 text-sm">
                                        <div><strong>type</strong>: "image" or "video" (required)</div>
                                    </div>
                                </div>
                                
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Response:</h4>
                                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-sm overflow-x-auto"><code>{
  "files": [
    {
      "name": "filename.jpg",
      "url": "http://localhost/files-storage/uploads/images/filename.jpg",
      "size": 1024000,
      "modified": 1641234567
    }
  ]
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Auth Check Endpoint -->
                    <div class="bg-white rounded-lg shadow-md border border-gray-200">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium mr-3">GET</span>
                                    Check Authentication
                                </h3>
                                <code class="text-sm bg-gray-100 px-3 py-1 rounded">/api/auth.php</code>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-700 mb-2">Response:</h4>
                                    <pre class="bg-gray-800 text-green-400 p-3 rounded text-sm overflow-x-auto"><code>{
  "success": true,
  "message": "Authenticated"
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Code Examples -->
                <div class="mt-8 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-laptop-code mr-2"></i>
                        Code Examples
                    </h3>
                    
                    <!-- JavaScript Example -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">JavaScript (Next.js/React):</h4>
                        <pre class="bg-gray-800 text-green-400 p-4 rounded text-sm overflow-x-auto"><code>// Upload File
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('type', 'image');

fetch('<?= $config['base_url'] ?>/api/upload.php', {
    method: 'POST',
    headers: {
        'Authorization': 'Bearer <?= $config['auth_token'] ?>'
    },
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('File URL:', data.url);
    }
});

// List Files
fetch('<?= $config['base_url'] ?>/api/list.php?type=image', {
    headers: {
        'Authorization': 'Bearer <?= $config['auth_token'] ?>'
    }
})
.then(response => response.json())
.then(data => {
    console.log('Files:', data.files);
});</code></pre>
                    </div>

                    <!-- cURL Example -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-700 mb-2">cURL:</h4>
                        <pre class="bg-gray-800 text-green-400 p-4 rounded text-sm overflow-x-auto"><code># Upload File
curl -X POST "<?= $config['base_url'] ?>/api/upload.php" \
  -H "Authorization: Bearer <?= $config['auth_token'] ?>" \
  -F "file=@/path/to/your/image.jpg" \
  -F "type=image"

# List Files
curl -X GET "<?= $config['base_url'] ?>/api/list.php?type=image" \
  -H "Authorization: Bearer <?= $config['auth_token'] ?>"</code></pre>
                    </div>

                    <!-- PHP Example -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">PHP:</h4>
                        <pre class="bg-gray-800 text-green-400 p-4 rounded text-sm overflow-x-auto"><code>// Upload File
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, '<?= $config['base_url'] ?>/api/upload.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'file' => new CURLFile('/path/to/image.jpg'),
    'type' => 'image'
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer <?= $config['auth_token'] ?>'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);

if ($data['success']) {
    echo 'File URL: ' . $data['url'];
}</code></pre>
                    </div>
                </div>

                <!-- Error Handling -->
                <div class="mt-6 bg-red-50 border border-red-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-red-800 mb-4 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Error Responses
                    </h3>
                    <div class="space-y-3">
                        <div class="bg-white rounded p-3 border border-red-100">
                            <strong>401 Unauthorized:</strong>
                            <pre class="bg-gray-800 text-red-400 p-2 rounded text-sm mt-2"><code>{"error": "Unauthorized"}</code></pre>
                        </div>
                        <div class="bg-white rounded p-3 border border-red-100">
                            <strong>400 Bad Request:</strong>
                            <pre class="bg-gray-800 text-red-400 p-2 rounded text-sm mt-2"><code>{"error": "File missing"}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            
            document.querySelector(`[data-tab="${tabName}"]`).classList.remove('border-transparent', 'text-gray-500');
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('border-blue-500', 'text-blue-600');
            
            // Update tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active', 'fade-in');
            
            // Load data if needed
            if (tabName === 'images') loadFiles('image');
            if (tabName === 'videos') loadFiles('video');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function loadFiles(type) {
            const listId = type + 'sList';
            const container = document.getElementById(listId);
            
            container.innerHTML = `
                <div class="col-span-full text-center py-12 text-gray-500">
                    <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                    <p>Loading ${type}s...</p>
                </div>
            `;
            
            fetch('<?= $config['base_url'] ?>/api/list.php?type=' + type, {
                headers: {
                    'Authorization': 'Bearer <?= $config['auth_token'] ?>'
                }
            })
            .then(response => response.json())
            .then(data => {
                let html = '';
                if (data.files.length === 0) {
                    html = `
                        <div class="col-span-full text-center py-12 text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-4"></i>
                            <p>No ${type}s found.</p>
                        </div>
                    `;
                } else {
                    data.files.forEach(file => {
                        const isImage = type === 'image';
                        html += `
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover-scale">
                                ${isImage ? 
                                    `<img src="${file.url}" alt="${file.name}" class="w-full h-48 object-cover">` :
                                    `<div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-video text-4xl text-gray-400"></i>
                                    </div>`
                                }
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-800 truncate mb-2">${file.name}</h3>
                                    <p class="text-sm text-gray-500 mb-3">Size: ${formatFileSize(file.size)}</p>
                                    <div class="flex space-x-2">
                                        <a href="${file.url}" target="_blank" class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded text-sm hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-external-link-alt mr-1"></i> View
                                        </a>
                                        <button onclick="copyToClipboard('${file.url}')" class="flex-1 bg-gray-600 text-white py-2 px-3 rounded text-sm hover:bg-gray-700 transition-colors">
                                            <i class="fas fa-copy mr-1"></i> Copy URL
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                container.innerHTML = html;
                updateStats();
            })
            .catch(error => {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12 text-red-500">
                        <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                        <p>Error loading ${type}s.</p>
                    </div>
                `;
            });
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg z-50 fade-in';
                toast.innerHTML = '<i class="fas fa-check mr-2"></i>URL copied to clipboard!';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
        }

        function updateStats() {
            const apiOptions = {
                headers: { 'Authorization': 'Bearer <?= $config['auth_token'] ?>' }
            };

            Promise.all([
                fetch('<?= $config['base_url'] ?>/api/list.php?type=image', apiOptions).then(r => r.json()),
                fetch('<?= $config['base_url'] ?>/api/list.php?type=video', apiOptions).then(r => r.json())
            ])
            .then(([imageData, videoData]) => {
                const imageFiles = imageData.files || [];
                const videoFiles = videoData.files || [];

                const imageCount = imageFiles.length;
                const videoCount = videoFiles.length;

                const imageSize = imageFiles.reduce((sum, f) => sum + f.size, 0);
                const videoSize = videoFiles.reduce((sum, f) => sum + f.size, 0);

                document.getElementById('imageCount').textContent = imageCount;
                document.getElementById('videoCount').textContent = videoCount;
                document.getElementById('imageSize').textContent = formatFileSize(imageSize);
                document.getElementById('videoSize').textContent = formatFileSize(videoSize);
                
                document.getElementById('storageUsed').textContent = formatFileSize(imageSize + videoSize);
                document.getElementById('storageBreakdown').textContent = `Img: ${formatFileSize(imageSize)} | Vid: ${formatFileSize(videoSize)}`;
            })
            .catch(err => console.error('Error updating stats:', err));
        }


        // File Type Selection (Badges)
        document.querySelectorAll('.file-type-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.file-type-btn').forEach(btn => {
                    btn.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-600');
                    btn.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                });
                this.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                this.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-600');
                document.getElementById('fileType').value = this.dataset.type;
            });
        });

        // Initialize default selected file type
        document.getElementById('imageTypeBtn').click();

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('file', document.getElementById('fileInput').files[0]);
            formData.append('type', document.getElementById('fileType').value);
            
            const resultDiv = document.getElementById('uploadResult');
            resultDiv.innerHTML = `
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-spinner fa-spin mr-3"></i>
                    <span>Uploading file...</span>
                </div>
            `;
            
            fetch('<?= $config['base_url'] ?>/api/upload.php', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer <?= $config['auth_token'] ?>'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-check-circle mr-3"></i>
                                <span class="font-medium">Upload successful!</span>
                            </div>
                            <div class="mt-2">
                                <p class="text-sm mb-2">File URL:</p>
                                <div class="flex items-center space-x-2">
                                    <input type="text" value="${data.url}" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm bg-gray-50">
                                    <button onclick="copyToClipboard('${data.url}')" class="bg-blue-600 text-white px-3 py-2 rounded text-sm hover:bg-blue-700">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById('fileInput').value = '';
                    updateStats();
                } else {
                    resultDiv.innerHTML = `
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                            <i class="fas fa-exclamation-triangle mr-3"></i>
                            <span>Error: ${data.error || 'Unknown error'}</span>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        <span>Upload failed. Please try again.</span>
                    </div>
                `;
            });
        });

        // Initialize stats on page load
        updateStats();
    </script>
</body>
</html>
