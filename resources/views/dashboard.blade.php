<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Real-Time Chat Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="chatApp()" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg flex flex-col md:flex-row h-[80vh]">

                <!-- Users List Sidebar -->
                <div class="w-full md:w-1/3 border-r border-gray-200 dark:border-gray-700 overflow-y-auto bg-white dark:bg-gray-800">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex justify-between items-center sticky top-0 z-10">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white uppercase tracking-wider">Контакти</h3>
                    </div>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($users as $user)
                        <li class="p-4 hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer flex justify-between items-center transition-all duration-200 group"
                            :class="{'bg-blue-100 dark:bg-gray-700 border-l-4 border-blue-600': activeUserId === {{ $user->id }}}"
                            @click="selectUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold shadow-sm group-hover:scale-110 transition-transform">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 italic">{{ $user->roles->pluck('name')->first() ?: 'user' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @role('admin')
                                <a href="{{ route('impersonate', $user->id) }}"
                                   title="Login as {{ $user->name }}"
                                   class="p-2 text-purple-600 hover:bg-purple-100 rounded-full transition-colors"
                                   @click.stop>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                                </a>
                                @endrole
                            </div>
                        </li>
                        @empty
                        <li class="p-8 text-center text-gray-500 dark:text-gray-400">
                            Користувачів не знайдено
                        </li>
                        @endforelse
                    </ul>
                </div>

                <!-- Chat Area -->
                <div class="w-full md:w-2/3 flex flex-col bg-gray-50 dark:bg-gray-900 relative">
                    <!-- Default state (no user selected) -->
                    <div x-show="!activeUserId" class="flex-1 flex flex-col items-center justify-center text-gray-400 space-y-4">
                        <svg class="w-16 h-16 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        <p class="text-lg">Виберіть користувача для початку спілкування</p>
                    </div>

                    <!-- Chat Interface -->
                    <div x-show="activeUserId" class="flex-1 flex flex-col h-full overflow-hidden" style="display: none;">

                        <!-- Chat Header -->
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold" x-text="activeUserName ? activeUserName.charAt(0) : ''"></div>
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white" x-text="activeUserName"></h3>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Online</span>
                            </div>
                        </div>

                        <!-- Messages Container -->
                        <div id="messages-container" class="flex-1 p-6 overflow-y-auto space-y-4 scroll-smooth bg-gray-50 dark:bg-gray-900">

                            <!-- Loading State -->
                            <div x-show="isLoading" class="flex justify-center py-4">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                            </div>

                            <!-- Empty Messages -->
                            <div x-show="!isLoading && messages.length === 0" class="text-center py-10 text-gray-400">
                                <p>Ще немає повідомлень. Напишіть щось першим!</p>
                            </div>

                            <!-- Messages Loop via Alpine -->
                            <template x-for="(msg, index) in messages" :key="msg.id || index">
                                <div x-show="msg" class="flex flex-col" :class="isMe(msg) ? 'items-end' : 'items-start'">
                                    <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm shadow-sm transition-all"
                                         :class="isMe(msg) ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 rounded-bl-none border border-gray-100 dark:border-gray-700'">
                                        <p x-text="msg.text" class="whitespace-pre-wrap break-words leading-relaxed"></p>
                                    </div>
                                    <span class="text-[10px] mt-1 px-1 text-gray-400" x-text="msg.created_at_human"></span>
                                </div>
                            </template>

                        </div>

                        <!-- Input Area -->
                        <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                            <form @submit.prevent="sendMessage" class="flex items-center space-x-2">
                                <div class="relative flex-1">
                                    <input type="text" x-model="newMessage" x-ref="messageInput" required
                                        @keydown.enter.prevent="sendMessage"
                                        class="w-full bg-gray-100 dark:bg-gray-700 border-none text-gray-900 dark:text-white text-sm rounded-full focus:ring-2 focus:ring-blue-500 block p-3 pr-10 transition-all"
                                        placeholder="Напишіть повідомлення..." :disabled="isSending">
                                </div>
                                <button type="submit" :disabled="isSending || !newMessage.trim()"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 hover:bg-blue-700 text-white shadow-md disabled:opacity-50 transition-all hover:scale-110 active:scale-95">
                                    <template x-if="!isSending">
                                        <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                                    </template>
                                    <template x-if="isSending">
                                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </template>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chatApp', () => ({
                currentUserId: {{ auth()->id() }},
                activeUserId: null,
                activeUserName: '',
                messages: [],
                newMessage: '',
                isSending: false,
                isLoading: false,

                init() {
                    // Initialize Laravel Echo listener on the current user's private channel
                    if (window.Echo) {
                        window.Echo.private(`user.${this.currentUserId}`)
                            .listen('MessageSent', (e) => {
                                if (!e.message) return;

                                const isFromActiveUser = this.activeUserId === e.message.sender_id;
                                const isToActiveUser = this.activeUserId === e.message.receiver_id;

                                // If the message is part of the current conversation
                                if (isFromActiveUser || isToActiveUser) {
                                    // Prevent duplicates
                                    if (!this.messages.find(m => m.id === e.message.id)) {
                                        this.messages.push(e.message);
                                        this.scrollToBottom();

                                        if (isFromActiveUser) {
                                            this.notify(e.message);
                                        }
                                    }
                                } else {
                                    // Notification for message from someone else
                                    this.notify(e.message);
                                }
                            });
                    }
                },

                notify(message) {
                    if (window.Notyf) {
                        window.Notyf.success({
                            message: `<b>${message.sender_name}</b>: ${message.text}`,
                            ripple: true,
                            dismissible: true
                        });
                    }
                },

                selectUser(id, name) {
                    if (this.activeUserId === id) return;
                    this.activeUserId = id;
                    this.activeUserName = name;
                    this.messages = [];
                    this.fetchMessages();
                    this.$nextTick(() => this.$refs.messageInput?.focus());
                },

                fetchMessages() {
                    if (!this.activeUserId) return;

                    this.isLoading = true;
                    axios.get(`/messages/${this.activeUserId}`)
                        .then(response => {
                            this.messages = Array.isArray(response.data.data) ? response.data.data : [];
                            this.scrollToBottom();
                        })
                        .catch(error => {
                            console.error('Error fetching messages:', error);
                            this.messages = [];
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                },

                sendMessage() {
                    const text = this.newMessage.trim();
                    if (!text || !this.activeUserId || this.isSending) return;

                    this.newMessage = '';
                    this.isSending = true;

                    axios.post('/messages', {
                        receiver_id: this.activeUserId,
                        text: text
                    })
                    .then(response => {
                        const newMsg = response.data.data;
                        if (!this.messages.find(m => m.id === newMsg.id)) {
                            this.messages.push(newMsg);
                            this.scrollToBottom();
                        }
                    })
                    .catch(error => {
                        console.error('Error sending message:', error);
                        this.newMessage = text;
                        if (window.Notyf) window.Notyf.error('Не вдалося надіслати повідомлення.');
                    })
                    .finally(() => {
                        this.isSending = false;
                        this.$nextTick(() => this.$refs.messageInput?.focus());
                    });
                },

                isMe(message) {
                    return message.sender_id === this.currentUserId;
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            const container = document.getElementById('messages-container');
                            if (container) {
                                container.scrollTo({
                                    top: container.scrollHeight,
                                    behavior: 'smooth'
                                });
                            }
                        }, 100);
                    });
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>
