<template>
    <!-- Smart App Banner - Shows when user is in browser but PWA is installed -->
    <transition name="slide-down">
        <div v-if="showBanner" 
             class="fixed top-0 left-0 right-0 z-[9999] bg-gradient-to-r from-primary to-blue-600 text-white px-4 py-3 shadow-lg"
             style="padding-top: max(env(safe-area-inset-top), 12px);">
            <div class="max-w-md mx-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-xl bg-white/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">phone_iphone</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold">افتح التطبيق</p>
                        <p class="text-xs opacity-80">تجربة أفضل وأسرع</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openInApp" class="bg-white text-primary px-4 py-2 rounded-lg text-sm font-bold shadow-sm active:scale-95 transition-transform">
                        فتح
                    </button>
                    <button @click="dismiss" class="p-2 hover:bg-white/10 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'OpenInAppBanner',
    data() {
        return {
            showBanner: false,
            isStandalone: false,
        };
    },
    mounted() {
        this.checkContext();
    },
    methods: {
        checkContext() {
            // Check if running as installed PWA
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                                window.navigator.standalone === true ||
                                document.referrer.includes('android-app://');

            // If in browser (not standalone) and PWA might be installed
            if (!this.isStandalone) {
                // Check if user dismissed recently
                const dismissed = sessionStorage.getItem('app_banner_dismissed');
                if (!dismissed) {
                    // Show banner after a short delay
                    setTimeout(() => {
                        this.showBanner = true;
                    }, 2000);
                }
            }
        },
        openInApp() {
            // Try to open the current URL - if PWA is installed and handles links, it will open
            // Use a special URL scheme or intent for Android
            const currentUrl = window.location.href;
            
            // For Android, try intent URL
            if (/android/i.test(navigator.userAgent)) {
                const intentUrl = `intent://${window.location.host}${window.location.pathname}${window.location.search}#Intent;scheme=https;package=org.chromium.webapk.${this.getWebAPKId()};end`;
                
                // Try opening via intent, fallback to regular URL
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = intentUrl;
                document.body.appendChild(iframe);
                
                setTimeout(() => {
                    document.body.removeChild(iframe);
                }, 2000);
            }
            
            // Show instruction to user
            this.showInstruction();
        },
        showInstruction() {
            // Create a modal with instructions
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            const isAndroid = /android/i.test(navigator.userAgent);
            
            let message = '';
            if (isIOS) {
                message = 'لفتح التطبيق:\n1. اضغط على أيقونة التطبيق من الشاشة الرئيسية\n\nأو إذا لم تثبته بعد:\n1. اضغط على زر المشاركة ⬆️\n2. اختر "إضافة إلى الشاشة الرئيسية"';
            } else if (isAndroid) {
                message = 'لفتح التطبيق:\n1. اضغط على أيقونة التطبيق من الشاشة الرئيسية\n\nأو إذا لم تثبته بعد:\n1. اضغط على قائمة المتصفح ⋮\n2. اختر "إضافة إلى الشاشة الرئيسية" أو "تثبيت التطبيق"';
            }
            
            if (message) {
                alert(message);
            }
            
            this.dismiss();
        },
        getWebAPKId() {
            // Generate a simple hash for the domain (not accurate but can trigger intent)
            const domain = window.location.host;
            let hash = 0;
            for (let i = 0; i < domain.length; i++) {
                hash = ((hash << 5) - hash) + domain.charCodeAt(i);
                hash |= 0;
            }
            return Math.abs(hash).toString(16);
        },
        dismiss() {
            this.showBanner = false;
            sessionStorage.setItem('app_banner_dismissed', 'true');
        }
    }
};
</script>

<style scoped>
.slide-down-enter-active,
.slide-down-leave-active {
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
    transform: translateY(-100%);
    opacity: 0;
}
</style>
