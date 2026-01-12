<template>
    <transition name="notif">
        <div v-if="notification" 
             @click="handleClick"
             class="fixed top-4 left-4 right-4 z-[110] bg-white dark:bg-slate-800 shadow-2xl rounded-2xl p-4 border border-slate-100 dark:border-slate-700 flex items-start gap-4 cursor-pointer active:scale-95 transition-transform max-w-md mx-auto pointer-events-auto"
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
export default {
    name: 'InAppNotification',
    data() {
        return {
            notification: null,
            timeout: null
        };
    },
    computed: {
        title() {
            return this.notification?.notification?.title || 'إشعار جديد';
        },
        body() {
            return this.notification?.notification?.body || '';
        },
        icon() {
            // Map types to icons if available in data
            const type = this.notification?.data?.type;
            const iconMap = {
                'NEW_REQUEST': 'potted_plant',
                'NEW_PROPOSAL': 'local_offer',
                'PROPOSAL_ACCEPTED': 'check_circle',
                'WORK_COMPLETED': 'verified',
                'NEW_REVIEW': 'star'
            };
            return iconMap[type] || 'notifications';
        }
    },
    mounted() {
        window.addEventListener('fcm-message', this.handleMessage);
    },
    beforeUnmount() {
        window.removeEventListener('fcm-message', this.handleMessage);
    },
    methods: {
        handleMessage(event) {
            this.notification = event.detail;
            
            // Auto hide after 6 seconds
            if (this.timeout) clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.notification = null;
            }, 6000);
            
            // Play a subtle sound if desired (optional)
            // try { new Audio('/notif.mp3').play(); } catch(e) {}
        },
        handleClick() {
            const requestId = this.notification?.data?.request_id;
            if (requestId) {
                // Dispatch the event that dashboards listen to
                window.dispatchEvent(new CustomEvent('open-request-details', {
                    detail: { requestId: parseInt(requestId) }
                }));
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
</style>
