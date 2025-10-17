<?php
include 'connect.php';


// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    die("Bạn không có quyền truy cập trang này!");
}

$adminId = (int)$_SESSION['user_id'];

// API trả badge số tin nhắn chưa đọc
if (isset($_GET['action']) && $_GET['action'] === 'new_count') {
    $userId = isset($_GET['user']) ? (int)$_GET['user'] : 0;
    if ($userId <= 0) { echo json_encode(['count'=>0]); exit; }

    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM tinnhan WHERE nguoigui_id=? AND nguoinhan_id=? AND trangthai='chua_doc'");
    $stmt->bind_param("ii", $userId, $adminId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    echo json_encode(['count' => (int)$row['cnt']]);
    exit;
}

// Lấy danh sách user
$sql = "SELECT id, username, avatar FROM taikhoan WHERE role='user' ORDER BY username ASC";
$res = $conn->query($sql);
$users = [];
while($r = $res->fetch_assoc()) $users[] = $r;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Chat</title>
<style>
body { font-family: Arial,sans-serif; background:#f0f2f5; margin:0; padding:20px; }
.user-list { width:300px; border:1px solid #ddd; background:#fff; border-radius:8px; overflow:hidden; }
.user-row { display:flex; align-items:center; padding:12px; cursor:pointer; border-bottom:1px solid #f0f0f0; position:relative; transition: background 0.3s; }
.user-row:hover { background:#f8f9fa; }
.user-row.active { background:#e3f2fd; border-left: 4px solid #0984e3; }
.user-avatar { width:40px; height:40px; border-radius:50%; margin-right:12px; object-fit:cover; border:2px solid #e0e0e0; }
.badge { position:absolute; right:12px; top:12px; background:#e74c3c; color:#fff; font-size:11px; font-weight:bold; padding:2px 6px; border-radius:10px; min-width:18px; text-align:center; display:none; }
#admin-chat-box { border:1px solid #ddd; border-radius:8px; overflow:hidden; display:flex; flex-direction:column; height:500px; background:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.1); }
#admin-chat-header { background:#0984e3; color:#fff; padding:12px; font-weight:bold; font-size:16px; }
#admin-chat-content { flex:1; padding:15px; overflow-y:auto; background:#f7f9fa; display:flex; flex-direction:column; }
#admin-input { flex:1; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:14px; }
#admin-send { padding:10px 20px; background:#0984e3; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px; transition:background 0.3s; }
#admin-send:hover { background:#086cc3; }
#admin-send:disabled { background:#ccc; cursor:not-allowed; }
.message { padding:10px 14px; border-radius:12px; max-width:70%; word-wrap:break-word; margin:4px 0; position:relative; line-height:1.4; }
.message.admin { background:#0984e3; color:#fff; align-self:flex-end; margin-left:auto; }
.message.user { background:#fff; border:1px solid #e0e0e0; align-self:flex-start; margin-right:auto; }
.message-time { font-size:10px; color:inherit; opacity:0.8; margin-top:4px; text-align:right; }
.user-info { display:flex; flex-direction:column; flex:1; min-width:0; }
.user-name { font-weight:bold; color:#333; font-size:14px; }
.last-message { font-size:12px; color:#666; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.user-newmsg { font-size:11px; margin-top:2px; display:none; }
.user-newmsg.has-message { display:block; color:#e74c3c; font-weight:bold; }
.empty-chat { display:flex; justify-content:center; align-items:center; height:100%; color:#999; font-style:italic; text-align:center; }
.no-messages { text-align:center; color:#999; margin-top:50px; font-style:italic; }
.chat-input-container { padding:12px; border-top:1px solid #ddd; background:#fff; display:flex; gap:8px; align-items:center; }
.system-message { text-align:center; color:#999; font-style:italic; margin:10px 0; font-size:12px; }
</style>
</head>
<body>
<h3>💬 Chat với người dùng</h3>
<div style="display:flex; gap:20px;">
  <div class="user-list">
    <?php foreach($users as $u):
        $avatar = $u['avatar'] ?? "";
        if($avatar && file_exists($_SERVER['DOCUMENT_ROOT'] . "/banhangonline/" . ltrim($avatar, "/"))){
            $avatarPath = "../" . ltrim($avatar, "/");
        } else {
            $avatarPath = "../img/avatar.png";
        }
    ?>
   <div class="user-row" data-userid="<?php echo $u['id']; ?>" data-username="<?php echo htmlspecialchars($u['username']); ?>">
    <img class="user-avatar" src="<?php echo htmlspecialchars($avatarPath); ?>" alt="avatar" onerror="this.src='../img/avatar.png'">
    <div class="user-info">
        <div class="user-name"><?php echo htmlspecialchars($u['username']); ?></div>
        <small class="user-newmsg no-message" id="newmsg-<?php echo $u['id']; ?>"></small>
    </div>
    <div class="badge" id="badge-<?php echo $u['id']; ?>">0</div>
   </div>
    <?php endforeach; ?>
  </div>

  <div style="flex:1;">
    <div id="admin-chat-box">
      <div id="admin-chat-header">💬 Admin Chat <span id="chat-with"></span></div>
      <div id="admin-chat-content">
        <div class="empty-chat">👆 Vui lòng chọn một người dùng<br>để bắt đầu trò chuyện</div>
      </div>
      <div class="chat-input-container">
        <input id="admin-input" placeholder="Nhập tin nhắn..." disabled>
        <button id="admin-send">Gửi</button>
      </div>
    </div>
  </div>
</div>

<script>
const adminId = <?php echo $adminId; ?>;
let selectedUser = null;
let convInterval = null;
let lastNotifiedUser = null;
const content = document.getElementById('admin-chat-content');
const chatWithLabel = document.getElementById('chat-with');
const adminInput = document.getElementById('admin-input');
const adminSend = document.getElementById('admin-send');

// Yêu cầu quyền Notification
if("Notification" in window && Notification.permission === "default") {
    Notification.requestPermission();
}

function notifyNewMessage(username, count){
    if("Notification" in window && Notification.permission === "granted"){
        new Notification(`💬 Tin nhắn mới từ ${username}`, { 
            body: `Bạn có ${count} tin nhắn chưa đọc từ ${username}`,
            icon: '../img/notification-icon.png'
        });
    }
}

// Click chọn user
document.querySelectorAll('.user-row').forEach(r=>{
    r.addEventListener('click', ()=>{
        // Xóa active class từ tất cả user
        document.querySelectorAll('.user-row').forEach(row => {
            row.classList.remove('active');
        });
        
        // Thêm active class cho user được chọn
        r.classList.add('active');
        
        selectedUser = r.getAttribute('data-userid');
        const username = r.getAttribute('data-username');
        chatWithLabel.textContent = ' - ' + username;
        
        // Kích hoạt input và button
        adminInput.disabled = false;
        adminSend.disabled = false;
        adminInput.placeholder = "Nhập tin nhắn cho " + username + "...";
        adminInput.focus();
        
        loadConv();
        markAsRead(selectedUser);
        hideBadgeAndText(selectedUser);
        
        if(convInterval) clearInterval(convInterval);
        convInterval = setInterval(loadConv, 2000);
    });
});

// Đánh dấu đã đọc
function markAsRead(userId) {
    fetch('chat_mark_read.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'user_id=' + userId + '&admin_id=' + adminId
    });
}

// Gửi tin nhắn
adminSend.addEventListener('click', sendMessage);
adminInput.addEventListener('keypress', function(e) {
    if(e.key === 'Enter') sendMessage();
});

function sendMessage() {
    if(!selectedUser) return alert('Vui lòng chọn người dùng trước');
    const msg = adminInput.value.trim();
    if(!msg) return;
    
    // Vô hiệu hóa nút gửi tạm thời
    adminSend.disabled = true;
    adminSend.textContent = 'Đang gửi...';
    
    fetch('chat_save_admin.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg) + '&partner=' + encodeURIComponent(selectedUser)
    }).then(r=>r.json()).then(j=>{
        if(j.success){
            adminInput.value='';
            loadConv();
            updateLastMessage(selectedUser, msg, true);
        }
    }).finally(() => {
        adminSend.disabled = false;
        adminSend.textContent = 'Gửi';
    });
}

// Load conversation
function loadConv(){
    if(!selectedUser) {
        showEmptyChat();
        return;
    }
    
    fetch('chat_load_admin.php?partner=' + selectedUser)
    .then(r=>r.json())
    .then(list=>{
        content.innerHTML='';
        
        if(list.length === 0) {
            showNoMessages();
            return;
        }
        
        let lastDate = '';
        list.forEach(m=>{
            const isAdmin = (m.nguoigui_id == adminId);
            const messageDate = new Date(m.thoigian).toLocaleDateString();
            
            // Hiển thị ngày nếu khác với tin nhắn trước
            if(messageDate !== lastDate) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'system-message';
                dateDiv.textContent = new Date(m.thoigian).toLocaleDateString('vi-VN', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                content.appendChild(dateDiv);
                lastDate = messageDate;
            }
            
            const div = document.createElement('div');
            div.style.display='flex';
            div.style.flexDirection='column';
            div.style.alignItems = isAdmin ? 'flex-end' : 'flex-start';
            
            const bub = document.createElement('div');
            bub.className = 'message ' + (isAdmin ? 'admin':'user');
            bub.textContent = m.noidung;
            
            const time = document.createElement('div');
            time.className='message-time';
            time.textContent = new Date(m.thoigian).toLocaleTimeString('vi-VN', {hour:'2-digit',minute:'2-digit'});
            
            bub.appendChild(time);
            div.appendChild(bub);
            content.appendChild(div);
        });
        content.scrollTop = content.scrollHeight;
    }).catch(err => {
        console.error('Lỗi tải tin nhắn:', err);
    });
}

function showEmptyChat() {
    content.innerHTML = '<div class="empty-chat">👆 Vui lòng chọn một người dùng<br>để bắt đầu trò chuyện</div>';
}

function showNoMessages() {
    content.innerHTML = '<div class="empty-chat">💬 Chưa có tin nhắn nào<br>Hãy bắt đầu cuộc trò chuyện!</div>';
}

// Update badge + thông báo tin nhắn mới
let notifiedUsers = new Set();

function checkNewMessages(){
    fetch('chat_new_message.php')
        .then(res => res.json())
        .then(data => {
            if(!data.success) return;

            data.users.forEach(u => {
                const b = document.getElementById('badge-'+u.user_id);
                const txt = document.getElementById('newmsg-'+u.user_id);

                if(b){
                    b.style.display = 'block';
                    b.textContent = u.count > 99 ? '99+' : u.count;
                }
                if(txt){
                    txt.className = 'user-newmsg has-message';
                    txt.textContent = `Bạn có ${u.count} tin nhắn mới`;
                    txt.style.display = 'block';
                }

                // Desktop notification
                if(selectedUser != u.user_id && !notifiedUsers.has(u.user_id)){
                    notifyNewMessage(u.username, u.count);
                    notifiedUsers.add(u.user_id);
                }
            });

            // Ẩn badge cho user không còn tin nhắn
            document.querySelectorAll('.user-row').forEach(r=>{
                const uid = r.getAttribute('data-userid');
                if(!data.users.find(x=>x.user_id==uid)){
                    const b = document.getElementById('badge-'+uid);
                    const txt = document.getElementById('newmsg-'+uid);
                    if(b){ b.style.display='none'; b.textContent='0'; }
                    if(txt){ txt.className='user-newmsg no-message'; txt.style.display='none'; txt.textContent=''; }
                    notifiedUsers.delete(uid);
                }
            });
        });
}

// Gọi ngay và lặp mỗi 3s
checkNewMessages();
setInterval(checkNewMessages, 3000);


function updateLastMessage(userId, message, isAdmin) {
    const lastMsgEl = document.getElementById('last-msg-' + userId);
    if(lastMsgEl) {
        if(message && message !== 'Chưa có tin nhắn') {
            const shortMsg = message.length > 25 ? message.substring(0, 25) + '...' : message;
            const prefix = isAdmin ? 'Bạn: ' : '';
            lastMsgEl.textContent = prefix + shortMsg;
            lastMsgEl.style.color = '#333';
            lastMsgEl.style.fontStyle = 'normal';
        } else {
            lastMsgEl.textContent = 'Chưa có tin nhắn';
            lastMsgEl.style.color = '#666';
            lastMsgEl.style.fontStyle = 'italic';
        }
    }
}


// Ẩn badge + text khi mở chat
function hideBadgeAndText(uid){
    const b = document.getElementById('badge-'+uid);
    const txt = document.getElementById('newmsg-'+uid);
    if(b){ 
        b.style.display='none'; 
        b.textContent='0'; 
    }
    if(txt){ 
        txt.className = 'user-newmsg no-message';
        txt.style.display = 'none';
        txt.textContent = '';
    }
}
function loadAllLastMessages() {
    document.querySelectorAll('.user-row').forEach(r => {
        const uid = r.getAttribute('data-userid');
        fetch('get_last_message.php?user_id=' + uid)
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    const lastMsgEl = document.getElementById('last-msg-' + uid);
                    if(lastMsgEl){
                        let msg = data.last_message;
                        if(msg.length > 25) msg = msg.substring(0,25) + '...';
                        lastMsgEl.textContent = (data.is_admin ? 'Bạn: ' : '') + msg;
                    }
                }
            });
    });
}

// Gọi ngay khi load page
loadAllLastMessages();


// Cập nhật badge liên tục
setInterval(updateBadges, 3000);
updateBadges();

// Tự động focus vào input khi chọn user
document.addEventListener('click', function(e) {
    if(e.target.closest('.user-row')) {
        setTimeout(() => adminInput.focus(), 100);
    }
});
</script>
</body>
</html>