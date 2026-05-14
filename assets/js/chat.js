document.addEventListener("DOMContentLoaded", function () {

    let pendingSentMessages = new Set();
    function updateUnreadBadges() {
        fetch('ajax/get_unread_counts.php')
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                const badges = document.querySelectorAll('.unread-badge');
                badges.forEach(badge => {
                    const userId = parseInt(badge.dataset.userId);
                    const found = data.counts.find(c => parseInt(c.sender_id) === userId);
                    if (found && found.unread > 0) {
                        badge.textContent = found.unread;
                        badge.classList.remove('hidden');
                    } else {
                        badge.textContent = '';
                        badge.classList.add('hidden');
                    }
                });
            })
            .catch(() => { });
    }

    updateUnreadBadges();

    setInterval(updateUnreadBadges, 5000);
    const userSearch = document.getElementById('userSearch');
    const userList = document.getElementById('userList');
    const menuBtn = document.getElementById('menuBtn');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const sidebarPanel = document.getElementById('sidebarPanel');
    const sidebarCloseBtn = document.getElementById('sidebarClose');
    const mobileUserSearch = document.getElementById('mobileUserSearch');
    const mobileUserList = document.getElementById('mobileUserList');
    function openSidebar() {
        sidebarOverlay.classList.remove('pointer-events-none');
        sidebarBackdrop.classList.add('opacity-50');
        sidebarPanel.classList.remove('-translate-x-full');
    }

    function closeSidebar() {
        sidebarBackdrop.classList.remove('opacity-50');
        sidebarPanel.classList.add('-translate-x-full');
        setTimeout(() => {
            sidebarOverlay.classList.add('pointer-events-none');
        }, 300);
    }
    if (menuBtn) {
        menuBtn.addEventListener('click', openSidebar);
    }
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', closeSidebar);
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }
    function syncMobileList() {
        const mainList = document.getElementById('userList');
        if (mainList && mobileUserList) {
            mobileUserList.innerHTML = mainList.innerHTML;
            mobileUserList.querySelectorAll('.user-item').forEach(link => {
                link.addEventListener('click', closeSidebar);
            });
        }
    }
    syncMobileList();
    /**
     *
     * @param {Array} users -{id, username, last_seen, image}
     */
    function renderUserList(users) {
        userList.innerHTML = '';

        for (const user of users) {
            let userAvatar = 'assets/images/default-avatar.png';
            if (user.image) {
                if (user.image.startsWith('http')) {
                    userAvatar = user.image;
                } else {
                    userAvatar = 'uploads/avatars/' + user.image;
                }
            }

            const lastSeenFormatted = user.last_seen
                ? user.last_seen.replace(' ', 'T') + 'Z'
                : '';

            const a = document.createElement('a');
            a.href = '?user=' + user.id;
            a.className = 'user-item p-3 mb-2 rounded-xl border transition duration-300 text-gray-700 hover:bg-blue-100 flex items-center gap-3 hover:text-black';

            const currentSelected = new URLSearchParams(window.location.search).get('user');
            if (user.id == currentSelected) {
                a.classList.add('!bg-blue-700', '!text-white');
            }

            a.innerHTML = `
            <div class="relative">
                <img src="${userAvatar}" alt="Avatar"
                     class="w-8 h-8 rounded-full object-cover border border-gray-300 shrink-0">
                <span class="status-do absolute left-5 w-2 h-2 rounded-full bg-gray-400 shrink-0 bottom-0.5"
                      data-last-seen="${lastSeenFormatted}"
                      data-user-id="${user.id}">
                </span>
            </div>
            <span class="truncate">${user.username}</span>
        `;

            userList.appendChild(a);
        }

        if (typeof updateOnlineStatus === 'function') {
            updateOnlineStatus();
        }
        
        if (mobileUserList) {
            mobileUserList.innerHTML = userList.innerHTML;
            mobileUserList.querySelectorAll('.user-item').forEach(link => {
                link.addEventListener('click', closeSidebar);
            });
        }
    }
    let mobileDebounceTimer;
    mobileUserSearch.addEventListener('input', function () {
        clearTimeout(mobileDebounceTimer);
        const query = this.value.trim();
        mobileDebounceTimer = setTimeout(() => {
            fetchUsers(query);
        }, 300);
    });
    /**
     * 
     * @param {string} query 
     */
    function fetchUsers(query) {
        fetch('ajax/search_users.php?query=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderUserList(data.users);
                }
            })
            .catch(() => {
            });
    }

    let debounceTimer;
    userSearch.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        debounceTimer = setTimeout(() => {
            fetchUsers(query);
        }, 300);
    });

    function fetchUsersStatus() {
        fetch('ajax/get_users_status.php')
            .then(res => res.json())
            .then(users => {
                users.forEach(user => {
                    const dot = document.querySelector(`.status-do[data-user-id="${user.id}"]`);
                    if (dot) {
                        
                        dot.dataset.lastSeen = user.last_seen ? new Date(user.last_seen.replace(' ', 'T') + 'Z') : '';
                    }
                });
                updateOnlineStatus();
            })
            .catch(() => { });
    }
    function updateOnlineStatus() {
        const dots = document.querySelectorAll('.status-do');
        dots.forEach(dot => {
            const lastSeen = dot.dataset.lastSeen;
            if (!lastSeen) return;
            const lastSeenTime = new Date(lastSeen).getTime();
            const now = Date.now();
            const diff = now - lastSeenTime;
            
            if (diff < 60 * 1000) {
                if (!dot.classList.contains('bg-green-500')) dot.classList.add('bg-green-500');
                if (dot.classList.contains('bg-gray-400')) dot.classList.remove('bg-gray-400');
            } else {
                if (!dot.classList.contains('bg-gray-400')) dot.classList.add('bg-gray-400');
                if (dot.classList.contains('bg-green-500')) dot.classList.remove('bg-green-500');
            }
        });
    }
    setInterval(fetchUsersStatus, 5000);

    updateOnlineStatus();
    setInterval(() => {
        fetch('ajax/update_activity.php').catch(() => { });
        updateOnlineStatus();
    }, 10000);
    if (!CHAT_USER_ID) return;

    setInterval(checkPendingReadStatus, 1000);
    function updateReadIcons(updatedIds) {
        updatedIds.forEach(id => {
            updateSingleReadIcon(id);
        });
    }
    function updateSingleReadIcon(id) {
        const msgDiv = document.getElementById(`msg-${id}`);
        if (!msgDiv) return;
        const readIcon = msgDiv.querySelector('.read-icon');
        if (readIcon) {
            readIcon.innerHTML = '✔✔';
            readIcon.setAttribute('title', 'Seen');
        }
    }
    function checkPendingReadStatus() {
        if (pendingSentMessages.size === 0 || !CHAT_USER_ID) return;

        const idsArray = Array.from(pendingSentMessages);
        fetch('ajax/check_read_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ids=' + encodeURIComponent(JSON.stringify(idsArray))
        })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.read_ids.length > 0) {
                    data.read_ids.forEach(id => {
                        updateSingleReadIcon(id);
                        pendingSentMessages.delete(id);
                    });
                    updateUnreadBadges(); 
                }
            });
    }
    let lastMessageId = 0;
    let unreadCount = 0;
    let userAtBottom = true;
    let renderedMessages = new Set();

    const box = document.getElementById("message");
    const form = document.getElementById("messageForm");
    const input = document.getElementById("messageInput");
    const scrollBtn = document.getElementById("scrollBtn");

    box.addEventListener("scroll", () => {
        userAtBottom =
            box.scrollTop + box.clientHeight >= box.scrollHeight - 50;
        if (userAtBottom) {
            unreadCount = 0;
            scrollBtn.classList.add("hidden");
        }
        else updateUnreadUI();
    });

    function updateUnreadUI() {
        if (scrollBtn.classList.contains("hidden")) scrollBtn.classList.remove("hidden");
        scrollBtn.innerText =
            unreadCount > 0 ? `↓ ${unreadCount} new` : `↓`;
    }

    function loadMessages() {
        fetch(`ajax/fetch_messages.php?user=${CHAT_USER_ID}&last_id=${lastMessageId}`)
            .then(res => res.json())
            .then(data => {
                const messages = data.messages;
                messages.forEach(msg => {
                    if (renderedMessages.has(msg.id)) return;
                    renderedMessages.add(msg.id);

                    if (msg.is_read == 0) {
                        pendingSentMessages.add(msg.id);
                    }


                    let div = document.createElement("div");

                    if (msg.sender_id == CURRENT_USER_ID) {
                        const readIcon = msg.is_read == 1
                            ? '<span class="read-icon text-blue-200 text-[8px] ml-1 absolute bottom-0.5 left-1" title="Seen">✔✔</span>'
                            : '<span class="read-icon text-gray-400 text-[8px] ml-1 absolute bottom-0.5 left-1" title="Sent">✔</span>';
                        div.innerHTML = `
                <div class="text-right   flex flex-col items-end" id="msg-${msg.id}">
                <p class="msg-text  relative inline-block min-w-12 bg-blue-500 text-white max-w-md p-2 rounded break-all">
                ${msg.message} 
                ${readIcon}
                        </p>
                        <div class="flex ">
                                <button title="edit" class="edit-btn text-xs p-1 rounded-2xl transition duration-300 text-gray-300 hover:text-white ml-1 hover:bg-gray-200" data-msg-id="${msg.id}">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                                </button>
                                <button title="delete" class="delete-btn text-xs p-1 rounded-2xl transition duration-300 text-gray-300 hover:text-white ml-1 hover:bg-gray-200" data-msg-id="${msg.id}">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    `;
                    } else {
                        div.innerHTML = `
                        <div class="text-left">
                            <p class="inline-block bg-gray-300 text-black max-w-md p-2 rounded break-all">
                                ${msg.message}
                            </p>
                        </div>
                    `;

                        if (!userAtBottom) {
                            unreadCount++;
                            updateUnreadUI();
                        }
                    }

                    box.appendChild(div);
                    lastMessageId = msg.id;
                });
                
                if (data.updated_read_ids && data.updated_read_ids.length > 0) {
                    updateReadIcons(data.updated_read_ids);
                    updateUnreadBadges();
                }

                if (userAtBottom) {
                    if (userAtBottom) {
                        requestAnimationFrame(() => {
                            box.scrollTop = box.scrollHeight;
                        });
                    }
                }
            });
    }

    loadMessages();
    setInterval(loadMessages, 1000);

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const message = input.value.trim();
        if (!message) return;

        fetch("ajax/send_message.php", {
            method: "POST",
            body: new URLSearchParams({
                message: message,
                receiver_id: CHAT_USER_ID
            })
        })
            .then(() => {
                input.value = "";
                loadMessages();
            });
    });

    scrollBtn.onclick = function () {
        scrollBtn.classList.add("hidden");

        box.scrollTo({
            top: box.scrollHeight,
            behavior: "smooth"
        });
        
    };
    box.addEventListener('click', function (e) {
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) {
            const msgId = editBtn.dataset.msgId;
            const messageDiv = document.getElementById(`msg-${msgId}`);
            if (!messageDiv) return;
            const textP = messageDiv.querySelector('.msg-text');

            const readIconEl = textP.querySelector('.read-icon');
            let originalText = '';
            if (readIconEl) {
                const clone = textP.cloneNode(true);
                clone.querySelector('.read-icon').remove();
                originalText = clone.textContent.trim();
            } else {
                originalText = textP.textContent.trim();
            }
            textP.style.display = 'none';
            editBtn.style.display = 'none';

            const input = document.createElement('input');
            input.type = 'text';
            input.value = originalText;
            input.className = 'bg-gray-50 text-gray-800 p-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 flex-1 min-w-0';
            input.style.width = Math.min(originalText.length * 8 + 40, 300) + 'px';

            const editContainer = document.createElement('div');
            editContainer.className = 'flex items-center gap-2 w-full justify-end mt-1';

            const saveBtn = document.createElement('button');
            saveBtn.innerHTML = '✅';
            saveBtn.className = 'text-green-500 hover:text-green-700 transition text-lg';
            saveBtn.title = 'Save';

            const cancelBtn = document.createElement('button');
            cancelBtn.innerHTML = '❌';
            cancelBtn.className = 'text-red-400 hover:text-red-600 transition text-lg';
            cancelBtn.title = 'Cancel';
            editContainer.appendChild(input);
            editContainer.appendChild(saveBtn);
            editContainer.appendChild(cancelBtn);
            messageDiv.appendChild(editContainer);
            function cancelEdit() {
                editContainer.remove();
                textP.style.display = '';
                editBtn.style.display = '';
            }
            saveBtn.addEventListener('click', () => {
                const newText = input.value.trim();
                if (newText === '' || newText == originalText) {
                    cancelEdit();
                    return;
                }
                fetch('ajax/edit_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ message_id: msgId, new_message: newText })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const icon = readIconEl;
                            textP.textContent = '';
                            textP.appendChild(document.createTextNode(newText))
                            if (icon) {
                                textP.appendChild(icon);
                            }
                            cancelEdit();
                        } else {
                            alert(data.error || 'Failed to edit message');
                            cancelEdit();
                        }
                    })
                    .catch(() => {
                        alert('Network error');
                        cancelEdit();
                    });
            })
            cancelBtn.addEventListener('click', cancelEdit);
        }
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) {
            const msgId = deleteBtn.dataset.msgId;
            const messageDiv = document.getElementById(`msg-${msgId}`);
            if (!messageDiv) return;

            if (confirm('Are you sure you want to delete this message?')) {
                fetch('ajax/delete_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ message_id: msgId })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            messageDiv.remove();
                        } else {
                            alert(data.error || 'Failed to delete message');
                        }
                    })
                    .catch(() => {
                        alert('Network error');
                    });
            }
        }
    })

});