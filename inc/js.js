// 公共JS函数库

// Ajax请求函数
function ajax(url, data, callback, method = 'POST') {
    const xhr = new XMLHttpRequest();
    
    if (method === 'GET' && data) {
        const params = new URLSearchParams(data).toString();
        url += (url.indexOf('?') > -1 ? '&' : '?') + params;
        data = null;
    }
    
    xhr.open(method, url, true);
    
    if (method === 'POST' && !(data instanceof FormData)) {
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        if (typeof data === 'object') {
            data = new URLSearchParams(data).toString();
        }
    }
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    callback(res);
                } catch (e) {
                    toast('响应数据格式错误', 'error');
                }
            } else {
                toast('网络请求失败', 'error');
            }
        }
    };
    
    xhr.send(data);
}

// 提示信息
function toast(msg, type = 'success') {
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = msg;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 2000);
}

// 确认对话框
function confirm_dialog(msg, callback) {
    if (confirm(msg)) {
        callback();
    }
}

// 显示遮罩层
function show_modal(content, width = '90%') {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content" style="max-width:${width}">
            <div class="modal-close" onclick="close_modal()">×</div>
            <div class="modal-body">${content}</div>
        </div>
    `;
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
    
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

// 关闭遮罩层
function close_modal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(modal);
            document.body.style.overflow = '';
        }, 300);
    });
}

// 图片预览
function preview_image(src) {
    show_modal(`<img src="${src}" style="max-width:100%;max-height:80vh;display:block;margin:auto;">`, '95%');
}

// 视频播放
function play_video(src) {
    show_modal(`
        <video src="${src}" controls autoplay style="max-width:100%;max-height:80vh;display:block;margin:auto;background:#000;">
            您的浏览器不支持视频播放
        </video>
    `, '95%');
}

// 音频播放
function play_audio(src, title) {
    show_modal(`
        <div style="padding:20px;text-align:center;">
            <h3>${title}</h3>
            <audio src="${src}" controls autoplay style="width:100%;max-width:500px;margin-top:20px;">
                您的浏览器不支持音频播放
            </audio>
        </div>
    `, '500px');
}

// 格式化时间
function format_time(time) {
    const date = new Date(time);
    const now = new Date();
    const diff = (now - date) / 1000;
    
    if (diff < 60) {
        return '刚刚';
    } else if (diff < 3600) {
        return Math.floor(diff / 60) + '分钟前';
    } else if (diff < 86400) {
        return Math.floor(diff / 3600) + '小时前';
    } else if (diff < 604800) {
        return Math.floor(diff / 86400) + '天前';
    } else {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hour = String(date.getHours()).padStart(2, '0');
        const minute = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day} ${hour}:${minute}`;
    }
}

// 格式化文件大小
function format_size(size) {
    const units = ['B', 'KB', 'MB', 'GB'];
    let index = 0;
    while (size >= 1024 && index < units.length - 1) {
        size /= 1024;
        index++;
    }
    return size.toFixed(2) + units[index];
}

// 压缩图片
function compress_image(file, callback, maxWidth = 1000) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            let width = img.width;
            let height = img.height;
            
            if (width > maxWidth) {
                height = Math.round(height * maxWidth / width);
                width = maxWidth;
            }
            
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            canvas.toBlob(function(blob) {
                callback(blob);
            }, file.type || 'image/jpeg', 0.85);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// 分片上传
function chunk_upload(file, url, callback, progressCallback) {
    const chunkSize = 1024 * 1024; // 1MB
    const chunks = Math.ceil(file.size / chunkSize);
    const fileId = Date.now() + '_' + Math.random().toString(36).substr(2);
    let currentChunk = 0;
    
    function uploadChunk() {
        const start = currentChunk * chunkSize;
        const end = Math.min(start + chunkSize, file.size);
        const chunk = file.slice(start, end);
        
        const formData = new FormData();
        formData.append('file', chunk);
        formData.append('chunk', currentChunk);
        formData.append('chunks', chunks);
        formData.append('fileId', fileId);
        formData.append('filename', file.name);
        formData.append('filesize', file.size);
        
        ajax(url, formData, function(res) {
            if (res.code === 1) {
                currentChunk++;
                
                if (progressCallback) {
                    progressCallback(Math.round(currentChunk / chunks * 100));
                }
                
                if (currentChunk < chunks) {
                    uploadChunk();
                } else {
                    callback(res);
                }
            } else {
                toast(res.msg, 'error');
            }
        });
    }
    
    uploadChunk();
}

// 自动链接网址
function auto_link(text) {
    const urlPattern = /(https?:\/\/[^\s<]+)/gi;
    return text.replace(urlPattern, '<a href="$1" target="_blank">$1</a>');
}