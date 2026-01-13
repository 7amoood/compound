<template>
    <transition name="notif">
        <div v-if="notification" 
             @click="handleClick"
             class="fixed left-4 right-4 z-[110] bg-white dark:bg-slate-800 shadow-2xl rounded-2xl p-4 border border-slate-100 dark:border-slate-700 flex items-start gap-4 cursor-pointer active:scale-95 transition-transform max-w-md mx-auto pointer-events-auto"
             style="top: max(1rem, calc(env(safe-area-inset-top) + 0.5rem));"
             dir="rtl">
            <div class="size-12 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-primary text-2xl">{{ icon }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ title }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ body }}</p>
                <div class="mt-2 flex items-center text-[10px] font-bold text-primary uppercase tracking-wider">
                    <span>اضغط للتفاصيل</span>
                    <span class="material-symbols-outlined text-[14px] mr-1">chevron_left</span>
                </div>
            </div>
            <button @click.stop="notification = null" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    </transition>
</template>

<script>
import { router, usePage } from '@inertiajs/vue3';

export default {
    name: 'InAppNotification',
    data() {
        return {
            notification: null,
            timeout: null,
            audio: null,
            audioUnlocked: false
        };
    },
    computed: {
        title() {
            return this.notification?.data?.title || 'إشعار جديد';
        },
        body() {
            return this.notification?.data?.body || '';
        },
        icon() {
            // Map types to icons if available in data
            const type = this.notification?.data?.type;
            const iconMap = {
                'NEW_REQUEST': 'potted_plant',
                'NEW_PROPOSAL': 'local_offer',
                'PROPOSAL_ACCEPTED': 'check_circle',
                'WORK_COMPLETED': 'verified',
                'NEW_REVIEW': 'star',
                'new_help_request': 'volunteer_activism',
                'help_picked': 'emoji_people',
                'help_comment': 'chat'
            };
            return iconMap[type] || 'notifications';
        }
    },
    mounted() {
        window.addEventListener('fcm-message', this.handleMessage);
        
        // iOS requires user interaction to unlock audio
        const unlock = () => {
            this.initAudio();
            window.removeEventListener('click', unlock);
            window.removeEventListener('touchstart', unlock);
        };
        window.addEventListener('click', unlock);
        window.addEventListener('touchstart', unlock);
    },
    beforeUnmount() {
        window.removeEventListener('fcm-message', this.handleMessage);
    },
    methods: {
        initAudio() {
            if (this.audioUnlocked) return;
            
            this.audio = new Audio('/sounds/notification.wav');
            this.audio.volume = 0;
            
            // Play and immediately pause to unlock
            this.audio.play().then(() => {
                this.audio.pause();
                this.audio.currentTime = 0;
                this.audio.volume = 0.5;
                this.audioUnlocked = true;
                console.log('Audio unlocked for iOS');
            }).catch(e => {
                console.warn('Audio unlock failed:', e);
            });
        },
        handleMessage(event) {
            const payload = event.detail;

            // Skip if sender is self
            const currentUserId = usePage().props.auth?.user?.id;
            if (payload?.data?.sender_id && currentUserId && payload.data.sender_id == currentUserId) {
                return;
            }

            this.notification = payload;
            
            // Auto hide after 6 seconds
            if (this.timeout) clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.notification = null;
            }, 6000);
            
            // Play notification sound
            if (this.audio && this.audioUnlocked) {
                this.audio.currentTime = 0;
                this.audio.play().catch(e => console.warn('Sound play failed:', e));
            } else {
                // Fallback for browsers that don't need unlocking
                const audio = new Audio('/sounds/notification.wav');
                audio.volume = 0.5;
                audio.play().catch(() => {});
            }
        },
        handleClick() {
            const data = this.notification?.data;
            const rid = data?.help_request_id || data?.request_id;

            if (rid) {
                const isHelpType = data.type === 'new_help_request' || data.type === 'help_picked' || data.type === 'help_comment';
                
                if (isHelpType) {
                    // If we are already on the help page, just trigger the modal
                    if (window.location.pathname === '/help') {
                        window.dispatchEvent(new CustomEvent('open-help-details', {
                            detail: { requestId: rid }
                        }));
                    } else {
                        router.visit(`/help?request_id=${rid}`);
                    }
                } else {
                    // Default behavior for other requests (e.g. maintenance)
                    window.dispatchEvent(new CustomEvent('open-request-details', {
                        detail: { requestId: parseInt(rid) }
                    }));
                }
            }
            this.notification = null;
        }
    }
}
</script>

<style scoped>
.notif-enter-active,
.notif-leave-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.notif-enter-from,
.notif-leave-to {
    transform: translateY(-100px);
    opacity: 0;
}

div {
    font-family: 'Cairo', sans-serif !important;
}
</style>
