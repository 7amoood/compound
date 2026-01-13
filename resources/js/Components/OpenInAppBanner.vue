<template>
    <!-- Smart App Banner - Shows when user is in browser but PWA is installed -->
    <transition name="slide-down">
        <div v-if="showBanner" 
             class="fixed top-0 left-0 right-0 z-[9999] bg-gradient-to-r from-primary to-blue-600 text-white px-4 py-3 shadow-lg font-display"
             dir="rtl"
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
                    <button @click="showInstructions" class="bg-white text-primary px-4 py-2 rounded-lg text-sm font-bold shadow-sm active:scale-95 transition-transform">
                        فتح
                    </button>
                    <button @click="dismiss" class="p-2 hover:bg-white/10 rounded-full transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            </div>
        </div>
    </transition>
    
    <!-- Instructions Modal -->
    <transition name="fade">
        <div v-if="showModal" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 font-display" dir="rtl">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
            <div class="relative bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                <button @click="closeModal" class="absolute top-4 left-4 text-slate-400 hover:text-slate-600">
                    <span class="material-symbols-outlined">close</span>
                </button>
                
                <div class="text-center mb-6">
                    <div class="size-16 rounded-2xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                        <span class="material-symbols-outlined text-primary text-[32px]">phone_iphone</span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">افتح من التطبيق</h3>
                </div>
                
                <div class="space-y-4 text-sm text-slate-600 dark:text-slate-300">
                    <div v-if="isIOS" class="space-y-3">
                        <p class="font-bold text-slate-900 dark:text-white">إذا كان التطبيق مثبتاً:</p>
                        <div class="flex items-start gap-3 bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                            <span class="material-symbols-outlined text-primary">touch_app</span>
                            <span>اضغط على أيقونة التطبيق من الشاشة الرئيسية</span>
                        </div>
                        
                        <p class="font-bold text-slate-900 dark:text-white pt-2">إذا لم يكن مثبتاً:</p>
                        <div class="flex items-start gap-3 bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                            <span class="material-symbols-outlined text-primary">ios_share</span>
                            <span>اضغط على زر المشاركة ⬆️ ثم "إضافة إلى الشاشة الرئيسية"</span>
                        </div>
                    </div>
                    
                    <div v-else class="space-y-3">
                        <p class="font-bold text-slate-900 dark:text-white">إذا كان التطبيق مثبتاً:</p>
                        <div class="flex items-start gap-3 bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                            <span class="material-symbols-outlined text-primary">touch_app</span>
                            <span>اضغط على أيقونة التطبيق من الشاشة الرئيسية</span>
                        </div>
                        
                        <p class="font-bold text-slate-900 dark:text-white pt-2">إذا لم يكن مثبتاً:</p>
                        <div class="flex items-start gap-3 bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                            <span class="material-symbols-outlined text-primary">more_vert</span>
                            <span>اضغط على قائمة المتصفح ⋮ ثم "تثبيت التطبيق" أو "إضافة إلى الشاشة الرئيسية"</span>
                        </div>
                    </div>
                </div>
                
                <button @click="closeModal" class="w-full mt-6 py-3 bg-primary text-white font-bold rounded-xl active:scale-[0.98] transition-transform">
                    حسناً، فهمت
                </button>
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
            showModal: false,
            isStandalone: false,
            isIOS: false,
        };
    },
    mounted() {
        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        this.checkContext();
    },
    methods: {
        checkContext() {
            // Check if running as installed PWA
            this.isStandalone = window.matchMedia('(display-mode: standalone)').matches ||
                                window.navigator.standalone === true ||
                                document.referrer.includes('android-app://');

            // If in browser (not standalone)
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
        showInstructions() {
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
            this.dismiss();
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
