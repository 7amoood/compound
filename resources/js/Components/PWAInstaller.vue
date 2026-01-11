<template>
    <div v-if="showOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/90 backdrop-blur-md p-6 overflow-hidden">
        <div class="relative w-full max-w-sm bg-white dark:bg-slate-800 rounded-3xl p-8 flex flex-col items-center text-center shadow-2xl animate-in fade-in zoom-in duration-300">
            <!-- App Icon -->
            <div class="w-24 h-24 bg-primary/10 rounded-2xl flex items-center justify-center mb-6 shadow-inner tracking-tighter">
                 <img src="/icons/icon-192x192.png" class="w-16 h-16 object-contain" alt="App Logo" />
            </div>

            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">ثبّت التطبيق الآن</h2>
            <p class="text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                للحصول على أفضل تجربة وأسرع وصول لخدمات المجمع، يرجى إضافة التطبيق إلى شاشتك الرئيسية.
            </p>

            <button 
                @click="installApp" 
                class="w-full bg-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-primary/30 hover:bg-blue-600 active:scale-95 transition-all mb-4 flex items-center justify-center gap-2"
            >
                <span class="material-symbols-outlined">download</span>
                <span>تثبيت الآن</span>
            </button>

            <button 
                v-if="!isIOS"
                @click="showOverlay = false" 
                class="text-slate-400 dark:text-slate-500 text-sm font-medium hover:text-slate-600 dark:hover:text-slate-400 transition-colors"
            >
                سأقوم بذلك لاحقاً
            </button>

            <!-- iOS Instructions -->
            <div v-if="isIOS" class="mt-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-600">
                <p class="text-xs text-slate-600 dark:text-slate-300 flex flex-col items-center gap-2">
                    <span>لأجهزة iOS، اضغط على زر <span class="material-symbols-outlined text-[18px] align-middle">ios_share</span> ثم "إضافة للشاشة الرئيسية"</span>
                </p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            deferredPrompt: null,
            showOverlay: false,
            isIOS: false
        }
    },
    mounted() {
        // Check if iOS
        this.isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;

        // Check if already installed
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
        
        if (!isStandalone) {
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent Chrome 67 and earlier from automatically showing the prompt
                e.preventDefault();
                // Stash the event so it can be triggered later.
                this.deferredPrompt = e;
                // Show the overlay
                this.showOverlay = true;
            });

            // For iOS, show overlay if not standalone
            if (this.isIOS) {
                this.showOverlay = true;
            }
        }
    },
    methods: {
        async installApp() {
            if (!this.deferredPrompt) {
                if (this.isIOS) {
                    window.showToast('اتبع تعليمات الإضافة في الأسفل', 'info');
                }
                return;
            }
            // Show the prompt
            this.deferredPrompt.prompt();
            // Wait for the user to respond to the prompt
            const { outcome } = await this.deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
                this.showOverlay = false;
            }
            this.deferredPrompt = null;
        }
    }
}
</script>
