// c:\xampp\htdocs\temanamal\assets\js\chat-wa.js
document.addEventListener('DOMContentLoaded', function() {
    const chatContent = document.getElementById('chat-content');
    const chatMessages = document.getElementById('chat-messages');
    const formChat = document.getElementById('form-chat-user');
    const nomorHpInput = document.getElementById('chat-nomor_hp');
    const pesanInput = document.getElementById('chat-pesan');
    let isSending = false;

    // Fungsi render pesan
    function addMessageToChat(sender, message, timestamp) {
        const bubbleClass = sender === 'user' ? 'chat-bubble-user' : 'chat-bubble-admin';
        const messageElement = `
            <div class="chat-message ${bubbleClass}">
                <div class="chat-bubble">
                    <p>${message}</p>
                    <span class="chat-timestamp">${timestamp}</span>
                </div>
            </div>
        `;
        chatMessages.insertAdjacentHTML('beforeend', messageElement);
    }

    // Fungsi ambil pesan (Polling)
    function fetchChatMessages() {
        fetch('chat_fetch.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON from chat_fetch:", text);
                    throw new Error("Respon server tidak valid (bukan JSON).");
                }
            })
            .then(data => {
                if (data.success) {
                    chatMessages.innerHTML = ''; // Reset view
                    if(data.messages.length === 0) {
                        chatMessages.innerHTML = '<p class="text-center text-gray-400 text-xs mt-4">Belum ada pesan. Mulai percakapan!</p>';
                    }
                    data.messages.forEach(msg => {
                        addMessageToChat(msg.sender, msg.message, msg.created_at);
                    });
                    // Scroll ke bawah
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            })
            .catch(error => console.error('Error fetching chat:', error));
    }

    // Load pesan saat halaman dibuka & set interval
    fetchChatMessages();
    setInterval(fetchChatMessages, 5000); // Update setiap 5 detik

    // Handle Kirim Pesan
    formChat.addEventListener('submit', function(e) {
        e.preventDefault();
        if (isSending) return;

        const nomorHp = nomorHpInput.value.trim();
        const pesan = pesanInput.value.trim();

        if (!nomorHp || !pesan) return;

        isSending = true;
        const btn = this.querySelector('button');
        const originalText = btn.innerText;
        btn.innerText = '...';
        btn.disabled = true;

        // Tampilkan pesan user secara optimistik (langsung muncul)
        const now = new Date();
        const timeString = now.getHours() + ":" + String(now.getMinutes()).padStart(2, '0');
        addMessageToChat('user', pesan, timeString);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        pesanInput.value = ''; // Clear input

        // Kirim ke server
        const formData = new FormData();
        formData.append('nomor_hp', nomorHp);
        formData.append('pesan', pesan);

        fetch('chat_send.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON from chat_send:", text);
                    throw new Error("Respon server tidak valid.");
                }
            });
        })
        .then(data => {
            if (!data.success) {
                alert('Gagal mengirim pesan: ' + data.message);
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            // alert('Gagal terhubung ke server: ' + err.message); // Optional: Uncomment untuk debug user
        })
        .finally(() => {
            isSending = false;
            btn.innerText = originalText;
            btn.disabled = false;
        });
    });
});
