// assets/js/floating-button.js
document.addEventListener('DOMContentLoaded', function() {
    const fabHistory = document.getElementById('fab-history');
    const fabChat = document.getElementById('fab-chat');
    const modalHistory = document.getElementById('modal-history');
    const modalChat = document.getElementById('modal-chat');
    const closeButtons = document.querySelectorAll('.close-fab');

    // Helper: Toggle Modal
    function toggleModal(modal) {
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            // Small delay to allow display:block to apply before opacity transition
            setTimeout(() => modal.classList.add('active'), 10);
        } else {
            modal.classList.remove('active');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }
    }

    // Event Listeners
    fabHistory.addEventListener('click', function() {
        toggleModal(modalHistory);
        loadHistory();
    });

    fabChat.addEventListener('click', function() {
         toggleModal(modalChat);
           
    });

    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modal = this.closest('.fab-modal');
            toggleModal(modal);
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('fab-modal')) {
            toggleModal(e.target);
        }
    });

    // Load History Function
    function loadHistory() {
        const container = document.getElementById('history-content');
        
        fetch('get_history.php')
            .then(response => response.text())
            .then(html => {
                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<p class="text-center text-red-500">Gagal memuat riwayat.</p>';
            });
    }

    // Handle Chat Submit
    const formChat = document.getElementById('form-chat-user');
    formChat.addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button');
        const originalText = btn.innerText;
        
        btn.disabled = true;
        btn.innerText = 'Mengirim...';

        const formData = new FormData(this);

        fetch('chat_send.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server Error: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON:', text);
                throw new Error('Respon server bermasalah.');
            }
        })
        .then(data => {
            if (data.success) {
                alert('Pesan terkirim! Admin akan membalas ke WhatsApp Anda.');
                formChat.reset();
                toggleModal(modalChat);
            } else {
                alert('Gagal: ' + (data.message || 'Terjadi kesalahan.'));
            }
        })
        .catch(err => {
            console.error('Chat Error:', err);
            alert('Gagal mengirim pesan: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    });
});
