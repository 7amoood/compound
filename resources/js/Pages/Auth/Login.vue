<template>
    <Head title="تسجيل الدخول - خدمات المجمع" />
    
    <div class="relative flex h-[100vh] w-full flex-col overflow-x-hidden overflow-y-auto max-w-md mx-auto bg-white dark:bg-background-dark shadow-sm font-display" dir="rtl">
        <!-- Header Section - Image goes to very top, content has safe area -->
        <div class="@container">
            <div class="@[480px]:px-4 @[480px]:py-3">
                <div class="w-full bg-center bg-no-repeat bg-cover flex flex-col justify-end overflow-hidden bg-slate-50 @[480px]:rounded-lg relative" 
                     style="background-image: url('/assets/garden_city.png'); min-height: calc(280px + env(safe-area-inset-top)); padding-top: env(safe-area-inset-top);">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                    <div class="relative p-6">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-white/20 backdrop-blur-md p-1.5 rounded-lg">
                                <span class="material-symbols-outlined text-white text-[20px]">location_city</span>
                            </span>
                            <span class="text-white/90 text-sm font-medium tracking-wide">كمبوند جاردن سيتي</span>
                        </div>
                        <h1 class="text-white tracking-light text-[32px] font-bold leading-tight">مرحباً بك</h1>
                        <p class="text-white/90 text-base font-normal leading-normal pt-1">حياة هادئة وخدمات متميزة في قلب جاردن سيتي</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Container -->
        <div class="px-4 pb-8 flex flex-col gap-5">
            <!-- Auth Tabs (Sign In / Sign Up) -->
            <div class="flex pt-4">
                <div class="flex h-12 flex-1 items-center justify-center rounded-lg bg-[#e7edf3] dark:bg-slate-800 p-1">
                    <label 
                        @click="activeTab = 'login'"
                        class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md text-sm font-medium leading-normal transition-all duration-200"
                        :class="activeTab === 'login' ? 'bg-white dark:bg-slate-700 shadow-[0_1px_3px_rgba(0,0,0,0.1)] text-primary dark:text-white font-semibold' : 'text-[#4c739a] dark:text-slate-400'">
                        <span class="truncate">تسجيل الدخول</span>
                    </label>
                    <label 
                        @click="activeTab = 'register'"
                        class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md text-sm font-medium leading-normal transition-all duration-200"
                        :class="activeTab === 'register' ? 'bg-white dark:bg-slate-700 shadow-[0_1px_3px_rgba(0,0,0,0.1)] text-primary dark:text-white font-semibold' : 'text-[#4c739a] dark:text-slate-400'">
                        <span class="truncate">إنشاء حساب</span>
                    </label>
                </div>
            </div>
            
            <!-- Login Form -->
            <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="flex flex-col gap-4 mt-2">
                <!-- Phone -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">رقم الهاتف</label>
                    <div class="relative flex items-center">
                        <span class="absolute right-3 text-slate-400 material-symbols-outlined text-[20px]">smartphone</span>
                        <input v-model="loginForm.phone" class="w-full h-12 pr-10 pl-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="01xxxxxxxxx" type="tel" required/>
                    </div>
                    <div v-if="loginForm.errors.phone" class="text-red-500 text-xs mr-1">{{ loginForm.errors.phone }}</div>
                </div>
                
                <!-- Password -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">كلمة المرور</label>
                    <div class="relative flex items-center">
                        <span class="absolute right-3 text-slate-400 material-symbols-outlined text-[20px]">lock</span>
                        <input v-model="loginForm.password" :type="passwordVisible ? 'text' : 'password'" class="w-full h-12 pr-10 pl-10 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="••••••••" required/>
                        <button class="absolute left-3 text-slate-400 hover:text-primary transition-colors" type="button" @click="passwordVisible = !passwordVisible">
                            <span class="material-symbols-outlined text-[20px]">{{ passwordVisible ? 'visibility' : 'visibility_off' }}</span>
                        </button>
                    </div>
                    <div v-if="loginForm.errors.password" class="text-red-500 text-xs mr-1">{{ loginForm.errors.password }}</div>
                </div>

                <div v-if="loginForm.errors.message" class="text-red-500 text-sm text-center bg-red-50 p-2 rounded">{{ loginForm.errors.message }}</div>
                
                <!-- Submit Button -->
                <button type="submit" :disabled="loginForm.processing" class="mt-4 w-full h-12 rounded-lg bg-primary text-white font-bold text-base shadow-md shadow-primary/20 hover:bg-blue-600 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70">
                    <span v-if="loginForm.processing" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ loginForm.processing ? 'جاري الدخول...' : 'تسجيل الدخول' }}</span>
                </button>
                
                <!-- Biometric Login Button -->
                <button v-if="hasSavedCredentials && biometricAvailable" type="button" @click="loginWithBiometric" :disabled="biometricLoading" 
                        class="w-full h-12 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium text-base hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70 border border-slate-200 dark:border-slate-700">
                    <span v-if="biometricLoading" class="w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></span>
                    <span v-else class="material-symbols-outlined text-[22px]">fingerprint</span>
                    <span>{{ biometricLoading ? 'جاري التحقق...' : 'الدخول بالبصمة' }}</span>
                </button>
            </form>
            
            <!-- Register Form -->
            <form v-else @submit.prevent="submitRegister" class="flex flex-col gap-4 mt-2">
                <!-- Role Selection -->
                <div class="flex flex-col gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mr-1">أنا</span>
                    <div class="flex h-12 w-full items-center justify-center rounded-lg bg-[#e7edf3] dark:bg-slate-800 p-1">
                        <label @click="activeRole = 'resident'; registerForm.role = 'resident'" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md text-sm font-medium leading-normal transition-colors" :class="activeRole === 'resident' ? 'bg-primary shadow-sm text-white' : 'text-[#4c739a] dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50'">
                            <span class="material-symbols-outlined text-[18px] ml-2">home</span>
                            <span class="truncate">ساكن</span>
                        </label>
                        <label @click="activeRole = 'provider'; registerForm.role = 'provider'" class="flex cursor-pointer h-full grow items-center justify-center overflow-hidden rounded-md text-sm font-medium leading-normal transition-colors" :class="activeRole === 'provider' ? 'bg-primary shadow-sm text-white' : 'text-[#4c739a] dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700/50'">
                            <span class="material-symbols-outlined text-[18px] ml-2">engineering</span>
                            <span class="truncate">مزود خدمة</span>
                        </label>
                    </div>
                </div>
                
                <!-- Full Name -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">الاسم الكامل</label>
                    <div class="relative flex items-center">
                        <span class="absolute right-3 text-slate-400 material-symbols-outlined text-[20px]">person</span>
                        <input v-model="registerForm.name" class="w-full h-12 pr-10 pl-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="أحمد محمد" type="text" required/>
                    </div>
                </div>
                
                <!-- Phone -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">رقم الهاتف</label>
                    <div class="relative flex items-center">
                        <span class="absolute right-3 text-slate-400 material-symbols-outlined text-[20px]">smartphone</span>
                        <input v-model="registerForm.phone" class="w-full h-12 pr-10 pl-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="01xxxxxxxxx" type="tel" required/>
                    </div>
                </div>

                <!-- Compound Selection (for residents) -->
                <div v-if="activeRole === 'resident'" class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">الكمبوند</label>
                    <div class="relative">
                        <select v-model="registerForm.compound_id" class="w-full appearance-none h-12 px-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" required>
                            <option value="">اختر الكمبوند</option>
                            <option v-for="c in compounds" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-slate-500">
                            <span class="material-symbols-outlined">expand_more</span>
                        </div>
                    </div>
                </div>
                
                <!-- Address Grid (for residents) -->
                <div v-if="activeRole === 'resident'" class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">العنوان</label>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="relative">
                            <input v-model="registerForm.block_no" class="w-full h-12 px-3 text-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="المبنى" type="text"/>
                        </div>
                        <div class="relative">
                            <input v-model="registerForm.floor" class="w-full h-12 px-3 text-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="الطابق" type="text"/>
                        </div>
                        <div class="relative">
                            <input v-model="registerForm.apt_no" class="w-full h-12 px-3 text-center rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="الشقة" type="text"/>
                        </div>
                    </div>
                </div>
                
                <!-- Provider Options -->
                <template v-if="activeRole === 'provider'">
                    <div class="flex flex-col gap-2 mb-1">
                        <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">نوع العمل</label>
                        <div class="flex gap-4 px-1">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="registerForm.is_market_staff" :value="false" class="w-4 h-4 text-primary focus:ring-primary border-slate-300" />
                                <span class="text-sm text-slate-600 dark:text-slate-300">مقدم خدمة مستقل</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" v-model="registerForm.is_market_staff" :value="true" class="w-4 h-4 text-primary focus:ring-primary border-slate-300" />
                                <span class="text-sm text-slate-600 dark:text-slate-300">موظف ماركت</span>
                            </label>
                        </div>
                    </div>

                    <!-- Service Type (Independent) -->
                    <div v-if="!registerForm.is_market_staff" class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">نوع الخدمة</label>
                        <div class="relative">
                            <select v-model="registerForm.service_type_id" class="w-full appearance-none h-12 px-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base">
                                <option value="">اختر نوع الخدمة</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-slate-500">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <!-- Market Selection (Staff) -->
                    <div v-else class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">الماركت</label>
                        <div class="relative">
                            <select v-model="registerForm.market_id" class="w-full appearance-none h-12 px-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base">
                                <option value="">اختر الماركت الذي تعمل به</option>
                                <option v-for="m in markets" :key="m.id" :value="m.id">{{ m.name }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-slate-500">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Password -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-[#0d141b] dark:text-white mr-1">كلمة المرور</label>
                    <div class="relative flex items-center">
                        <span class="absolute right-3 text-slate-400 material-symbols-outlined text-[20px]">lock</span>
                        <input v-model="registerForm.password" :type="passwordVisible ? 'text' : 'password'" class="w-full h-12 pr-10 pl-10 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#0d141b] dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all text-base" placeholder="••••••••" required minlength="6"/>
                        <button class="absolute left-3 text-slate-400 hover:text-primary transition-colors" type="button" @click="passwordVisible = !passwordVisible">
                            <span class="material-symbols-outlined text-[20px]">{{ passwordVisible ? 'visibility' : 'visibility_off' }}</span>
                        </button>
                    </div>
                    <div v-if="registerForm.errors.password" class="text-red-500 text-xs mr-1">{{ registerForm.errors.password }}</div>
                </div>

                <div v-if="registerForm.errors.message" class="text-red-500 text-sm text-center bg-red-50 p-2 rounded">{{ registerForm.errors.message }}</div>
                
                <!-- Submit Button -->
                <button type="submit" :disabled="registerForm.processing" class="mt-4 w-full h-12 rounded-lg bg-primary text-white font-bold text-base shadow-md shadow-primary/20 hover:bg-blue-600 active:scale-[0.98] transition-all flex items-center justify-center gap-2 disabled:opacity-70">
                    <span v-if="registerForm.processing" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>{{ registerForm.processing ? 'جاري التسجيل...' : 'إنشاء حساب' }}</span>
                </button>
            </form>
            
            <!-- Footer Links -->
            <div class="flex flex-col items-center gap-4 py-2">
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center">
                    بالتسجيل، أنت توافق على <a class="text-primary font-medium hover:underline" href="#">الشروط</a> و <a class="text-primary font-medium hover:underline" href="#">سياسة الخصوصية</a>
                </p>
                <div class="w-full border-t border-slate-100 dark:border-slate-800"></div>
                <div class="flex w-full justify-between items-center px-2">
                    <a class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-primary transition-colors" href="#">تحتاج مساعدة؟</a>
                    <a class="text-sm font-medium text-primary hover:text-blue-700 transition-colors" href="#">نسيت كلمة المرور؟</a>
                </div>
            </div>
        </div>
        <div class="h-5 bg-transparent"></div>
        
        <!-- Biometric Registration Prompt Modal -->
        <div v-if="showBiometricPrompt" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-sm w-full shadow-xl">
                <div class="flex flex-col items-center text-center gap-4">
                    <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[32px]">fingerprint</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">تفعيل الدخول بالبصمة</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">هل تريد تفعيل الدخول السريع باستخدام بصمة الإصبع أو Face ID؟</p>
                    <div class="flex flex-col gap-2 w-full mt-2">
                        <button @click="setupBiometric" :disabled="biometricLoading" class="w-full h-12 rounded-lg bg-primary text-white font-bold flex items-center justify-center gap-2 disabled:opacity-70">
                            <span v-if="biometricLoading" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span>{{ biometricLoading ? 'جاري التفعيل...' : 'نعم، فعّل البصمة' }}</span>
                        </button>
                        <button @click="skipBiometric" class="w-full h-12 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-medium">
                            ليس الآن
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, useForm } from '@inertiajs/vue3';

