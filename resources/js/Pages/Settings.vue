<template>
    <Head title="الإعدادات - خدمات المجمع" />
    <Toast />
    
    <div class="relative flex h-[100vh] w-full flex-col mx-auto max-w-md bg-background-light dark:bg-background-dark font-display" dir="rtl">
        <!-- Top App Bar (iOS style) -->
        <header class="sticky top-0 z-50 flex items-center justify-between px-4 py-3 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md border-b border-gray-200 dark:border-gray-800" style="padding-top: max(0.75rem, env(safe-area-inset-top));">
            <Link href="/dashboard" class="flex items-center text-primary text-base font-normal -mr-1">
                <span class="material-symbols-outlined text-2xl">chevron_right</span>
                <span>رجوع</span>
            </Link>
            <h1 class="text-[17px] font-semibold text-center text-[#0d141b] dark:text-white absolute left-1/2 transform -translate-x-1/2">الإعدادات</h1>
            <button @click="saveProfile" :disabled="saving" class="text-primary text-[17px] font-semibold">{{ saving ? '...' : 'حفظ' }}</button>
        </header>

        <div class="flex-1 overflow-y-auto no-scrollbar pb-24 px-0">
        
        <!-- Profile Header -->
        <div class="flex flex-col items-center justify-center pt-8 pb-6 px-4">
            <div class="relative cursor-pointer group" @click="showAvatarGallery = true">
                <div class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 bg-cover bg-center shadow-sm border-2 border-white dark:border-gray-800 transition-transform active:scale-95" :style="`background-image: url('${user.photo || 'https://ui-avatars.com/api/?name=' + user.name}');`"></div>
                <div class="absolute bottom-0 right-0 bg-primary text-white rounded-full p-1.5 shadow-md border-2 border-white dark:border-gray-800 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[16px] leading-none">photo_camera</span>
                </div>
            </div>
            <div class="mt-3 text-center">
                <h2 class="text-xl font-bold text-[#0d141b] dark:text-white">{{ user.name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ user.role === 'resident' ? 'ساكن' : user.role === 'provider' ? 'مقدم خدمة' : 'مسؤول' }} • {{ user.apt_no ? `شقة ${user.apt_no}` : user.phone }}</p>
            </div>
        </div>
        
        <!-- Personal Information Section -->
        <div class="px-4 mb-6">
            <h3 class="pr-4 mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">المعلومات الشخصية</h3>
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm divide-y divide-gray-100 dark:divide-gray-800">
                <!-- Name Field -->
                <div class="flex items-center px-4 py-3 bg-surface-light dark:bg-surface-dark">
                    <label class="w-20 text-[16px] text-gray-900 dark:text-white font-normal">الاسم</label>
                    <input v-model="form.name" class="flex-1 bg-transparent border-none p-0 text-[16px] text-primary text-left focus:ring-0 placeholder:text-gray-400 font-normal" type="text" />
                </div>
                <!-- Phone Field -->
                <div class="flex items-center px-4 py-3 bg-surface-light dark:bg-surface-dark">
                    <label class="w-20 text-[16px] text-gray-900 dark:text-white font-normal">الهاتف</label>
                    <input v-model="form.phone" class="flex-1 bg-transparent border-none p-0 text-[16px] text-primary text-left focus:ring-0 placeholder:text-gray-400 font-normal" type="tel" />
                </div>
            </div>
        </div>



                <!-- Password Section -->
        <div class="px-4 mb-6">
            <h3 class="pr-4 mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">كلمة المرور</h3>
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm divide-y divide-gray-100 dark:divide-gray-800">
                <div class="flex items-center px-4 py-3">
                    <label class="w-28 text-[16px] text-gray-900 dark:text-white font-normal">الحالية</label>
                    <input v-model="passwordForm.current_password" class="flex-1 bg-transparent border-none p-0 text-[16px] text-gray-900 dark:text-white text-left focus:ring-0 placeholder:text-gray-400 font-normal" type="password" placeholder="••••••••" />
                </div>
                <div class="flex items-center px-4 py-3">
                    <label class="w-28 text-[16px] text-gray-900 dark:text-white font-normal">الجديدة</label>
                    <input v-model="passwordForm.new_password" class="flex-1 bg-transparent border-none p-0 text-[16px] text-gray-900 dark:text-white text-left focus:ring-0 placeholder:text-gray-400 font-normal" type="password" placeholder="••••••••" />
                </div>
                <div class="flex items-center px-4 py-3">
                    <label class="w-28 text-[16px] text-gray-900 dark:text-white font-normal">تأكيد الجديدة</label>
                    <input v-model="passwordForm.new_password_confirmation" class="flex-1 bg-transparent border-none p-0 text-[16px] text-gray-900 dark:text-white text-left focus:ring-0 placeholder:text-gray-400 font-normal" type="password" placeholder="••••••••" />
                </div>
            </div>
            <button @click="changePassword" :disabled="changingPassword" class="mt-3 w-full bg-slate-100 dark:bg-slate-800 rounded-xl p-3 text-primary font-medium text-[16px] transition-colors">
                {{ changingPassword ? 'جاري التحديث...' : 'تحديث كلمة المرور' }}
            </button>
        </div>
        
        <!-- Notifications Section -->
        <div class="px-4 mb-6">
            <h3 class="pr-4 mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">الإشعارات</h3>
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm divide-y divide-gray-100 dark:divide-gray-800">
                <!-- Dark Mode Selector -->
                <div class="flex flex-col px-4 py-3 gap-3">
                    <span class="text-[16px] text-gray-900 dark:text-white font-normal">المظهر</span>
                    <div class="grid grid-cols-3 gap-2 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                        <button @click="setTheme('light')" class="flex items-center justify-center py-2 px-3 rounded-lg text-sm font-medium transition-all" :class="theme === 'light' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <span class="material-symbols-outlined text-[18px] ml-1">light_mode</span> فاتح
                        </button>
                        <button @click="setTheme('dark')" class="flex items-center justify-center py-2 px-3 rounded-lg text-sm font-medium transition-all" :class="theme === 'dark' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <span class="material-symbols-outlined text-[18px] ml-1">dark_mode</span> داكن
                        </button>
                        <button @click="setTheme('auto')" class="flex items-center justify-center py-2 px-3 rounded-lg text-sm font-medium transition-all" :class="theme === 'auto' ? 'bg-white dark:bg-gray-700 text-primary shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <span class="material-symbols-outlined text-[18px] ml-1">settings_brightness</span> تلقائي
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Support & Legal Section -->
        <div class="px-4 mb-8">
            <h3 class="pr-4 mb-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">الدعم والقانون</h3>
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl overflow-hidden shadow-sm divide-y divide-gray-100 dark:divide-gray-800">
                <a class="flex items-center justify-between px-4 py-3 active:bg-gray-50 dark:active:bg-gray-800 transition-colors group" href="#">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center text-primary"><span class="material-symbols-outlined text-[18px]">help</span></div>
                        <span class="text-[16px] text-gray-900 dark:text-white font-normal">مركز المساعدة</span>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 text-xl">chevron_left</span>
                </a>
                <a class="flex items-center justify-between px-4 py-3 active:bg-gray-50 dark:active:bg-gray-800 transition-colors group" href="#">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-md bg-blue-500/10 flex items-center justify-center text-blue-500"><span class="material-symbols-outlined text-[18px]">lock</span></div>
                        <span class="text-[16px] text-gray-900 dark:text-white font-normal">سياسة الخصوصية</span>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 text-xl">chevron_left</span>
                </a>
                <a class="flex items-center justify-between px-4 py-3 active:bg-gray-50 dark:active:bg-gray-800 transition-colors group" href="#">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-md bg-purple-500/10 flex items-center justify-center text-purple-500"><span class="material-symbols-outlined text-[18px]">description</span></div>
                        <span class="text-[16px] text-gray-900 dark:text-white font-normal">شروط الخدمة</span>
                    </div>
                    <span class="material-symbols-outlined text-gray-400 text-xl">chevron_left</span>
                </a>
            </div>
        </div>
        
        <!-- Logout Section -->
        <div class="px-4 mb-8">
            <a href="/logout" class="w-full flex items-center justify-center bg-surface-light dark:bg-surface-dark rounded-xl p-3 shadow-sm text-red-500 font-medium text-[16px] active:bg-red-50 dark:active:bg-red-900/20 transition-colors">
                تسجيل الخروج
            </a>
            <p class="text-center text-xs text-gray-400 mt-4">CommunityApp v1.0.0</p>
        </div>

        <!-- Avatar Gallery Modal -->
        <Modal :show="showAvatarGallery" title="اختر صورة البروفيل" @close="showAvatarGallery = false">
            <div class="grid grid-cols-3 gap-4 p-2">
                <button v-for="avatar in avatars" :key="avatar" @click="selectAvatar(avatar)" :disabled="selectingAvatar" 
                        class="relative aspect-square rounded-2xl overflow-hidden border-2 transition-all active:scale-95"
                        :class="user.photo?.endsWith(avatar) ? 'border-primary ring-4 ring-primary/20' : 'border-slate-100 dark:border-slate-700'">
                    <img :src="avatar" class="w-full h-full object-cover" />
                    <div v-if="user.photo?.endsWith(avatar)" class="absolute inset-0 bg-primary/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white text-2xl font-bold">check_circle</span>
                    </div>
                    <div v-if="selectingAvatar && nextAvatar === avatar" class="absolute inset-0 bg-black/20 flex items-center justify-center">
                        <span class="material-symbols-outlined text-white animate-spin">progress_activity</span>
                    </div>
                </button>
            </div>
            <p class="text-center text-xs text-slate-400 mt-6 pb-2">اختر الصورة التي تعبر عنك وسيتم تحديثها فوراً</p>
        </Modal>
    </div>
