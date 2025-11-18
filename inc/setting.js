// 设置页面脚本

// 保存设置
function save_setting() {
    const title = document.getElementById('title').value.trim();
    const subtitle = document.getElementById('subtitle').value.trim();
    const nickname = document.getElementById('nickname').value.trim();
    const username = document.getElementById('username').value.trim();
    const old_password = document.getElementById('old_password').value;
    const new_password = document.getElementById('new_password').value;
    
    if (!title || !nickname || !username) {
        toast('标题、昵称和用户名不能为空', 'error');
        return;
    }
    
    const data = {
        title,
        subtitle,
        nickname,
        username,
        old_password,
        new_password
    };
    
    ajax('setting.php?act=save', data, function(res) {
        if (res.code === 1) {
            toast(res.msg, 'success');
            setTimeout(() => {
                location.href = 'index.php';
            }, 1500);
        } else {
            toast(res.msg, 'error');
        }
    });
}