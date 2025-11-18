// 首页脚本
let currentPage = 1;
let currentSearch = '';
let uploadedFiles = [];

// 页面加载完成
document.addEventListener('DOMContentLoaded', function() {
    load_wlog_list();
    
    // 内容字数统计
    const contentInput = document.getElementById('publish_content');
    if (contentInput) {
        contentInput.addEventListener('input', function() {
            const length = this.value.length;
            document.getElementById('content_length').textContent = length;
            
            if (length > 300) {
                this.value = this.value.substring(0, 300);
                document.getElementById('content_length').textContent = 300;
            }
        });
    }
});

// 显示登录框
function show_login() {
    const html = `
        <div style="padding:20px;">
            <h2 style="margin-bottom:20px;">登录</h2>
            <input type="text" id="login_username" placeholder="用户名" style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:4px;">
            <input type="password" id="login_password" placeholder="密码" style="width:100%;padding:10px;margin-bottom:20px;border:1px solid #ddd;border-radius:4px;">
            <button onclick="do_login()" style="width:100%;padding:12px;background:#1a73e8;color:#fff;border:none;border-radius:4px;cursor:pointer;">登录</button>
        </div>
    `;
    show_modal(html, '400px');
}

// 执行登录
function do_login() {
    const username = document.getElementById('login_username').value.trim();
    const password = document.getElementById('login_password').value;
    
    if (!username || !password) {
        toast('请填写用户名和密码', 'error');
        return;
    }
    
    ajax('index.php?act=login', {username, password}, function(res) {
        if (res.code === 1) {
            toast(res.msg, 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            toast(res.msg, 'error');
        }
    });
}

// 退出登录
function logout() {
    confirm_dialog('确定要退出登录吗？', function() {
        ajax('index.php?act=logout', {}, function(res) {
            if (res.code === 1) {
                toast(res.msg, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        });
    });
}

// 加载微博列表
function load_wlog_list(page = 1) {
    currentPage = page;
    
    ajax('index.php?act=get_list', {page, search: currentSearch}, function(res) {
        if (res.code === 1) {
            render_wlog_list(res.data.list);
            render_pagination(res.data.page_info);
        } else {
            toast(res.msg, 'error');
        }
    }, 'GET');
}

// 渲染微博列表
function render_wlog_list(list) {
    const container = document.getElementById('wlog_list');
    
    if (list.length === 0) {
        container.innerHTML = '<div style="text-align:center;padding:50px;color:#999;">暂无微博</div>';
        return;
    }
    
    let html = '';
    list.forEach(item => {
        const topClass = item.is_top == 1 ? 'is-top' : '';
        const hiddenText = item.is_hidden == 1 ? ' (已隐藏)' : '';
        
        html += `
            <div class="wlog-item ${topClass}">
                <div class="wlog-content">${auto_link(item.content)}</div>
                ${render_files(item.file_list)}
                <div class="wlog-meta">
                    <span>${format_time(item.create_time)}${hiddenText}</span>
                    ${render_actions(item)}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// 渲染附件
function render_files(files) {
    if (!files || files.length === 0) return '';
    
    const images = files.filter(f => f.filetype === 'image');
    const videos = files.filter(f => f.filetype === 'video');
    const audios = files.filter(f => f.filetype === 'audio');
    const others = files.filter(f => f.filetype === 'file');
    
    let html = '<div class="wlog-files">';
    
    // 图片网格
    if (images.length > 0) {
        html += `<div class="file-grid count-${images.length}">`;
        images.forEach(img => {
            html += `<div class="file-item" onclick="preview_image('${jsc}${img.filepath}')">
                <img src="${jsc}${img.filepath}" alt="${img.filename}">
            </div>`;
        });
        html += '</div>';
    }
    
    // 视频
    videos.forEach(v => {
        html += `<div class="file-item video" onclick="play_video('${jsc}${v.filepath}')">
            <video src="${jsc}${v.filepath}" style="width:100%;max-height:300px;"></video>
        </div>`;
    });
    
    // 音频
    audios.forEach(a => {
        html += `<div class="file-item audio" onclick="play_audio('${jsc}${a.filepath}','${a.filename}')">
            <div style="padding:40px;background:#f5f5f5;text-align:center;border-radius:4px;">
                🎵 ${a.filename}
            </div>
        </div>`;
    });
    
    // 其他文件
    others.forEach(f => {
        html += `<div class="file-download" onclick="download_file(${f.id})">
            <span>📎</span>
            <span style="flex:1;">${f.filename}</span>
            <span>${format_size(f.filesize)}</span>
        </div>`;
    });
    
    html += '</div>';
    return html;
}

// 渲染操作按钮
function render_actions(item) {
    const isLogin = document.querySelector('.user-info') !== null;
    if (!isLogin) return '';
    
    const topText = item.is_top == 1 ? '取消置顶' : '置顶';
    const hiddenText = item.is_hidden == 1 ? '显示' : '隐藏';
    
    return `
        <div class="wlog-actions">
            <button onclick="toggle_top(${item.id}, ${item.is_top == 1 ? 0 : 1})">${topText}</button>
            <button onclick="toggle_hidden(${item.id}, ${item.is_hidden == 1 ? 0 : 1})">${hiddenText}</button>
            <button onclick="delete_wlog(${item.id})">删除</button>
            <button onclick="share_wlog(${item.id})">分享</button>
        </div>
    `;
}

// 渲染分页
function render_pagination(info) {
    const container = document.getElementById('pagination');
    
    if (info.total_page <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '';
    
    // 首页
    html += `<button ${info.page === 1 ? 'disabled' : ''} onclick="load_wlog_list(1)">首页</button>`;
    
    // 上一页
    html += `<button ${info.page === 1 ? 'disabled' : ''} onclick="load_wlog_list(${info.page - 1})">上一页</button>`;
    
    // 页码选择
    html += '<select onchange="load_wlog_list(this.value)">';
    for (let i = 1; i <= info.total_page; i++) {
        html += `<option value="${i}" ${i === info.page ? 'selected' : ''}>第${i}页</option>`;
    }
    html += '</select>';
    
    // 下一页
    html += `<button ${info.page === info.total_page ? 'disabled' : ''} onclick="load_wlog_list(${info.page + 1})">下一页</button>`;
    
    // 末页
    html += `<button ${info.page === info.total_page ? 'disabled' : ''} onclick="load_wlog_list(${info.total_page})">末页</button>`;
    
    container.innerHTML = html;
}

// 搜索微博
function search_wlog() {
    currentSearch = document.getElementById('search_input').value.trim();
    load_wlog_list(1);
}

// 重置搜索
function reset_search() {
    document.getElementById('search_input').value = '';
    currentSearch = '';
    load_wlog_list(1);
}

// 发布微博
function publish_wlog() {
    const content = document.getElementById('publish_content').value.trim();
    
    if (!content) {
        toast('请输入微博内容', 'error');
        return;
    }
    
    const fileIds = uploadedFiles.map(f => f.id).join(',');
    
    ajax('index.php?act=publish', {content, file_ids: fileIds}, function(res) {
        if (res.code === 1) {
            toast(res.msg, 'success');
            document.getElementById('publish_content').value = '';
            document.getElementById('content_length').textContent = '0';
            document.getElementById('preview_files').innerHTML = '';
            uploadedFiles = [];
            load_wlog_list(1);
        } else {
            toast(res.msg, 'error');
        }
    });
}

// 删除微博
function delete_wlog(id) {
    confirm_dialog('确定要删除这条微博吗？', function() {
        ajax('index.php?act=delete', {id}, function(res) {
            if (res.code === 1) {
                toast(res.msg, 'success');
                load_wlog_list(currentPage);
            } else {
                toast(res.msg, 'error');
            }
        });
    });
}

// 切换置顶
function toggle_top(id, top) {
    ajax('index.php?act=toggle_top', {id, top}, function(res) {
        if (res.code === 1) {
            toast(res.msg, 'success');
            load_wlog_list(currentPage);
        } else {
            toast(res.msg, 'error');
        }
    });
}

// 切换隐藏
function toggle_hidden(id, hidden) {
    ajax('index.php?act=toggle_hidden', {id, hidden}, function(res) {
        if (res.code === 1) {
            toast(res.msg, 'success');
            load_wlog_list(currentPage);
        } else {
            toast(res.msg, 'error');
        }
    });
}

// 分享微博
function share_wlog(id) {
    const url = location.origin + location.pathname + '?id=' + id;
    const input = document.createElement('input');
    input.value = url;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    toast('链接已复制到剪贴板', 'success');
}

// 上传图片
function upload_images(files) {
    if (files.length === 0) return;
    
    if (uploadedFiles.filter(f => f.type === 'image').length + files.length > 9) {
        toast('最多只能上传9张图片', 'error');
        return;
    }
    
    Array.from(files).forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            toast('图片大小不能超过10MB', 'error');
            return;
        }
        
        compress_image(file, function(blob) {
            const formData = new FormData();
            formData.append('file', blob, file.name);
            formData.append('type', 'image');
            
            ajax('upload.php?act=upload', formData, function(res) {
                if (res.code === 1) {
                    uploadedFiles.push({id: res.data.id, type: 'image', url: res.data.filepath});
                    render_preview();
                } else {
                    toast(res.msg, 'error');
                }
            });
        });
    });
}

// 上传视频
function upload_video(file) {
    if (!file) return;
    
    if (uploadedFiles.filter(f => f.type === 'video').length > 0) {
        toast('只能上传1个视频', 'error');
        return;
    }
    
    if (file.size > 50 * 1024 * 1024) {
        toast('视频大小不能超过50MB', 'error');
        return;
    }
    
    toast('正在上传视频...', 'success');
    
    chunk_upload(file, 'upload.php?act=chunk_upload&type=video', function(res) {
        if (res.code === 1) {
            uploadedFiles.push({id: res.data.id, type: 'video', url: res.data.filepath});
            render_preview();
            toast('视频上传成功', 'success');
        } else {
            toast(res.msg, 'error');
        }
    }, function(progress) {
        console.log('上传进度：' + progress + '%');
    });
}

// 上传音频
function upload_audio(file) {
    if (!file) return;
    
    if (uploadedFiles.filter(f => f.type === 'audio').length > 0) {
        toast('只能上传1个音频', 'error');
        return;
    }
    
    if (file.size > 50 * 1024 * 1024) {
        toast('音频大小不能超过50MB', 'error');
        return;
    }
    
    toast('正在上传音频...', 'success');
    
    chunk_upload(file, 'upload.php?act=chunk_upload&type=audio', function(res) {
        if (res.code === 1) {
            uploadedFiles.push({id: res.data.id, type: 'audio', url: res.data.filepath, name: file.name});
            render_preview();
            toast('音频上传成功', 'success');
        }
    });
}

// 上传文件
function upload_file(file) {
    if (!file) return;
    
    if (file.size > 50 * 1024 * 1024) {
        toast('文件大小不能超过50MB', 'error');
        return;
    }
    
    toast('正在上传文件...', 'success');
    
    chunk_upload(file, 'upload.php?act=chunk_upload&type=file', function(res) {
        if (res.code === 1) {
            uploadedFiles.push({id: res.data.id, type: 'file', url: res.data.filepath, name: file.name});
            render_preview();
            toast('文件上传成功', 'success');
        }
    });
}

// 渲染预览
function render_preview() {
    const container = document.getElementById('preview_files');
    let html = '';
    
    uploadedFiles.forEach((file, index) => {
        if (file.type === 'image') {
            html += `
                <div class="preview-item">
                    <img src="${file.url}">
                    <div class="remove-btn" onclick="remove_file(${index})">×</div>
                </div>
            `;
        } else {
            html += `
                <div class="preview-item" style="padding:10px;background:#f5f5f5;border-radius:4px;">
                    <span>${file.name || file.type}</span>
                    <span class="remove-btn" onclick="remove_file(${index})">×</span>
                </div>
            `;
        }
    });
    
    container.innerHTML = html;
}

// 移除文件
function remove_file(index) {
    uploadedFiles.splice(index, 1);
    render_preview();
}

// 下载文件
function download_file(id) {
    window.open('download.php?id=' + id, '_blank');
}