</div>
</template>

<script>
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';
import Modal from '@/Components/Modal.vue';

export default {
    name: 'Settings',
    components: { Head, Link, Toast, Modal },
    data() {
        return {
            form: { name: '', phone: '' },
            passwordForm: { current_password: '', new_password: '', new_password_confirmation: '' },
            saving: false,
            changingPassword: false,
            theme: 'auto',
            avatars: [
                '/avatars/man_1.png', '/avatars/woman_1.png',
                '/avatars/man_2.png', '/avatars/woman_2.png',
                '/avatars/man_3.png', '/avatars/woman_3.png',
                '/avatars/boy.png', '/avatars/girl.png'
            ],
            selectingAvatar: false,
            showAvatarGallery: false,
            nextAvatar: null,
        };
    },
    computed: {
        user() {
            const page = usePage();
            return page.props.auth ? page.props.auth.user : {};
        },
        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        }
    },
    watch: {
        user: {
            handler(newUser) {
                if (newUser) {
                    this.form.name = newUser.name || '';
                    this.form.phone = newUser.phone || '';
                }
            },
            immediate: true,
            deep: true
        }
    },
    mounted() {
        if (localStorage.theme === 'dark') {
            this.theme = 'dark';
        } else if (localStorage.theme === 'light') {
            this.theme = 'light';
        } else {
            this.theme = 'auto';
        }
    },
    methods: {
        async saveProfile() {
            this.saving = true;
            try {
                // Update profile
                const response = await axios.put('/api/settings/profile', { name: this.form.name, phone: this.form.phone });
                if (response.data.success) {
                    window.showToast('تم حفظ البيانات بنجاح', 'success');
                }
            } catch (e) {
                window.showToast('حدث خطأ أثناء الحفظ', 'error');
            } finally {
                this.saving = false;
            }
        },
        async selectAvatar(avatarPath) {
            this.selectingAvatar = true;
            this.nextAvatar = avatarPath;
            try {
                const response = await axios.post('api/settings/avatar', { avatar: avatarPath });
                if (response.data.success) {
                    window.showToast('تم تحديث الصورة الشخصية', 'success');
                    // Force refresh user data from page props
                    this.$inertia.reload({ only: ['auth'] });
                    setTimeout(() => {
                        this.showAvatarGallery = false;
                    }, 500);
                }
            } catch (e) {
                window.showToast('حدث خطأ أثناء تحديث الصورة', 'error');
            } finally {
                this.selectingAvatar = false;
                this.nextAvatar = null;
            }
        },
        async changePassword() {
            if (!this.passwordForm.current_password || !this.passwordForm.new_password || !this.passwordForm.new_password_confirmation) {
                window.showToast('يرجى ملء جميع الحقول', 'error');
                return;
            }
            if (this.passwordForm.new_password !== this.passwordForm.new_password_confirmation) {
                window.showToast('كلمة المرور الجديدة غير متطابقة', 'error');
                return;
            }
            this.changingPassword = true;
            try {
                const response = await axios.put('/api/settings/password', this.passwordForm);
                if (response.data.success) {
                    window.showToast('تم تحديث كلمة المرور بنجاح', 'success');
                    this.passwordForm = { current_password: '', new_password: '', new_password_confirmation: '' };
                } else {
                    window.showToast(response.data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                window.showToast(e.response?.data?.message || 'حدث خطأ', 'error');
            } finally {
                this.changingPassword = false;
            }
        },
        setTheme(mode) {
            this.theme = mode;
            if (mode === 'dark') {
                localStorage.theme = 'dark';
                document.documentElement.classList.add('dark');
            } else if (mode === 'light') {
                localStorage.theme = 'light';
                document.documentElement.classList.remove('dark');
            } else {
                localStorage.removeItem('theme');
                if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }
        },
    },
};
</script>

<style scoped>
/* No component-specific styles needed for the new theme selector as it uses Tailwind utility classes */
</style>
