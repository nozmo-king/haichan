<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Secret Upload - Haichan</title>
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff41;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #00ff41;
            border-radius: 8px;
            padding: 30px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #00ff41;
            font-size: 28px;
            text-shadow: 0 0 10px #00ff41;
        }
        
        .upload-zone {
            border: 2px dashed #00ff41;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-zone:hover {
            background: rgba(0, 255, 65, 0.1);
            border-color: #41ff00;
        }
        
        .upload-zone.dragover {
            background: rgba(0, 255, 65, 0.2);
            border-color: #41ff00;
        }
        
        .file-input {
            display: none;
        }
        
        .upload-text {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .upload-hint {
            color: #888;
            font-size: 14px;
        }
        
        .secret-key {
            width: 100%;
            padding: 12px;
            background: #111;
            border: 1px solid #00ff41;
            border-radius: 4px;
            color: #00ff41;
            font-family: 'Courier New', monospace;
            margin-bottom: 20px;
        }
        
        .upload-btn {
            width: 100%;
            padding: 15px;
            background: #00ff41;
            color: #000;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Courier New', monospace;
        }
        
        .upload-btn:hover {
            background: #41ff00;
            box-shadow: 0 0 20px #00ff41;
        }
        
        .upload-btn:disabled {
            background: #333;
            color: #666;
            cursor: not-allowed;
        }
        
        .file-list {
            margin-top: 20px;
        }
        
        .file-item {
            background: #111;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-name {
            color: #ccc;
        }
        
        .file-size {
            color: #888;
            font-size: 12px;
        }
        
        .file-remove {
            color: #ff4444;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
        }
        
        .results {
            margin-top: 30px;
            padding: 20px;
            background: #111;
            border-radius: 4px;
            display: none;
        }
        
        .result-item {
            padding: 8px;
            margin-bottom: 5px;
            border-radius: 3px;
        }
        
        .result-item.uploaded {
            background: rgba(0, 255, 65, 0.2);
            border: 1px solid #00ff41;
        }
        
        .result-item.duplicate {
            background: rgba(255, 165, 0, 0.2);
            border: 1px solid #ffa500;
            color: #ffa500;
        }
        
        .result-item.error {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid #ff4444;
            color: #ff4444;
        }
        
        .hash {
            font-size: 10px;
            color: #666;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 SECRET UPLOAD ZONE</h1>
        
        <form id="uploadForm">
            <input type="password" 
                   class="secret-key" 
                   name="secret_key" 
                   placeholder="Enter secret key..." 
                   required>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #00ff41; margin-bottom: 8px; font-size: 14px;">
                    📝 Content Description (helps AI understand usage)
                </label>
                <input type="text" 
                       class="context-input" 
                       id="contextInput"
                       name="context" 
                       placeholder="e.g., 'logo/header for main view', 'jukebox songs', 'board backgrounds'..." 
                       style="width: 100%; padding: 12px; background: #111; border: 1px solid #00ff41; border-radius: 4px; color: #00ff41; font-family: 'Courier New', monospace; margin-bottom: 10px;">
                <div style="font-size: 12px; color: #666; line-height: 1.4;">
                    💡 Examples:<br>
                    • "logo/header for main view" → AI will update site header<br>
                    • "jukebox songs" → AI will build music player<br>
                    • "board backgrounds" → AI will set as board themes
                </div>
            </div>
            
            <div class="upload-zone" id="uploadZone">
                <input type="file" 
                       class="file-input" 
                       id="fileInput" 
                       multiple 
                       accept="image/*,video/*,.webm,.mp4,.mov,.avi,.svg,.avif,.heic,.heif,.pdf,.txt,.md">
                <div class="upload-text">📁 Click to select files or drag & drop</div>
                <div class="upload-hint">Images, videos, documents - up to 50MB each</div>
            </div>
            
            <div class="file-list" id="fileList"></div>
            
            <div style="display: flex; gap: 10px;">
                <button type="submit" class="upload-btn" id="uploadBtn" disabled style="flex: 1;">
                    🚀 UPLOAD FILES
                </button>
                <button type="button" class="upload-btn" id="processBtn" style="flex: 1; background: #333; cursor: not-allowed; opacity: 0.6;" 
                        onclick="processUploadedContent()">
                    🤖 PROCESS CONTEXT
                </button>
            </div>
        </form>
        
        <div class="results" id="results"></div>
    </div>

    <script>
        console.log('Secret upload script loading...');
        
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadForm = document.getElementById('uploadForm');
        const results = document.getElementById('results');
        
        console.log('Elements found:', { uploadZone, fileInput, fileList, uploadBtn });
        
        let selectedFiles = [];
        
        // Click to select files
        uploadZone.addEventListener('click', (e) => {
            console.log('Upload zone clicked');
            e.preventDefault();
            fileInput.click();
        });
        
        // Drag and drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            handleFiles(Array.from(e.dataTransfer.files));
        });
        
        fileInput.addEventListener('change', (e) => {
            console.log('File input changed:', e.target.files);
            handleFiles(Array.from(e.target.files));
        });
        
        function handleFiles(files) {
            selectedFiles = [...selectedFiles, ...files];
            updateFileList();
            updateUploadButton();
        }
        
        function updateFileList() {
            fileList.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const item = document.createElement('div');
                item.className = 'file-item';
                item.innerHTML = `
                    <div>
                        <div class="file-name">${file.name}</div>
                        <div class="file-size">${formatFileSize(file.size)}</div>
                    </div>
                    <div class="file-remove" onclick="removeFile(${index})">✖</div>
                `;
                fileList.appendChild(item);
            });
        }
        
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateFileList();
            updateUploadButton();
        }
        
        function updateUploadButton() {
            uploadBtn.disabled = selectedFiles.length === 0;
        }
        
        function formatFileSize(bytes) {
            if (bytes >= 1073741824) {
                return (bytes / 1073741824).toFixed(2) + ' GB';
            } else if (bytes >= 1048576) {
                return (bytes / 1048576).toFixed(2) + ' MB';
            } else if (bytes >= 1024) {
                return (bytes / 1024).toFixed(2) + ' KB';
            }
            return bytes + ' bytes';
        }
        
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (selectedFiles.length === 0) {
                alert('Please select files to upload');
                return;
            }
            
            const secretKey = document.querySelector('input[name="secret_key"]').value;
            if (!secretKey) {
                alert('Please enter the secret key');
                return;
            }
            
            const context = document.getElementById('contextInput').value;
            
            uploadBtn.disabled = true;
            uploadBtn.textContent = '⏳ UPLOADING...';
            
            const formData = new FormData();
            selectedFiles.forEach(file => {
                formData.append('files[]', file);
            });
            formData.append('secret_key', secretKey);
            formData.append('context', context);
            
            try {
                const response = await fetch('/secret/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    lastUploadContext = context; // Store context for processing
                    showResults(result.files);
                    selectedFiles = [];
                    updateFileList();
                    fileInput.value = '';
                } else {
                    alert('Upload failed: ' + (result.message || 'Unknown error'));
                }
                
            } catch (error) {
                console.error('Upload error:', error);
                alert('Upload failed: ' + error.message);
            }
            
            uploadBtn.disabled = false;
            uploadBtn.textContent = '🚀 UPLOAD FILES';
            updateUploadButton();
        });
        
        let lastUploadContext = '';
        let uploadedHashes = [];
        
        function showResults(files) {
            results.innerHTML = '<h3>📊 UPLOAD RESULTS</h3>';
            
            uploadedHashes = [];
            
            files.forEach(file => {
                const item = document.createElement('div');
                item.className = `result-item ${file.status}`;
                
                let content = `<strong>${file.name}</strong> - `;
                
                if (file.status === 'uploaded') {
                    content += `✅ Successfully uploaded (${file.size || 'unknown size'})`;
                    if (file.hash) uploadedHashes.push(file.hash);
                } else if (file.status === 'duplicate') {
                    content += `⚠️ Duplicate file (already exists)`;
                    if (file.hash) uploadedHashes.push(file.hash);
                } else if (file.status === 'error') {
                    content += `❌ Error: ${file.error}`;
                }
                
                if (file.hash) {
                    content += `<div class="hash">Hash: ${file.hash}</div>`;
                }
                
                item.innerHTML = content;
                results.appendChild(item);
            });
            
            results.style.display = 'block';
            
            // Enable process button if we have context and files
            const processBtn = document.getElementById('processBtn');
            console.log('Button state check:', {
                uploadedHashes: uploadedHashes.length,
                context: lastUploadContext,
                contextLength: lastUploadContext.trim().length
            });
            
            if (uploadedHashes.length > 0 && lastUploadContext.trim().length > 0) {
                processBtn.disabled = false;
                processBtn.style.background = '#ffa500';
                processBtn.style.cursor = 'pointer';
                processBtn.style.opacity = '1';
                console.log('✅ Process button enabled');
            } else {
                processBtn.disabled = true;
                processBtn.style.background = '#333';
                processBtn.style.cursor = 'not-allowed';
                processBtn.style.opacity = '0.6';
                console.log('❌ Process button disabled - missing hashes or context');
            }
        }
        
        async function processUploadedContent() {
            if (uploadedHashes.length === 0 || !lastUploadContext.trim()) {
                alert('❌ No uploaded files or context to process.\n\n1. Upload files first\n2. Enter context description\n3. Try again');
                return;
            }
            
            const processBtn = document.getElementById('processBtn');
            const originalText = processBtn.textContent;
            processBtn.disabled = true;
            processBtn.textContent = '🤖 PROCESSING...';
            
            try {
                const response = await fetch('/secret/process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        context: lastUploadContext,
                        hashes: uploadedHashes,
                        secret_key: document.querySelector('input[name="secret_key"]').value
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Context processed successfully!\n\n' + (result.message || 'Files processed and applied.'));
                } else {
                    alert('❌ Processing failed: ' + (result.message || 'Unknown error'));
                }
                
            } catch (error) {
                console.error('Processing error:', error);
                alert('❌ Processing failed: ' + error.message);
            }
            
            processBtn.disabled = false;
            processBtn.textContent = originalText;
        }
    </script>
</body>
</html>