<template>
    <Head title="لوحة التحكم - المسؤول" />
    <Toast />

    <div class="bg-background-light dark:bg-background-dark font-display min-h-screen flex flex-col antialiased selection:bg-primary/30 selection:text-primary" dir="rtl">
        <!-- Top App Bar -->
        <header class="sticky top-0 z-50 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between px-4 py-3 max-w-md mx-auto">
                <div class="flex items-center gap-3">
                    <div class="bg-center bg-no-repeat bg-cover rounded-full size-9 border border-slate-200 dark:border-slate-700 shadow-sm" :style="`background-image: url('${user.photo || 'https://ui-avatars.com/api/?name=Admin'}');`"></div>
                    <h1 class="text-text-primary-light dark:text-text-primary-dark text-lg font-bold leading-tight tracking-tight">لوحة المسؤول</h1>
                </div>
                <form method="POST" action="/logout" class="m-0">
                    <input type="hidden" name="_token" :value="csrfToken" />
                    <button type="submit" class="flex items-center justify-center size-10 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-red-500">
                        <span class="material-symbols-outlined">logout</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 pb-24 overflow-x-hidden max-w-md mx-auto w-full">
            <!-- Headline -->
            <section class="px-4 pt-6 pb-2">
                <h2 class="text-text-primary-light dark:text-text-primary-dark text-2xl font-bold leading-tight">صباح الخير، أدمن.</h2>
                <p class="text-text-secondary-light dark:text-text-secondary-dark text-sm mt-1">هذا ما يحدث اليوم في مجتمعك.</p>
            </section>

            <!-- Stats Overview (Grid for mobile) -->
            <section class="px-4 py-4">
                <div class="grid grid-cols-2 gap-3">
                    <!-- Card 1: Inactive Users -->
                    <div @click="activeSection = 'users'; userFilter = 'inactive'" class="relative flex flex-col justify-between gap-3 rounded-xl p-4 bg-white dark:bg-surface-dark shadow-sm border-l-4 border-l-rose-500 border-y border-r border-slate-100 dark:border-slate-800 cursor-pointer hover:shadow-md transition-shadow active:scale-[0.98]">
                        <div class="flex justify-between items-start">
                            <div class="p-2 bg-rose-50 dark:bg-rose-900/20 rounded-lg text-rose-500">
                                <span class="material-symbols-outlined text-xl">person_off</span>
                            </div>
                            <!-- Pulse Indicator -->
                            <span v-if="stats.pending_users > 0" class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                            </span>
                        </div>
                        <div>
                            <p class="text-text-secondary-light dark:text-text-secondary-dark text-xs font-medium">مستخدمين غير مفعلين</p>
                            <p class="text-text-primary-light dark:text-text-primary-dark text-2xl font-bold mt-0.5">{{ stats.pending_users || 0 }}</p>
                        </div>
                    </div>

                    <!-- Card 2: Pending Requests -->
                    <div @click="activeSection = 'requests'; requestFilter = 'pending'" class="relative flex flex-col justify-between gap-3 rounded-xl p-4 bg-white dark:bg-surface-dark shadow-sm border-l-4 border-l-amber-500 border-y border-r border-slate-100 dark:border-slate-800 cursor-pointer hover:shadow-md transition-shadow active:scale-[0.98]">
                        <div class="flex justify-between items-start">
                            <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-amber-500">
                                <span class="material-symbols-outlined text-xl">hourglass_empty</span>
                            </div>
                            <!-- Pulse Indicator -->
                            <span v-if="stats.pending_requests > 0" class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                            </span>
                        </div>
                        <div>
                            <p class="text-text-secondary-light dark:text-text-secondary-dark text-xs font-medium">طلبات قيد الانتظار</p>
                            <p class="text-text-primary-light dark:text-text-primary-dark text-2xl font-bold mt-0.5">{{ stats.pending_requests || 0 }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tab Navigation -->
            <section class="px-4 py-2">
                <div class="flex w-full items-center justify-between gap-2 overflow-x-auto no-scrollbar py-2">
                    <button @click="activeSection = 'users'" class="flex flex-col items-center justify-center min-w-[80px] p-3 rounded-2xl transition-all duration-200" :class="activeSection === 'users' ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-105' : 'bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'">
                        <span class="material-symbols-outlined text-2xl mb-1">group</span>
                        <span class="text-xs font-bold">المستخدمين</span>
                    </button>
                    <button @click="activeSection = 'requests'" class="flex flex-col items-center justify-center min-w-[80px] p-3 rounded-2xl transition-all duration-200" :class="activeSection === 'requests' ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-105' : 'bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'">
                        <span class="material-symbols-outlined text-2xl mb-1">assignment</span>
                        <span class="text-xs font-bold">الطلبات</span>
                    </button>
                    <button @click="activeSection = 'services'" class="flex flex-col items-center justify-center min-w-[80px] p-3 rounded-2xl transition-all duration-200" :class="activeSection === 'services' ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-105' : 'bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'">
                        <span class="material-symbols-outlined text-2xl mb-1">design_services</span>
                        <span class="text-xs font-bold">الخدمات</span>
                    </button>
                    <button @click="activeSection = 'compounds'" class="flex flex-col items-center justify-center min-w-[80px] p-3 rounded-2xl transition-all duration-200" :class="activeSection === 'compounds' ? 'bg-primary text-white shadow-lg shadow-primary/30 scale-105' : 'bg-white dark:bg-slate-800 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700'">
                        <span class="material-symbols-outlined text-2xl mb-1">apartment</span>
                        <span class="text-xs font-bold">الكمبوندات</span>
                    </button>
                </div>
            </section>

            <!-- Search Bar -->
            <section class="px-4 py-3">
                <div class="relative flex items-center w-full h-12 rounded-xl focus-within:ring-2 focus-within:ring-primary/20 transition-shadow bg-white dark:bg-surface-dark shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="grid place-items-center h-full w-12 text-slate-400"><span class="material-symbols-outlined">search</span></div>
                    <input v-model="searchQuery" class="peer h-full w-full outline-none text-sm text-text-primary-light dark:text-text-primary-dark pl-2 bg-transparent placeholder-slate-400" :placeholder="activeSection === 'users' ? 'بحث عن مستخدمين...' : activeSection === 'requests' ? 'بحث عن طلبات...' : 'بحث عن خدمات...'" type="text" />
                </div>
            </section>

            <!-- Users Section -->
            <section v-if="activeSection === 'users'" class="px-4 py-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-text-primary-light dark:text-text-primary-dark text-lg font-semibold">قائمة المستخدمين</h3>
                    <span class="text-xs text-slate-400">{{ users.length }} مستخدم</span>
                </div>
                <!-- User Filter Tabs -->
                <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar pb-1">
                    <button v-for="filter in userFilters" :key="filter.id" @click="userFilter = filter.id"
                        class="shrink-0 h-8 px-4 rounded-full text-xs font-bold shadow-sm transition-colors"
                        :class="userFilter === filter.id ? 'bg-primary text-white' : 'bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                        {{ filter.label }}
                    </button>
                </div>
                <div class="flex flex-col gap-3">
                    <div v-if="loading" class="text-center py-6 text-slate-400"><span class="material-symbols-outlined text-3xl animate-spin">progress_activity</span></div>
                    <div v-else-if="users.length === 0" class="text-center py-6 text-slate-400">
                        <span class="material-symbols-outlined text-4xl">person_off</span>
                        <p class="mt-2 text-sm">لا يوجد مستخدمين</p>
                    </div>
                    <div v-for="u in users" :key="u.id" @click="openUserDetails(u)" class="flex items-center justify-between p-3 bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 active:scale-[0.98] transition-all">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-10 h-10 rounded-full bg-cover bg-center" :style="`background-image: url('${u.photo || 'https://ui-avatars.com/api/?name=' + u.name}');`"></div>
                                <div class="absolute bottom-0 right-0 w-3 h-3 border-2 border-white dark:border-surface-dark rounded-full" :class="u.is_active ? 'bg-green-500' : 'bg-slate-300'"></div>
                            </div>
                            <div>
                                <p class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold">{{ u.name }}</p>
                                <p class="text-text-secondary-light dark:text-text-secondary-dark text-xs">{{ u.role === 'resident' ? 'ساكن' : u.role === 'provider' ? 'مقدم خدمة' : 'مسؤول' }} • {{ u.phone }}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400">chevron_left</span>
                    </div>
                    <!-- Load More Users -->
                    <button v-if="nextUsersUrl" @click="loadMoreUsers" class="w-full py-3 mt-2 text-primary font-bold text-sm bg-white dark:bg-surface-dark border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        {{ loadingMoreUsers ? 'جاري التحميل...' : 'عرض المزيد' }}
                    </button>
                </div>
            </section>

            <!-- Requests Section -->
            <section v-if="activeSection === 'requests'" class="px-4 py-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-text-primary-light dark:text-text-primary-dark text-lg font-semibold">الطلبات</h3>
                    <span class="text-xs text-slate-400">{{ filteredRequests.length }} طلب</span>
                </div>
                <!-- Request Filter Tabs -->
                <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar pb-1">
                    <button v-for="filter in requestFilters" :key="filter.id" @click="requestFilter = filter.id"
                        class="shrink-0 h-8 px-4 rounded-full text-xs font-bold shadow-sm transition-colors"
                        :class="requestFilter === filter.id ? 'bg-primary text-white' : 'bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                        {{ filter.label }}
                    </button>
                </div>
                <div class="flex flex-col gap-3">
                    <div v-if="loadingRequests" class="text-center py-6 text-slate-400"><span class="material-symbols-outlined text-3xl animate-spin">progress_activity</span></div>
                    <div v-else-if="filteredRequests.length === 0" class="text-center py-6 text-slate-400">
                        <span class="material-symbols-outlined text-4xl">inbox</span>
                        <p class="mt-2 text-sm">لا توجد طلبات</p>
                    </div>
                    <div v-for="req in filteredRequests" :key="req.id" @click="openRequestDetails(req)" class="flex flex-col bg-white dark:bg-surface-dark p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 active:scale-[0.98] transition-all">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-primary"><span class="material-symbols-outlined">{{ req.category?.icon || 'help' }}</span></div>
                                <div>
                                    <p class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold">{{ req.category?.name }}</p>
                                    <p class="text-text-secondary-light dark:text-text-secondary-dark text-xs">{{ req.resident?.name }} • {{ formatDate(req.created_at) }}</p>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-slate-400">chevron_left</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold" :class="{
                                'bg-orange-100 dark:bg-orange-900/40 text-orange-600 dark:text-orange-300': req.status === 'pending',
                                'bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300': req.status === 'accepted_offer',
                                'bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-300': req.status === 'in_progress',
                                'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-300': req.status === 'completed'
                            }">{{ req.status === 'pending' ? 'انتظار' : req.status === 'in_progress' ? 'جاري' : req.status === 'completed' ? 'مكتمل' : 'تم قبول عرض' }}</span>
                        </div>
                        <div v-if="req.delivery_method" class="mt-2 flex items-center justify-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl w-full">
                            <span class="material-symbols-outlined text-[16px]">directions_car</span>
                            <span>{{ req.delivery_method }}</span>
                        </div>
                    </div>
                    <!-- Load More Requests -->
                    <button v-if="nextRequestsUrl" @click="loadMoreRequests" class="w-full py-3 mt-2 text-primary font-bold text-sm bg-white dark:bg-surface-dark border border-slate-100 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        {{ loadingMoreRequests ? 'جاري التحميل...' : 'عرض المزيد' }}
                    </button>
                </div>
            </section>

            <!-- Services Section -->
            <section v-if="activeSection === 'services'" class="px-4 py-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-text-primary-light dark:text-text-primary-dark text-lg font-semibold">إدارة الخدمات</h3>
                    <button @click="openAddService" class="text-primary p-1"><span class="material-symbols-outlined">add</span></button>
                </div>
                <div class="flex flex-col gap-3">
                    <div v-for="(service, index) in services" :key="service.id" @click="openServiceDetails(service)" class="flex items-center justify-between p-4 bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 active:scale-[0.98] transition-all" :class="!service.is_active && 'opacity-75'">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-lg" :class="getServiceColor(index)" :style="getServiceStyle(index)">
                                <span class="material-symbols-outlined" :class="getServiceTextColor(index)">{{ service.icon }}</span>
                            </div>
                            <div>
                                <p class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold">{{ service.name }}</p>
                                <p class="text-text-secondary-light dark:text-text-secondary-dark text-xs">{{ service.is_active ? 'نشط' : 'متوقف مؤقتاً' }}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400">chevron_left</span>
                    </div>
                </div>
            </section>

            <!-- Compounds Section -->
            <section v-if="activeSection === 'compounds'" class="px-4 py-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-text-primary-light dark:text-text-primary-dark text-lg font-semibold">إدارة الكمبوندات</h3>
                    <button @click="openAddCompound" class="text-primary p-1"><span class="material-symbols-outlined">add</span></button>
                </div>
                <div class="flex flex-col gap-3">
                    <div v-if="loadingCompounds" class="text-center py-6 text-slate-400"><span class="material-symbols-outlined text-3xl animate-spin">progress_activity</span></div>
                    <div v-else-if="compounds.length === 0" class="text-center py-6 text-slate-400">
                        <span class="material-symbols-outlined text-4xl">apartment</span>
                        <p class="mt-2 text-sm">لا توجد كمبوندات</p>
                    </div>
                    <div v-for="compound in compounds" :key="compound.id" @click="openCompoundDetails(compound)" class="flex items-center justify-between p-4 bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 active:scale-[0.98] transition-all" :class="!compound.is_active && 'opacity-75'">
                        <div class="flex items-center gap-3">
                            <div class="p-2.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-600"><span class="material-symbols-outlined">apartment</span></div>
                            <div>
                                <p class="text-text-primary-light dark:text-text-primary-dark text-sm font-semibold">{{ compound.name }}</p>
                                <p class="text-text-secondary-light dark:text-text-secondary-dark text-xs">{{ compound.residents_count || 0 }} ساكن • {{ compound.is_active ? 'نشط' : 'متوقف' }}</p>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400">chevron_left</span>
                    </div>
                </div>
            </section>
        </main>

        <!-- Bottom Tab Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white dark:bg-surface-dark border-t border-slate-200 dark:border-slate-800 px-4 pb-6 pt-3 z-50">
            <ul class="flex justify-around items-center max-w-md mx-auto">
                <li><a class="flex flex-col items-center gap-1" :class="activeSection === 'users' || activeSection === 'requests' || activeSection === 'services' ? 'text-primary' : 'text-slate-400'" href="/dashboard"><span class="material-symbols-outlined text-[26px]">dashboard</span><span class="text-[10px] font-medium">الرئيسية</span></a></li>
                <li><a class="flex flex-col items-center gap-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors" href="/settings"><span class="material-symbols-outlined text-[26px]">settings</span><span class="text-[10px] font-medium">الإعدادات</span></a></li>
            </ul>
        </nav>

        <!-- User Details/Edit Modal -->
        <Modal :show="showUserModal" :title="editingUser ? 'تعديل المستخدم' : selectedUser?.name" @close="showUserModal = false; editingUser = false">
            <div v-if="selectedUser" class="space-y-4">
                <!-- View Mode -->
                <template v-if="!editingUser">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-cover bg-center border-2 border-slate-200" :style="`background-image: url('${selectedUser.photo || 'https://ui-avatars.com/api/?name=' + selectedUser.name}');`"></div>
                        <div>
                            <p class="font-bold text-lg">{{ selectedUser.name }}</p>
                            <p class="text-slate-500 text-sm">{{ selectedUser.role === 'resident' ? 'ساكن' : selectedUser.role === 'provider' ? 'مقدم خدمة' : 'مسؤول' }}</p>
                        </div>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">الهاتف</span>
                            <a :href="`tel:${selectedUser.phone}`" class="text-primary font-semibold">{{ selectedUser.phone }}</a>
                        </div>
                        <!-- Resident-specific info -->
                        <template v-if="selectedUser.role === 'resident'">
                            <div class="flex justify-between items-center" v-if="selectedUser.compound">
                                <span class="text-slate-500 text-sm">الكمبوند</span>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">{{ selectedUser.compound.name }}</span>
                                    <a v-if="selectedUser.compound.location_url" :href="selectedUser.compound.location_url" target="_blank" class="text-primary hover:text-primary-dark" title="الموقع على الخريطة">
                                        <span class="material-symbols-outlined text-lg">location_on</span>
                                    </a>
                                </div>
                            </div>
                            <div class="flex justify-between items-center" v-if="selectedUser.block_no">
                                <span class="text-slate-500 text-sm">المبنى</span>
                                <span class="font-semibold">{{ selectedUser.block_no }}</span>
                            </div>
                            <div class="flex justify-between items-center" v-if="selectedUser.floor">
                                <span class="text-slate-500 text-sm">الدور</span>
                                <span class="font-semibold">{{ selectedUser.floor }}</span>
                            </div>
                            <div class="flex justify-between items-center" v-if="selectedUser.apt_no">
                                <span class="text-slate-500 text-sm">رقم الشقة</span>
                                <span class="font-semibold">{{ selectedUser.apt_no }}</span>
                            </div>
                        </template>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">الحالة</span>
                            <span class="font-bold" :class="selectedUser.is_active ? 'text-green-600' : 'text-red-500'">{{ selectedUser.is_active ? 'مفعل' : 'غير مفعل' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">تاريخ التسجيل</span>
                            <span class="font-semibold">{{ formatDate(selectedUser.created_at) }}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <button @click="startEditUser" class="flex-1 py-3 rounded-xl font-bold bg-primary text-white">
                                <span class="material-symbols-outlined text-lg align-middle ml-1">edit</span> تعديل
                            </button>
                            <button @click="toggleUserStatus(selectedUser)" class="flex-1 py-3 rounded-xl font-bold text-white" :class="selectedUser.is_active ? 'bg-red-500' : 'bg-green-500'">
                                {{ selectedUser.is_active ? 'إيقاف' : 'تفعيل' }}
                            </button>
                        </div>
                        <button @click="openChangePasswordModal(selectedUser)" class="w-full py-3 rounded-xl font-bold bg-amber-500 hover:bg-amber-600 text-white transition-colors">
                            <span class="material-symbols-outlined text-lg align-middle ml-1">lock_reset</span> تغيير كلمة المرور
                        </button>
                    </div>
                </template>
                <!-- Edit Mode -->
                <template v-else>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">الاسم</label>
                            <input v-model="userForm.name" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">رقم الهاتف</label>
                            <input v-model="userForm.phone" type="tel" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">نوع المستخدم</label>
                            <select v-model="userForm.role" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                <option value="resident">ساكن</option>
                                <option value="provider">مقدم خدمة</option>
                                <option value="admin">مسؤول</option>
                            </select>
                        </div>
                        <!-- Resident-specific fields -->
                        <template v-if="userForm.role === 'resident'">
                            <div>
                                <label class="block text-sm font-medium mb-1">الكمبوند</label>
                                <select v-model="userForm.compound_id" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                    <option value="">اختر الكمبوند</option>
                                    <option v-for="comp in compounds" :key="comp.id" :value="comp.id">{{ comp.name }}</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">رقم المبنى</label>
                                    <input v-model="userForm.block_no" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">الدور</label>
                                    <input v-model="userForm.floor" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">رقم الشقة</label>
                                <input v-model="userForm.apt_no" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" />
                            </div>
                        </template>
                        <div class="flex gap-2">
                            <button @click="saveUser" :disabled="savingUser" class="flex-1 py-3 rounded-xl font-bold bg-primary text-white">
                                {{ savingUser ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
                            </button>
                            <button @click="editingUser = false" class="flex-1 py-3 rounded-xl font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">إلغاء</button>
                        </div>
                    </div>
                </template>
            </div>
        </Modal>

        <!-- Request Details Modal -->
        <Modal :show="showRequestModal" title="تفاصيل الطلب" @close="showRequestModal = false">
            <div v-if="selectedRequest" class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-xl bg-blue-50 text-primary">
                        <span class="material-symbols-outlined text-2xl">{{ selectedRequest.category?.icon || 'help' }}</span>
                    </div>
                    <div>
                        <p class="font-bold text-lg">{{ selectedRequest.category?.name }}</p>
                        <span class="px-2 py-1 rounded-full text-xs font-bold" :class="{
                            'bg-orange-100 text-orange-600': selectedRequest.status === 'pending',
                            'bg-blue-100 text-blue-600': selectedRequest.status === 'accepted_offer' || selectedRequest.status === 'in_progress',
                            'bg-green-100 text-green-600': selectedRequest.status === 'completed'
                        }">{{ selectedRequest.status === 'pending' ? 'قيد الانتظار' : selectedRequest.status === 'in_progress' ? 'جاري التنفيذ' : selectedRequest.status === 'completed' ? 'مكتمل' : 'تم قبول عرض' }}</span>
                    </div>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">الساكن</span>
                        <span class="font-semibold">{{ selectedRequest.resident?.name }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">الهاتف</span>
                        <a :href="`tel:${selectedRequest.resident?.phone}`" class="text-primary font-semibold">{{ selectedRequest.resident?.phone }}</a>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">تاريخ الطلب</span>
                        <span class="font-semibold">{{ formatDate(selectedRequest.created_at) }}</span>
                    </div>
                    <div v-if="selectedRequest.delivery_method" class="flex justify-between items-center bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg -mx-2">
                        <span class="text-slate-500 dark:text-slate-400 text-sm flex items-center gap-1">
                            <span class="material-symbols-outlined text-base">directions_car</span>
                            طريقة التقديم
                        </span>
                        <span class="font-bold text-blue-700 dark:text-blue-300">{{ selectedRequest.delivery_method }}</span>
                    </div>
                    <div v-if="selectedRequest.accepted_proposal" class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">مقدم الخدمة</span>
                        <span class="font-semibold">{{ selectedRequest.accepted_proposal.provider?.name }}</span>
                    </div>
                    <div v-if="selectedRequest.accepted_proposal" class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">السعر</span>
                        <span class="font-bold text-green-600">{{ selectedRequest.accepted_proposal.price }} ج.م</span>
                    </div>
                </div>
                <div v-if="selectedRequest.notes" class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
                    <p class="text-slate-500 text-sm mb-1">ملاحظات</p>
                    <p class="text-slate-800 dark:text-white">{{ selectedRequest.notes }}</p>
                </div>
            </div>
        </Modal>

        <!-- Service Details/Edit Modal -->
        <Modal :show="showServiceModal" :title="editingService ? (selectedService ? 'تعديل الخدمة' : 'إضافة خدمة جديدة') : selectedService?.name" @close="showServiceModal = false; editingService = false">
            <div v-if="selectedService || editingService" class="space-y-4">
                <!-- View Mode -->
                <template v-if="!editingService">
                    <div class="flex items-center gap-4 justify-center">
                        <div class="p-4 rounded-2xl bg-orange-50 text-orange-500">
                            <span class="material-symbols-outlined text-4xl">{{ selectedService.icon }}</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-xl">{{ selectedService.name }}</p>
                        <p class="text-slate-500">{{ selectedService.description || 'خدمة متوفرة في التطبيق' }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">الحالة</span>
                            <span class="font-bold" :class="selectedService.is_active ? 'text-green-600' : 'text-red-500'">{{ selectedService.is_active ? 'نشط' : 'متوقف' }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="startEditService" class="flex-1 py-3 rounded-xl font-bold bg-primary text-white">
                            <span class="material-symbols-outlined text-lg align-middle ml-1">edit</span> تعديل
                        </button>
                        <button @click="toggleServiceStatus(selectedService)" class="flex-1 py-3 rounded-xl font-bold text-white" :class="selectedService.is_active ? 'bg-red-500' : 'bg-green-500'">
                            {{ selectedService.is_active ? 'إيقاف' : 'تفعيل' }}
                        </button>
                    </div>
                </template>
                <!-- Edit Mode -->
                <template v-else>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">اسم الخدمة</label>
                            <input v-model="serviceForm.name" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">الأيقونة (Material Symbol)</label>
                            <input v-model="serviceForm.icon" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" placeholder="مثال: plumbing, electrical_services" />
                            <div class="mt-2 flex items-center gap-2 text-slate-500 text-sm">
                                <span class="material-symbols-outlined text-2xl text-orange-500">{{ serviceForm.icon || 'help' }}</span>
                                <span>معاينة الأيقونة</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">الوصف</label>
                            <textarea v-model="serviceForm.description" rows="3" class="w-full p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">طرق التقديم (Delivery Options)</label>
                            <div class="flex items-center gap-2 mb-2">
                                <input v-model="serviceForm.new_delivery_method" @keydown.enter.prevent="addDeliveryMethod" type="text" class="flex-1 h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm" placeholder="اكتب واضغط Enter (مثال: زيارة منزلية)" />
                                <button @click.prevent="addDeliveryMethod" class="h-10 px-4 rounded-xl bg-slate-100 dark:bg-slate-700 font-bold text-sm hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">إضافة</button>
                            </div>
                            <div class="flex flex-col gap-2">
                                <div v-for="(method, index) in serviceForm.delivery_methods" :key="index" class="px-4 py-2 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 text-sm font-medium flex items-center justify-between w-full">
                                    <span>{{ method }}</span>
                                    <button @click="removeDeliveryMethod(index)" class="hover:text-red-500 rounded-full p-1"><span class="material-symbols-outlined text-lg">close</span></button>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="saveService" :disabled="savingService" class="flex-1 py-3 rounded-xl font-bold bg-primary text-white">
                                {{ savingService ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
                            </button>
                            <button @click="editingService = false" class="flex-1 py-3 rounded-xl font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">إلغاء</button>
                        </div>
                    </div>
                </template>
            </div>
        </Modal>

        <!-- Compound Details/Edit Modal -->
        <Modal :show="showCompoundModal" :title="editingCompound ? (selectedCompound?.id ? 'تعديل الكمبوند' : 'إضافة كمبوند جديد') : selectedCompound?.name" @close="showCompoundModal = false; editingCompound = false">
            <div v-if="selectedCompound || editingCompound" class="space-y-4">
                <!-- View Mode -->
                <template v-if="!editingCompound && selectedCompound">
                    <div class="flex items-center gap-4 justify-center">
                        <div class="p-4 rounded-2xl bg-green-50 text-green-600">
                            <span class="material-symbols-outlined text-4xl">apartment</span>
                        </div>
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-xl">{{ selectedCompound.name }}</p>
                        <p class="text-slate-500">{{ selectedCompound.address || 'لا يوجد عنوان' }}</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">عدد السكان</span>
                            <span class="font-bold">{{ selectedCompound.residents_count || 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 text-sm">الحالة</span>
                            <span class="font-bold" :class="selectedCompound.is_active ? 'text-green-600' : 'text-red-500'">{{ selectedCompound.is_active ? 'نشط' : 'متوقف' }}</span>
                        </div>
                        <div class="flex justify-between items-center" v-if="selectedCompound.location_url">
                            <span class="text-slate-500 text-sm">الموقع</span>
                            <a :href="selectedCompound.location_url" target="_blank" class="text-primary font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-lg">location_on</span> فتح في الخريطة
                            </a>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="startEditCompound" class="flex-1 py-3 rounded-xl font-bold bg-primary text-white">
                            <span class="material-symbols-outlined text-lg align-middle ml-1">edit</span> تعديل
                        </button>
                        <button @click="toggleCompoundStatus(selectedCompound)" class="flex-1 py-3 rounded-xl font-bold text-white" :class="selectedCompound.is_active ? 'bg-red-500' : 'bg-green-500'">
                            {{ selectedCompound.is_active ? 'إيقاف' : 'تفعيل' }}
                        </button>
                    </div>
                </template>
                <!-- Edit/Add Mode -->
                <template v-else>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">اسم الكمبوند</label>
                            <input v-model="compoundForm.name" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" placeholder="مثال: مدينتي" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">العنوان</label>
                            <input v-model="compoundForm.address" type="text" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" placeholder="مثال: القاهرة الجديدة" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">رابط Google Maps</label>
                            <input v-model="compoundForm.location_url" type="url" class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800" placeholder="https://maps.google.com/..." dir="ltr" />
                        </div>
                        <div class="flex gap-2">
                            <button @click="saveCompound" :disabled="savingCompound" class="flex-1 py-3 rounded-xl font-bold bg-primary text-white">
                                {{ savingCompound ? 'جاري الحفظ...' : (selectedCompound?.id ? 'حفظ التغييرات' : 'إضافة الكمبوند') }}
                            </button>
                            <button @click="editingCompound = false; showCompoundModal = false" class="flex-1 py-3 rounded-xl font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">إلغاء</button>
                        </div>
                    </div>
                </template>
            </div>
        </Modal>

        <!-- Change Password Modal -->
        <Modal :show="showPasswordModal" title="تغيير كلمة المرور" @close="showPasswordModal = false">
            <div v-if="passwordChangeUser" class="space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl flex gap-3 text-blue-800 dark:text-blue-200">
                    <span class="material-symbols-outlined text-2xl flex-shrink-0">info</span>
                    <div class="text-sm">
                        <p class="font-semibold mb-1">تغيير كلمة المرور لـ: {{ passwordChangeUser.name }}</p>
                        <p class="text-xs opacity-80">سيتم تحديث كلمة المرور فوراً وسيحتاج المستخدم لاستخدام الكلمة الجديدة عند تسجيل الدخول.</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">كلمة المرور الجديدة</label>
                    <input 
                        v-model="newPassword" 
                        type="password" 
                        placeholder="أدخل كلمة المرور الجديدة"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">تأكيد كلمة المرور</label>
                    <input 
                        v-model="confirmPassword" 
                        type="password" 
                        placeholder="أعد إدخال كلمة المرور"
                        class="w-full h-12 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent"
                    />
                </div>
                
                <div class="flex gap-2 pt-2">
                    <button 
                        @click="changeUserPassword" 
                        :disabled="changingPassword || !newPassword || newPassword !== confirmPassword"
                        class="flex-1 py-3 rounded-xl font-bold bg-primary text-white disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <span v-if="changingPassword" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        <span>{{ changingPassword ? 'جاري التحديث...' : 'تحديث كلمة المرور' }}</span>
                    </button>
                    <button 
                        @click="showPasswordModal = false; newPassword = ''; confirmPassword = ''" 
                        class="flex-1 py-3 rounded-xl font-bold bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200"
                    >
                        إلغاء
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Toast from '@/Components/Toast.vue';
import Modal from '@/Components/Modal.vue';

export default {
    name: 'AdminDashboard',
    components: { Head, Toast, Modal },
    data() {
        return {
            loading: true,
            loadingRequests: true,
            stats: {},
            users: [],
            services: [],
            requests: [],
            searchQuery: '',
            activeSection: 'users',
            userFilter: 'all',
            userFilters: [
                { id: 'all', label: 'الكل' },
                { id: 'resident', label: 'المقيمين' },
                { id: 'provider', label: 'مقدمي الخدمات' },
                { id: 'inactive', label: 'غير مفعل' }
            ],
            requestFilter: 'all',
            requestFilters: [
                { id: 'all', label: 'الكل' },
                { id: 'pending', label: 'قيد الانتظار' },
                { id: 'accepted_offer', label: 'تم قبول عرض' },
                { id: 'in_progress', label: 'جاري التنفيذ' },
                { id: 'completed', label: 'مكتمل' }
            ],
            // Modal states
            showUserModal: false,
            showRequestModal: false,
            showServiceModal: false,
            showCompoundModal: false,
            selectedUser: null,
            selectedRequest: null,
            selectedService: null,
            selectedCompound: null,
            // Edit states
            editingUser: false,
            editingService: false,
            editingCompound: false,
            savingUser: false,
            savingService: false,
            savingCompound: false,
            userForm: { name: '', phone: '', compound_id: '', block_no: '', floor: '', apt_no: '', role: '' },
            serviceForm: { name: '', icon: '', description: '', delivery_methods: [], new_delivery_method: '' },
            compoundForm: { name: '', address: '', location_url: '' },
            // Compounds data
            compounds: [],
            loadingCompounds: false,
            // Pagination
            nextUsersUrl: null,
            nextRequestsUrl: null,
            loadingMoreRequests: false,
            // Password change
            showPasswordModal: false,
            passwordChangeUser: null,
            newPassword: '',
            confirmPassword: '',
            changingPassword: false,
            // Search debounce
            searchDebounceTimer: null,
        };
    },
    computed: {
        user() {
            const page = usePage();
            return page.props.auth ? page.props.auth.user : {};
        },
        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        },
        filteredRequests() {
            let filtered = this.requests;
            
            // Filter by status
            if (this.requestFilter !== 'all') {
                filtered = filtered.filter(r => r.status === this.requestFilter);
            }
            
            // Filter by search query
            if (this.searchQuery && this.activeSection === 'requests') {
                const q = this.searchQuery.toLowerCase();
                filtered = filtered.filter(r => 
                    r.category?.name?.toLowerCase().includes(q) || 
                    r.resident?.name?.toLowerCase().includes(q) ||
                    r.notes?.toLowerCase().includes(q)
                );
            }
            
            return filtered;
        }
    },
    watch: {
        activeSection(newVal) {
            const url = new URL(window.location);
            url.searchParams.set('tab', newVal);
            window.history.pushState({}, '', url);
        },
        searchQuery() {
            // Clear existing timer
            if (this.searchDebounceTimer) {
                clearTimeout(this.searchDebounceTimer);
            }
            
            // Set new timer to reload users after 500ms of no typing
            this.searchDebounceTimer = setTimeout(() => {
                this.loadUsers();
            }, 500);
        },
        userFilter(newVal) {
            const url = new URL(window.location);
            if (newVal && newVal !== 'all') {
                url.searchParams.set('user_filter', newVal);
            } else {
                url.searchParams.delete('user_filter');
            }
            window.history.pushState({}, '', url);
            // Reload users when filter changes
            this.loadUsers();
        },
        requestFilter(newVal) {
            const url = new URL(window.location);
            if (newVal && newVal !== 'all') {
                url.searchParams.set('request_filter', newVal);
            } else {
                url.searchParams.delete('request_filter');
            }
            window.history.pushState({}, '', url);
        }
    },
    mounted() {
        // Restore state from URL
        const params = new URLSearchParams(window.location.search);
        
        const tab = params.get('tab');
        if (tab && ['users', 'requests', 'services', 'compounds'].includes(tab)) {
            this.activeSection = tab;
        }

        const uFilter = params.get('user_filter');
        if (uFilter) this.userFilter = uFilter;

        const rFilter = params.get('request_filter');
        if (rFilter) this.requestFilter = rFilter;

        this.loadStats();
        this.loadUsers();
        this.loadServices();
        this.loadRequests();
        this.loadCompounds();
    },
    methods: {
        async loadStats() {
            try {
                const response = await axios.get('/api/admin/stats');
                if (response.data.success) {
                    this.stats = response.data.stats;
                }
            } catch (e) {
                console.error(e);
            }
        },
        async loadUsers() {
            this.loading = true;
            try {
                // Build query parameters
                const params = new URLSearchParams();
                
                // Add search parameter
                if (this.searchQuery) {
                    params.append('search', this.searchQuery);
                }
                
                // Add role filter
                if (this.userFilter === 'resident') {
                    params.append('role', 'resident');
                } else if (this.userFilter === 'provider') {
                    params.append('role', 'provider');
                } else if (this.userFilter === 'inactive') {
                    params.append('is_active', '0');
                }
                
                const url = `/api/users${params.toString() ? '?' + params.toString() : ''}`;
                const response = await axios.get(url);
                
                if (response.data.success) {
                    this.users = response.data.users.data;
                    this.nextUsersUrl = response.data.users.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async loadMoreUsers() {
            if (!this.nextUsersUrl || this.loadingMoreUsers) return;
            this.loadingMoreUsers = true;
            try {
                const response = await axios.get(this.nextUsersUrl);
                if (response.data.success) {
                    this.users = [...this.users, ...response.data.users.data];
                    this.nextUsersUrl = response.data.users.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingMoreUsers = false;
            }
        },
        async loadServices() {
            try {
                const response = await axios.get('/api/services');
                if (response.data.success) {
                    this.services = response.data.categories;
                }
            } catch (e) {
                console.error(e);
            }
        },
        async loadRequests() {
            this.loadingRequests = true;
            try {
                const response = await axios.get('/api/requests');
                if (response.data.success) {
                    this.requests = response.data.requests.data;
                    this.nextRequestsUrl = response.data.requests.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingRequests = false;
            }
        },
        async loadMoreRequests() {
            if (!this.nextRequestsUrl || this.loadingMoreRequests) return;
            this.loadingMoreRequests = true;
            try {
                const response = await axios.get(this.nextRequestsUrl);
                if (response.data.success) {
                    this.requests = [...this.requests, ...response.data.requests.data];
                    this.nextRequestsUrl = response.data.requests.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingMoreRequests = false;
            }
        },
        // Service Helpers
        openAddService() {
            this.selectedService = null;
            this.serviceForm = { name: '', icon: '', description: '', delivery_methods: [], new_delivery_method: '' };
            this.editingService = true;
            this.showServiceModal = true;
        },
        getServiceColor(index) {
            const colors = [
                'bg-blue-50 dark:bg-blue-900/20',
                'bg-purple-50 dark:bg-purple-900/20',
                'bg-rose-50 dark:bg-rose-900/20',
                'bg-amber-50 dark:bg-amber-900/20',
                'bg-emerald-50 dark:bg-emerald-900/20',
                'bg-cyan-50 dark:bg-cyan-900/20'
            ];
            return colors[index % colors.length];
        },
        getServiceTextColor(index) {
             const colors = [
                'text-blue-600',
                'text-purple-600',
                'text-rose-600',
                'text-amber-600',
                'text-emerald-600',
                'text-cyan-600'
            ];
            return colors[index % colors.length];
        },
        getServiceStyle(index) {
            // Optional: fallback if classes fail
            return {};
        },
        async toggleUserStatus(user) {
            try {
                const response = await axios.post(`/api/admin/users/${user.id}/toggle-status`);
                if (response.data.success) {
                    user.is_active = !user.is_active;
                    if (window.showToast) window.showToast('تم تحديث حالة المستخدم', 'success');
                }
            } catch (e) {
                if (window.showToast) window.showToast('حدث خطأ', 'error');
            }
        },
        openChangePasswordModal(user) {
            this.passwordChangeUser = user;
            this.newPassword = '';
            this.confirmPassword = '';
            this.showPasswordModal = true;
        },
        async changeUserPassword() {
            if (!this.newPassword || this.newPassword !== this.confirmPassword) {
                if (window.showToast) window.showToast('كلمات المرور غير متطابقة', 'error');
                return;
            }
            
            if (this.newPassword.length < 6) {
                if (window.showToast) window.showToast('كلمة المرور يجب أن تكون 6 أحرف على الأقل', 'error');
                return;
            }

            this.changingPassword = true;
            try {
                const response = await axios.post(`/api/admin/users/${this.passwordChangeUser.id}/change-password`, {
                    password: this.newPassword,
                    password_confirmation: this.confirmPassword
                });
                
                if (response.data.success) {
                    if (window.showToast) window.showToast('تم تغيير كلمة المرور بنجاح', 'success');
                    this.showPasswordModal = false;
                    this.newPassword = '';
                    this.confirmPassword = '';
                    this.passwordChangeUser = null;
                } else {
                    if (window.showToast) window.showToast(response.data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                console.error(e);
                if (window.showToast) window.showToast('حدث خطأ أثناء تغيير كلمة المرور', 'error');
            } finally {
                this.changingPassword = false;
            }
        },
        async toggleServiceStatus(service) {
            try {
                const response = await axios.post(`/api/admin/services/${service.id}/toggle-status`);
                if (response.data.success) {
                    service.is_active = response.data.is_active;
                    if (window.showToast) window.showToast(`تم ${service.is_active ? 'تفعيل' : 'إيقاف'} الخدمة`, 'success');
                    // Refresh stats since category counts might change
                    this.loadStats();
                }
            } catch (e) {
                if (window.showToast) window.showToast('حدث خطأ أثناء تحديث حالة الخدمة', 'error');
            }
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('ar-EG', {
                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        },
        openUserDetails(user) {
            this.selectedUser = user;
            this.editingUser = false;
            this.showUserModal = true;
        },
        openRequestDetails(request) {
            this.selectedRequest = request;
            this.showRequestModal = true;
        },
        openServiceDetails(service) {
            this.selectedService = service;
            this.editingService = false;
            this.showServiceModal = true;
        },
        startEditUser() {
            this.userForm = {
                name: this.selectedUser.name || '',
                phone: this.selectedUser.phone || '',
                compound_id: this.selectedUser.compound_id || '',
                block_no: this.selectedUser.block_no || '',
                floor: this.selectedUser.floor || '',
                apt_no: this.selectedUser.apt_no || '',
                role: this.selectedUser.role || 'resident',
            };
            this.editingUser = true;
        },
        async saveUser() {
            this.savingUser = true;
            try {
                const response = await axios.put(`/api/admin/users/${this.selectedUser.id}`, this.userForm);
                if (response.data.success) {
                    // Update local data
                    Object.assign(this.selectedUser, this.userForm);
                    this.editingUser = false;
                    if (window.showToast) window.showToast('تم حفظ بيانات المستخدم بنجاح', 'success');
                } else {
                    if (window.showToast) window.showToast(response.data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                if (window.showToast) window.showToast(e.response?.data?.message || 'حدث خطأ أثناء الحفظ', 'error');
            } finally {
                this.savingUser = false;
            }
        },
        startEditService() {
            this.serviceForm = {
                name: this.selectedService.name || '',
                icon: this.selectedService.icon || '',
                description: this.selectedService.description || '',
                delivery_methods: this.selectedService.delivery_methods ? [...this.selectedService.delivery_methods] : [],
                new_delivery_method: ''
            };
            this.editingService = true;
        },
        addDeliveryMethod() {
            const method = this.serviceForm.new_delivery_method.trim();
            if (method && !this.serviceForm.delivery_methods.includes(method)) {
                this.serviceForm.delivery_methods.push(method);
                this.serviceForm.new_delivery_method = '';
            }
        },
        removeDeliveryMethod(index) {
            this.serviceForm.delivery_methods.splice(index, 1);
        },
        async saveService() {
            this.savingService = true;
            try {
                let response;
                if (this.selectedService && this.selectedService.id) {
                     // Update
                    response = await axios.put(`/api/admin/services/${this.selectedService.id}`, this.serviceForm);
                } else {
                    // Create
                    response = await axios.post('/api/services', this.serviceForm);
                }

                if (response.data.success) {
                    if (this.selectedService && this.selectedService.id) {
                         // Update local data
                        Object.assign(this.selectedService, this.serviceForm);
                    } else {
                        // Add new service to list
                        this.services.push(response.data.category);
                    }
                    this.showServiceModal = false;
                    this.editingService = false;
                    if (window.showToast) window.showToast('تم حفظ بيانات الخدمة بنجاح', 'success');
                } else {
                    if (window.showToast) window.showToast(response.data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                if (window.showToast) window.showToast(e.response?.data?.message || 'حدث خطأ أثناء الحفظ', 'error');
            } finally {
                this.savingService = false;
            }
        },
        // Compound methods
        async loadCompounds() {
            this.loadingCompounds = true;
            try {
                const response = await axios.get('/api/compounds');
                if (response.data.success) {
                    this.compounds = response.data.compounds;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingCompounds = false;
            }
        },
        openCompoundDetails(compound) {
            this.selectedCompound = compound;
            this.editingCompound = false;
            this.showCompoundModal = true;
        },
        openAddCompound() {
            this.selectedCompound = null;
            this.compoundForm = { name: '', address: '', location_url: '' };
            this.editingCompound = true;
            this.showCompoundModal = true;
        },
        startEditCompound() {
            this.compoundForm = {
                name: this.selectedCompound.name || '',
                address: this.selectedCompound.address || '',
                location_url: this.selectedCompound.location_url || '',
            };
            this.editingCompound = true;
        },
        async saveCompound() {
            this.savingCompound = true;
            try {
                let response;
                if (this.selectedCompound?.id) {
                    // Update existing
                    response = await axios.put(`/api/admin/compounds/${this.selectedCompound.id}`, this.compoundForm);
                    if (response.data.success) {
                        Object.assign(this.selectedCompound, this.compoundForm);
                        this.editingCompound = false;
                        if (window.showToast) window.showToast('تم حفظ بيانات الكمبوند بنجاح', 'success');
                    }
                } else {
                    // Create new
                    response = await axios.post('/api/compounds', this.compoundForm);
                    if (response.data.success) {
                        this.compounds.push(response.data.compound);
                        this.editingCompound = false;
                        this.showCompoundModal = false;
                        if (window.showToast) window.showToast('تم إضافة الكمبوند بنجاح', 'success');
                    }
                }
                if (!response.data.success) {
                    if (window.showToast) window.showToast(response.data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                if (window.showToast) window.showToast(e.response?.data?.message || 'حدث خطأ أثناء الحفظ', 'error');
            } finally {
                this.savingCompound = false;
            }
        },
        async toggleCompoundStatus(compound) {
            try {
                const response = await axios.post(`/api/admin/compounds/${compound.id}/toggle-status`);
                if (response.data.success) {
                    compound.is_active = !compound.is_active;
                    if (window.showToast) window.showToast(`تم ${compound.is_active ? 'تفعيل' : 'إيقاف'} الكمبوند`, 'success');
                }
            } catch (e) {
                if (window.showToast) window.showToast('حدث خطأ', 'error');
            }
        },
    },
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