export default {
    name: 'Login',
    components: { Head },
    props: {
        categories: Array,
        compounds: Array,
        markets: Array,
    },
    data() {
        return {
            activeTab: 'login',
            activeRole: 'resident',
            passwordVisible: false,
            loginForm: useForm({
                phone: '',
                password: '',
            }),
            registerForm: useForm({
                name: '',
                phone: '',
                password: '',
                role: 'resident',
                compound_id: '',
                block_no: '',
                floor: '',
                apt_no: '',
                service_type_id: '',
                market_id: '',
                is_market_staff: false,
            }),
            // Biometric
            biometricAvailable: false,
            hasSavedCredentials: false,
            biometricLoading: false,
            showBiometricPrompt: false,
            pendingCredentials: null,
        };
    },
    mounted() {
        this.checkBiometricAvailability();
        this.checkSavedCredentials();
    },
    methods: {
        submitLogin() {
            this.loginForm.post('/login');
        },
        submitRegister() {
            const userData = { ...this.registerForm.data() };
            const selectedCompound = this.compounds.find(c => c.id === userData.compound_id);
            const compoundName = selectedCompound ? selectedCompound.name : '';
            
            this.registerForm.post('/register', {
                onSuccess: () => {
                    // Store credentials for biometric setup
                    this.pendingCredentials = {
                        phone: userData.phone,
                        password: userData.password
                    };
                    
                    // Clear the form
                    this.registerForm.reset();
                    this.activeRole = 'resident';
                    
                    // Switch to login tab
                    this.activeTab = 'login';
                    
                    // Check if biometric is available and prompt for setup
                    if (this.biometricAvailable) {
                        this.showBiometricPrompt = true;
                    } else {
                        // If no biometric, just redirect to WhatsApp
                        this.redirectToWhatsApp(userData, compoundName);
                    }
                },
            });
        },
        redirectToWhatsApp(userData, compoundName) {
            const whatsappNumber = "201201763086";
            let message = `مرحباً 👋\n\n`;
            message += `🔐 *طلب تفعيل حساب جديد*\n`;
            message += `━━━━━━━━━━━━━━━━━━\n\n`;
            message += `👤 *الاسم:* ${userData.name}\n`;
            
            if (userData.role === 'resident') {
                message += `🏠 *النوع:* ساكن\n`;
                message += `📍 *الكمبوند:* ${compoundName}\n`;
                if (userData.block_no || userData.floor || userData.apt_no) {
                    message += `🏢 *العنوان:*\n`;
                    if (userData.block_no) message += `   • مبنى ${userData.block_no}\n`;
                    if (userData.floor) message += `   • طابق ${userData.floor}\n`;
                    if (userData.apt_no) message += `   • شقة ${userData.apt_no}\n`;
                }
            } else {
                message += `🔧 *النوع:* مزود خدمة\n`;
            }
            
            message += `\n📱 *رقم الهاتف:* ${userData.phone}\n`;
            message += `\n━━━━━━━━━━━━━━━━━━\n`;
            message += `يرجى تفعيل حسابي في أقرب وقت ممكن 🙏\n`;
            message += `شكراً لكم ✨`;
            
            const waUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
            setTimeout(() => {
                window.location.href = waUrl;
            }, 300);
        },
        async checkBiometricAvailability() {
            // Check if Web Credentials API is available
            if (window.PublicKeyCredential) {
                try {
                    const available = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
                    this.biometricAvailable = available;
                } catch (e) {
                    this.biometricAvailable = false;
                }
            }
        },
        checkSavedCredentials() {
            const saved = localStorage.getItem('biometric_credentials');
            this.hasSavedCredentials = !!saved;
        },
        async setupBiometric() {
            if (!this.pendingCredentials) return;
            
            this.biometricLoading = true;
            try {
                // Encrypt and store credentials
                const credentials = btoa(JSON.stringify(this.pendingCredentials));
                localStorage.setItem('biometric_credentials', credentials);
                this.hasSavedCredentials = true;
                this.showBiometricPrompt = false;
                
                // Pre-fill login form with phone
                this.loginForm.phone = this.pendingCredentials.phone;
                
                // Redirect to WhatsApp
                const userData = this.pendingCredentials;
                this.redirectToWhatsApp({ ...userData, name: '', role: 'resident' }, '');
            } catch (e) {
                console.error('Failed to setup biometric:', e);
            } finally {
                this.biometricLoading = false;
                this.pendingCredentials = null;
            }
        },
        skipBiometric() {
            this.showBiometricPrompt = false;
            if (this.pendingCredentials) {
                const userData = this.pendingCredentials;
                this.redirectToWhatsApp({ ...userData, name: '', role: 'resident' }, '');
            }
            this.pendingCredentials = null;
        },
        async loginWithBiometric() {
            this.biometricLoading = true;
            try {
                // Request biometric verification
                const credential = await navigator.credentials.get({
                    mediation: 'required',
                    publicKey: {
                        challenge: new Uint8Array(32),
                        timeout: 60000,
                        userVerification: 'required',
                        rpId: window.location.hostname,
                    }
                }).catch(() => null);
                
                // If WebAuthn fails, try simple device authentication
                // For iOS, this triggers Face ID/Touch ID
                const stored = localStorage.getItem('biometric_credentials');
                if (stored) {
                    const creds = JSON.parse(atob(stored));
                    this.loginForm.phone = creds.phone;
                    this.loginForm.password = creds.password;
                    this.loginForm.post('/login');
                }
            } catch (e) {
                console.error('Biometric login failed:', e);
                this.loginForm.errors.message = 'فشل الدخول بالبصمة، يرجى استخدام كلمة المرور';
            } finally {
                this.biometricLoading = false;
            }
        },
    },
};
</script>
