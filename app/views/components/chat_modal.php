<!-- MODAL CHAT -->
<div id="modal-chat" class="fab-modal hidden">
    <div class="fab-modal-content">
        <div class="fab-header">
            <h3>Chat dengan Admin</h3>
            <button class="close-fab">&times;</button>
        </div>
        <div class="fab-body" id="chat-content">
            <div id="chat-messages" class="space-y-3 mb-4 overflow-y-auto h-64 p-2">
                <!-- Isi Chat Akan Muncul Di Sini -->
            </div>
            <form id="form-chat-user">
                <input type="hidden" id="chat-csrf-token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-2">
                    <label class="text-xs font-bold text-gray-600">Nama Anda</label>
                  <input type="text" id="chat-nama" name="nama"
                        class="w-full p-2 border rounded-lg text-sm" placeholder="Nama Anda" required>
                </div>
                <div class="mb-2">
                    <label class="text-xs font-bold text-gray-600">Nomor WhatsApp</label>
                    <input type="tel" id="chat-nomor_hp" name="nomor_hp"
                        class="w-full p-2 border rounded-lg text-sm" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="flex items-center gap-2">
                    <textarea id="chat-pesan" name="pesan"
                        class="flex-grow p-2 border rounded-lg text-sm" rows="1"
                        placeholder="Tulis pesan Anda..." required></textarea>
                    
                    <!-- Tombol Kirim -->
                    <button type="submit" id="btn-kirim-chat"
                        class="bg-primary-orange text-white py-2 px-4 rounded-lg font-bold text-sm hover:bg-orange-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/chat-wa.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // [FIX] LOGIKA URL API YANG LEBIH KUAT
    // 1. Cek apakah ada BASE_URL dari PHP
    const PHP_BASE_URL = "<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>";
    
    // 2. Fungsi untuk mendapatkan URL file yang valid (Absolute Path)
    function getFileUrl(filename) {
        if (PHP_BASE_URL && PHP_BASE_URL.trim() !== '') {
            // Hapus slash di akhir jika ada, lalu tambahkan filename
            return PHP_BASE_URL.replace(/\/$/, "") + '/' + filename;
        }
        // Jika BASE_URL kosong (misal di hosting live), gunakan root domain (/)
        // Ini memastikan 'chat_send.php' dicari di 'temanamal.org/chat_send.php'
        return '/' + filename; 
    }

    const chatContent = document.getElementById('chat-content');
    const chatMessages = document.getElementById('chat-messages');
    const formChat = document.getElementById('form-chat-user');
    const nomorHpInput = document.getElementById('chat-nomor_hp');
    const namaInput = document.getElementById('chat-nama');
    const pesanInput = document.getElementById('chat-pesan');
    const btnKirim = document.getElementById('btn-kirim-chat'); 

    // Load pesan saat pertama kali buka
    fetchChatMessages();

    function addMessageToChat(sender, message, timestamp) {
        const bubbleClass = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-admin';
        const safeMessage = message.replace(/</g, "&lt;").replace(/>/g, "&gt;");
        
        const messageElement = `
            <div class="chat-message ${bubbleClass}">
                <div class="chat-bubble">
                    <p>${safeMessage}</p>
                    <span class="chat-timestamp">${timestamp}</span>
                </div>
            </div>
        `;
        chatMessages.innerHTML += messageElement;
        chatMessages.scrollTop = chatMessages.scrollHeight; 
    }

    function fetchChatMessages() {
        const currentKodeUser = localStorage.getItem('chat_kode_user');
        if (!currentKodeUser) {
            chatMessages.innerHTML = '<div class="text-center text-sm text-gray-500 mt-4">Silakan perkenalkan diri Anda.</div>';
            return;
        }

        // [FIX] Gunakan getFileUrl
        const fetchUrl = '<?= BASE_URL ?>/api/chat/fetch';

        fetch(fetchUrl + '?kode_user=' + currentKodeUser)
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // Silent fail untuk fetch agar tidak spam error alert
                    console.error("Fetch Error (Not JSON):", text);
                    return { success: false, message: "Server Error" };
                }
            })
            .then(data => {
                if (data.success) {
                    chatMessages.innerHTML = ''; 
                    if (data.messages.length === 0) {
                         chatMessages.innerHTML = '<div class="text-center text-sm text-gray-500 mt-4">Belum ada pesan.</div>';
                    } else {
                        data.messages.forEach(msg => {
                            addMessageToChat(msg.sender, msg.message, msg.created_at);
                        });
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // --- LOGIKA PENGIRIMAN PESAN ---
    // [FIX] Cegah Event Listener Ganda (Double Binding)
    if (formChat.dataset.bound) return; 
    formChat.dataset.bound = "true";

    formChat.addEventListener('submit', function(e) {
        e.preventDefault();

        // [FIX] Cegah double submit jika tombol sudah disabled (sedang proses)
        if (btnKirim.disabled) return;

        const nomorHp = nomorHpInput.value;
        const nama = namaInput.value;
        const pesan = pesanInput.value;

        if (!pesan.trim() || !nomorHp.trim() || !nama.trim()) {
            alert("Mohon lengkapi semua data.");
            return;
        }

        // Matikan tombol
        btnKirim.disabled = true;
        btnKirim.innerHTML = '<i class="fa fa-spinner fa-spin"></i> ...';

        const payload = {
            kode_user: localStorage.getItem('chat_kode_user'), 
            nomor_hp: nomorHp,
            pesan: pesan,
            nama: nama,
            csrf_token: document.getElementById('chat-csrf-token').value
        };

        // [FIX] Gunakan getFileUrl
        const sendUrl = '<?= BASE_URL ?>/api/chat/send_secure';

        fetch(sendUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    // [DEBUG ERROR] Tampilkan diagnosa jika respon bukan JSON
                    console.error("Raw Server Response:", text);
                    
                    let errorHint = "Respon server bukan JSON yang valid.";
                    
                    // Deteksi Error 404 (File Hilang/Salah Path)
                    if (response.status === 404 || text.includes("404 Not Found") || text.includes("File not found")) {
                        errorHint = `File API tidak ditemukan di: ${sendUrl}. Pastikan file chat_send.php sudah di-upload.`;
                    }
                    // Deteksi Error PHP (Syntax/Database)
                    else if (text.includes("<b>Fatal error</b>") || text.includes("<b>Warning</b>")) {
                        errorHint = "Terjadi error di kode PHP server (Cek Console/Log).";
                    }

                    throw new Error(errorHint);
                }
            })
            .then(data => {
                if (data.success) {
                    pesanInput.value = ''; 
                    
                    if (data.kode_user) {
                        localStorage.setItem('chat_kode_user', data.kode_user);
                    }
                    
                    fetchChatMessages(); 
                } else {
                    alert("Gagal: " + data.message);
                }
            }) 
            .catch(error => {
                console.error('Error:', error);
                // Tampilkan pesan error yang sudah diperjelas
                alert('Gagal mengirim pesan:\n' + error.message);
            })
            .finally(() => {
                btnKirim.disabled = false;
                btnKirim.innerHTML = 'Kirim';
            });
    });
});
</script>
