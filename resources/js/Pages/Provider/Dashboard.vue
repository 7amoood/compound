<template>
    <Head title="لوحة التحكم - مقدم الخدمة" />
    <Toast />
    
    <div class="relative flex h-[100dvh] w-full flex-col overflow-hidden max-w-md mx-auto bg-background-light dark:bg-background-dark shadow-2xl font-display" dir="rtl">
        <!-- Top App Bar -->
        <div class="flex items-center px-4 py-3 pb-2 justify-between z-10 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md sticky top-0 transition-all duration-300">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 ring-2 ring-primary/20" :style="`background-image: url('${user.photo || 'https://ui-avatars.com/api/?name=' + user.name}');`"></div>
                    <div class="absolute bottom-0 right-0 size-3 bg-green-500 rounded-full border-2 border-white dark:border-background-dark"></div>
                </div>
                <div>
                    <h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight">لوحة التحكم</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs font-medium">مرحباً، {{ user.name?.split(' ')[0] }}</p>
                </div>
            </div>
            <div class="flex items-center justify-end">
                <button @click="showNotificationsModal = true; loadNotifications()" class="relative flex items-center justify-center rounded-full size-10 bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined text-[24px]">notifications</span>
                    <span v-if="unreadNotificationsCount > 0" class="absolute top-2 right-2.5 size-2 bg-red-500 rounded-full"></span>
                </button>
            </div>
        </div>
        
        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-24">
            <PullToRefresh :onRefresh="triggerRefresh" class="h-full">
            <!-- Segmented Control Tabs -->
            <div class="px-4 py-3 sticky top-0 z-10 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-sm">
                <div class="flex h-12 w-full items-center justify-center rounded-xl bg-slate-200 dark:bg-slate-800 p-1">
                    <label @click="switchToTab('available')" class="group relative flex cursor-pointer h-full flex-1 items-center justify-center overflow-hidden rounded-lg px-2 transition-all duration-200">
                        <span class="relative z-10 truncate text-sm font-semibold transition-colors" :class="activeTab === 'available' ? 'text-white' : 'text-slate-500 dark:text-slate-400'">الطلبات المتاحة</span>
                        <div v-if="activeTab === 'available'" class="absolute inset-0 z-0 rounded-lg bg-primary shadow-sm"></div>
                    </label>
                    <label @click="switchToTab('myjobs')" class="group relative flex cursor-pointer h-full flex-1 items-center justify-center overflow-hidden rounded-lg px-2 transition-all duration-200">
                        <span class="relative z-10 truncate text-sm font-semibold transition-colors" :class="activeTab === 'myjobs' ? 'text-white' : 'text-slate-500 dark:text-slate-400'">أعمالي</span>
                        <div v-if="activeTab === 'myjobs'" class="absolute inset-0 z-0 rounded-lg bg-primary shadow-sm"></div>
                    </label>
                    <label @click="switchToTab('reviews')" class="group relative flex cursor-pointer h-full flex-1 items-center justify-center overflow-hidden rounded-lg px-2 transition-all duration-200">
                        <span class="relative z-10 truncate text-sm font-semibold transition-colors" :class="activeTab === 'reviews' ? 'text-white' : 'text-slate-500 dark:text-slate-400'">التقييمات</span>
                        <div v-if="activeTab === 'reviews'" class="absolute inset-0 z-0 rounded-lg bg-primary shadow-sm"></div>
                    </label>
                </div>
            </div>
            
            <!-- Stats Overview -->
            <div class="px-4 mb-2 grid grid-cols-2 gap-3">
                <div class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-green-500 text-xl">payments</span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">الأرباح اليوم</span>
                    </div>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">{{ stats.today_earnings || 0 }} ج.م</p>
                </div>
                <div class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="material-symbols-outlined text-primary text-xl">star</span>
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">التقييم</span>
                    </div>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">{{ (stats.average_rating ? Number(stats.average_rating).toFixed(1) : '0.0') }} <span class="text-xs font-normal text-slate-400">/ 5.0</span></p>
                </div>
            </div>
            
            <!-- Section Header -->
            <div class="px-4 pt-2 pb-2 flex justify-between items-end">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ activeTab === 'available' ? 'فرص جديدة' : activeTab === 'myjobs' ? 'أعمالي الحالية' : 'آراء العملاء' }}</h3>
            </div>
            
            <!-- Card List -->
            <div class="flex flex-col gap-4 px-4 pb-4">
                <div v-if="loading" class="text-center py-8 text-slate-400">
                    <span class="material-symbols-outlined text-4xl animate-spin">progress_activity</span>
                </div>
                
                <!-- Available Requests -->
                <template v-else-if="activeTab === 'available'">
                    <div v-if="availableJobs.length === 0" class="flex flex-col items-center justify-center p-8 text-center opacity-60">
                        <div class="size-16 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-3xl text-slate-400">check_circle</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">لا توجد طلبات متاحة حالياً.</p>
                    </div>
                    
                    <div v-for="job in availableJobs" :key="job.id" class="flex flex-col rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden group">
                        <div class="p-4 flex flex-col gap-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="bg-white/90 dark:bg-slate-900/90 backdrop-blur px-2.5 py-1 rounded-full flex items-center gap-1 shadow-sm border border-slate-100 dark:border-slate-700">
                                            <span class="material-symbols-outlined text-primary text-[16px]">{{ job.category?.icon }}</span>
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ job.category?.name }}</span>
                                        </div>
                                    </div>
                                    <h3 class="text-slate-900 dark:text-white text-lg font-bold leading-tight mb-1">{{ job.resident?.name }}</h3>
                                    <div class="flex flex-col gap-0.5 text-slate-500 dark:text-slate-400">
                                        <div class="flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                                            <p class="text-xs font-medium">{{ job.resident?.block_no ? `مبنى ${job.resident.block_no}، الطابق ${job.resident.floor || '-'}، شقة ${job.resident.apt_no}` : 'العنوان غير محدد' }}</p>
                                        </div>
                                        <div v-if="job.resident?.compound" class="flex items-center gap-1 text-xs px-1">
                                            <span class="material-symbols-outlined text-[14px]">apartment</span>
                                            <span>{{ job.resident.compound.name }}</span>
                                            <a v-if="job.resident.compound.location_url" :href="job.resident.compound.location_url" target="_blank" class="text-primary hover:underline flex items-center gap-0.5 mr-2 font-bold" @click.stop>
                                                <span class="material-symbols-outlined text-[14px]">map</span>
                                                الموقع
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-slate-900/60 backdrop-blur px-2 py-1 rounded-md text-xs font-medium text-white flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                                    {{ formatDate(job.created_at) }}
                                </div>
                            </div>
                            <div v-if="job.delivery_method" class="flex items-center justify-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl w-full">
                                <span class="material-symbols-outlined text-[16px]">directions_car</span>
                                <span>{{ job.delivery_method }}</span>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-700/50 p-3 rounded-lg border border-slate-100 dark:border-slate-700/50">
                                <p class="text-slate-600 dark:text-slate-300 text-sm line-clamp-2">{{ job.notes || 'لا توجد تفاصيل إضافية' }}</p>
                            </div>
                            <div class="pt-1 flex gap-3">
                                <button class="flex-1 h-10 rounded-lg border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-medium text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    تجاهل
                                </button>
                                <button @click="openProposalModal(job)" class="flex-[2] h-10 rounded-lg bg-primary text-white font-medium text-sm shadow-md shadow-blue-500/20 hover:bg-blue-600 transition-colors flex items-center justify-center gap-2">
                                    <span v-if="job.proposals && job.proposals.length > 0">تعديل العرض ({{ job.proposals[0].price }} ج.م)</span>
                                    <span v-else>تقديم عرض</span>
                                    <span class="material-symbols-outlined text-[18px]">{{ (job.proposals && job.proposals.length > 0) ? 'edit' : 'arrow_back' }}</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </template>
                
                <!-- My Jobs -->
                <template v-else-if="activeTab === 'myjobs'">
                    <!-- Filter Chips -->
                    <div class="flex gap-2 mb-4 overflow-x-auto no-scrollbar pb-1">
                        <button @click="myJobsFilter = 'active'" 
                            class="shrink-0 h-8 px-4 rounded-full text-sm font-semibold shadow-sm transition-colors"
                            :class="myJobsFilter === 'active' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            الأعمال النشطة
                        </button>
                        <button @click="myJobsFilter = 'completed'" 
                            class="shrink-0 h-8 px-4 rounded-full text-sm font-semibold shadow-sm transition-colors"
                            :class="myJobsFilter === 'completed' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                            مكتمل
                        </button>
                    </div>
                    
                    <div v-if="filteredMyJobs.length === 0" class="flex flex-col items-center justify-center p-8 text-center opacity-60">
                        <div class="size-16 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-3xl text-slate-400">work_off</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">ليس لديك أعمال {{ myJobsFilter === 'active' ? 'نشطة' : 'مكتملة' }}.</p>
                    </div>

                    <div v-for="job in filteredMyJobs" :key="job.id" class="flex flex-col rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden p-4 gap-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-bold text-primary uppercase tracking-wider mb-1 block">{{ job.category?.name }}</span>
                                <h4 class="font-bold text-slate-900 dark:text-white text-lg">{{ job.resident?.name }}</h4>
                                <a :href="`tel:${job.resident?.phone}`" class="flex items-center gap-1 text-sm text-slate-500 mt-1 hover:text-primary">
                                    <span class="material-symbols-outlined text-[16px]">call</span>
                                    {{ job.resident?.phone }}
                                </a>
                                <div class="mt-2 text-slate-500 dark:text-slate-400 text-xs">
                                     <p class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">location_on</span>
                                        {{ job.resident?.block_no ? `مبنى ${job.resident.block_no}، الطابق ${job.resident.floor || '-'}، شقة ${job.resident.apt_no}` : 'العنوان غير محدد' }}
                                     </p>
                                     <div v-if="job.resident?.compound" class="flex items-center gap-1 mt-1">
                                        <span class="material-symbols-outlined text-[14px]">apartment</span>
                                        <span>{{ job.resident.compound.name }}</span>
                                        <a v-if="job.resident.compound.location_url" :href="job.resident.compound.location_url" target="_blank" class="text-primary hover:underline flex items-center gap-0.5 mr-2 font-bold" @click.stop>
                                            <span class="material-symbols-outlined text-[14px]">map</span>
                                            الموقع
                                        </a>
                                    </div>
                                </div>

                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                  :class="{
                                      'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300': job.status === 'in_progress',
                                      'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300': job.status === 'accepted_offer',
                                      'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300': job.status === 'completed'
                                  }">
                                {{ job.status === 'in_progress' ? 'جاري التنفيذ' : job.status === 'completed' ? 'مكتمل' : 'بانتظار البدء' }}
                            </span>
                        </div>
                        <div v-if="job.delivery_method" class="flex items-center justify-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl w-full">
                            <span class="material-symbols-outlined text-[16px]">directions_car</span>
                            <span>{{ job.delivery_method }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
                            <span class="font-bold text-xl text-emerald-600">{{ job.accepted_proposal?.price }} ج.م</span>
                            <div v-if="job.status !== 'completed'" class="flex gap-2">
                                <button v-if="job.status === 'accepted_offer'" @click="updateJobStatus(job.id, 'in_progress')"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold shadow-md hover:bg-blue-700 transition-colors">بدء العمل</button>
                                <button v-if="job.status === 'in_progress'" @click="updateJobStatus(job.id, 'completed')"
                                        class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-bold shadow-md hover:bg-green-700 transition-colors">إكمال</button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Reviews Tab -->
                <template v-else-if="activeTab === 'reviews'">
                     <div v-if="reviews.length === 0" class="flex flex-col items-center justify-center p-8 text-center opacity-60">
                        <div class="size-16 rounded-full bg-slate-200 dark:bg-slate-800 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-3xl text-slate-400">star_rate</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">لا توجد تقييمات بعد.</p>
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="review in reviews" :key="review.id" class="flex flex-col rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden p-4 gap-3">
                             <div class="flex justify-between items-start">
                                <div class="flex items-center gap-3">
                                     <div class="size-10 rounded-full bg-slate-100 dark:bg-slate-700 bg-cover bg-center" :style="`background-image: url('${review.request?.resident?.photo || 'https://ui-avatars.com/api/?name=' + review.request?.resident?.name}');`"></div>
                                     <div>
                                         <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ review.request?.resident?.name }}</h4>
                                         <p class="text-xs text-slate-400">{{ formatDate(review.created_at) }}</p>
                                     </div>
                                </div>
                                <div class="flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-lg">
                                    <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ review.rating }}</span>
                                    <span class="material-symbols-outlined text-[16px] text-amber-500 filled">star</span>
                                </div>
                             </div>
                             <p v-if="review.comment" class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg relative">
                                <span class="material-symbols-outlined absolute top-1 right-2 text-slate-200 dark:text-slate-700 text-3xl opacity-30">format_quote</span>
                                <span class="relative">{{ review.comment }}</span>
                             </p>
                             <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-slate-100 dark:bg-slate-700 rounded text-slate-500">{{ review.request?.category?.name }}</span>
                             </div>
                        </div>
                        
                        <button v-if="hasMoreReviews" @click="loadMoreReviews" class="w-full py-3 text-sm font-bold text-primary bg-primary/5 hover:bg-primary/10 rounded-xl transition-colors">
                            تحميل المزيد
                        </button>
                    </div>
                </template>
            </div>
            </PullToRefresh>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 pb-safe z-40">
            <div class="flex items-center justify-around h-16 max-w-md mx-auto">
                <Link href="/dashboard" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-primary">
                    <span class="material-symbols-outlined filled">home</span>
                    <span class="text-[10px] font-medium">الرئيسية</span>
                </Link>
                <Link href="/settings" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined">person</span>
                    <span class="text-[10px] font-medium">الحساب</span>
                </Link>
            </div>
        </nav>

        <!-- Proposal Modal -->
        <Modal :show="showProposalModal" title="تقديم عرض سعر" @close="showProposalModal = false">
            <div class="space-y-4">

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-bold text-slate-900 dark:text-white">السعر المقترح (ج.م)</label>
                    <input v-model="proposalForm.price" type="number" class="w-full h-12 px-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary/50" placeholder="0.00" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-bold text-slate-900 dark:text-white">ملاحظات العرض</label>
                    <textarea v-model="proposalForm.notes" class="w-full h-24 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-primary/50 resize-none" placeholder="اكتب تفاصيل عرضك هنا..."></textarea>
                </div>
                <button @click="submitProposal" :disabled="submittingProposal || !proposalForm.price" class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-2">
                    <span v-if="submittingProposal" class="material-symbols-outlined animate-spin">progress_activity</span>
                    <span>إرسال العرض</span>
                </button>
            </div>
        </Modal>

        <!-- Notifications Modal -->
        <Modal :show="showNotificationsModal" title="الإشعارات" @close="showNotificationsModal = false">
            <div class="flex items-center justify-between mb-4 px-1" v-if="notifications.length > 0">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">سجل التنبيهات</h3>
                <button v-if="unreadNotificationsCount > 0" @click="markAllNotificationsAsRead" class="text-xs font-bold text-primary hover:underline">تحديد الكل كمقروء</button>
            </div>
            <div class="space-y-3">
                <div v-if="loadingNotifications" class="text-center py-12 text-slate-400">
                    <span class="material-symbols-outlined text-5xl animate-spin">progress_activity</span>
                    <p class="mt-3 text-sm font-medium">جاري تحميل الإشعارات...</p>
                </div>
                <div v-else-if="notifications.length === 0" class="text-center py-8 text-slate-400">
                    <span class="material-symbols-outlined text-4xl">notifications_off</span>
                    <p class="mt-2">لا توجد إشعارات</p>
                </div>
                <div v-for="n in notifications" :key="n.id" @click="handleNotificationClick(n)" 
                     class="flex gap-3 p-3 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                     :class="n.is_read ? 'bg-slate-50 dark:bg-slate-800/50' : 'bg-primary/5 border border-primary/20'">
                    <div class="size-10 rounded-full flex items-center justify-center flex-shrink-0 bg-white shadow-sm text-primary">
                        <span class="material-symbols-outlined">notifications</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold">{{ n.title }}</h4>
                        <p class="text-xs text-slate-500">{{ n.body }}</p>
                        <p class="text-[10px] text-slate-400 mt-1">{{ formatDate(n.created_at) }}</p>
                    </div>
                </div>
                <button v-if="nextNotificationsUrl" @click="loadMoreNotifications" :disabled="loadingMoreNotifications" class="w-full mt-2 py-3 text-sm font-bold text-primary bg-primary/10 dark:bg-primary/20 hover:bg-primary/20 rounded-xl transition-colors flex items-center justify-center gap-2">
                    <span v-if="loadingMoreNotifications" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                    <span>{{ loadingMoreNotifications ? 'جاري التحميل...' : 'تحميل المزيد' }}</span>
                </button>
            </div>
        </Modal>

        <!-- Confirmation Modal -->
        <Modal :show="showConfirmModal" title="تأكيد الإجراء" @close="showConfirmModal = false">
            <div class="space-y-4">
                <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl flex gap-3 text-amber-800 dark:text-amber-200">
                    <span class="material-symbols-outlined text-2xl flex-shrink-0">warning</span>
                    <p class="text-sm font-medium leading-relaxed">{{ confirmMessage }}</p>
                </div>
                <div class="flex gap-3">
                    <button @click="showConfirmModal = false" class="flex-1 py-3 text-slate-600 dark:text-slate-300 font-bold bg-slate-100 dark:bg-slate-700/50 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-colors">
                        إلغاء
                    </button>
                    <button @click="confirmAction" :disabled="processingConfirm" class="flex-1 py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/20 flex items-center justify-center gap-2 hover:bg-blue-600 transition-colors">
                        <span v-if="processingConfirm" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        <span>تأكيد</span>
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<script>
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import Toast from '@/Components/Toast.vue';
import PullToRefresh from '@/Components/PullToRefresh.vue';
import { requestNotificationPermission } from '@/firebase';

export default {
    name: 'ProviderDashboard',
    components: { Head, Link, Modal, Toast, PullToRefresh },
    data() {
        return {
            activeTab: localStorage.getItem('provider_current_tab') || 'available',
            availableJobs: [],
            myJobs: [],
            reviews: [],
            reviewsPage: 1,
            hasMoreReviews: false,
            loading: true,
            stats: {},
            showProposalModal: false,
            showNotificationsModal: false,
            notifications: [],
            loadingNotifications: false,
            nextNotificationsUrl: null,
            loadingMoreNotifications: false,
            unreadNotificationsCount: 0,
            selectedJob: null,
            proposalForm: { price: '', notes: '' },

            submittingProposal: false,
            editingProposal: null,
            // Confirmation modal
            showConfirmModal: false,
            confirmMessage: '',
            confirmAction: null,
            processingConfirm: false,
            // My Jobs filter
            myJobsFilter: 'active',
        };
    },
    computed: {
        user() {
            const page = usePage();
            return page.props.auth ? page.props.auth.user : {};
        },
        filteredMyJobs() {
            if (this.myJobsFilter === 'active') {
                return this.myJobs.filter(job => job.status === 'accepted_offer' || job.status === 'in_progress');
            }
            return this.myJobs.filter(job => job.status === this.myJobsFilter);
        },
    },
    mounted() {
        this.loadStats();
        this.loadUnreadCount();
        this.switchToTab(this.activeTab);
        // Lazy load notification permission request
        setTimeout(() => requestNotificationPermission(), 1000);

        // Handle direct request focus (e.g. from push notifications)
        const params = new URLSearchParams(window.location.search);
        if (params.has('request_id')) {
            this.focusRequest(parseInt(params.get('request_id')));
        }
        
        // Listen for incoming push notifications while app is open
        window.addEventListener('fcm-message', this.handleFcmMessage);
    },
    beforeUnmount() {
        window.removeEventListener('fcm-message', this.handleFcmMessage);
    },
    methods: {
        handleFcmMessage(event) {
            // Update unread count
            this.unreadNotificationsCount++;
            
            // Reload notifications if modal is open
            if (this.showNotificationsModal) {
                this.loadNotifications();
            }
            
            // Reload available jobs if it's a new request notification
            const payload = event.detail;
            if (payload?.data?.request_id) {
                this.loadAvailableJobs();
                this.loadMyJobs();
            }
        },
        async triggerRefresh() {
            // Refresh in parallel but don't block UI
            this.loadStats();
            this.loadUnreadCount();
            this.loadAvailableJobs();
            this.loadMyJobs();
        },
        async focusRequest(id) {
            // 1. Try to find in My Jobs first (active/completed)
            if (this.myJobs.length === 0) await this.loadMyJobs();
            const myJob = this.myJobs.find(j => j.id === id);
            if (myJob) {
                this.switchToTab('myjobs');
                this.myJobsFilter = (myJob.status === 'completed') ? 'completed' : 'active';
                return;
            }

            // 2. Try to find in Available Jobs
            if (this.availableJobs.length === 0) await this.loadAvailableJobs();
            const availableJob = this.availableJobs.find(j => j.id === id);
            if (availableJob) {
                this.switchToTab('available');
                this.openProposalModal(availableJob);
                return;
            }
        },
        switchToTab(tab) {
            this.activeTab = tab;
            localStorage.setItem('provider_current_tab', tab);
            if (tab === 'available') this.loadAvailableJobs();
            else if (tab === 'myjobs') this.loadMyJobs();
            else if (tab === 'reviews') this.loadReviews(true);
        },
        async loadStats() {
            try {
                const response = await axios.get('/api/provider/stats');
                if (response.data.success) {
                    this.stats = response.data.stats;
                }
            } catch (e) {}
        },
        async loadAvailableJobs() {
            this.loading = true;
            try {
                const response = await axios.get('/api/provider/available-requests');
                if (response.data.success) {
                    this.availableJobs = response.data.requests.data;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async loadMyJobs() {
            this.loading = true;
            try {
                const response = await axios.get('/api/provider/my-jobs');
                if (response.data.success) {
                    this.myJobs = response.data.jobs.data;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async loadReviews(reset = true) {
            if (reset) {
                this.reviewsPage = 1;
                this.reviews = [];
            }
            this.loading = true;
            try {
                const response = await axios.get(`/api/provider/reviews?page=${this.reviewsPage}`);
                if (response.data.success) {
                    if (reset) {
                        this.reviews = response.data.reviews.data;
                    } else {
                        this.reviews = [...this.reviews, ...response.data.reviews.data];
                    }
                    this.hasMoreReviews = !!response.data.reviews.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        loadMoreReviews() {
            this.reviewsPage++;
            this.loadReviews(false);
        },
        openProposalModal(job) {
            this.selectedJob = job;
            if (job.proposals && job.proposals.length > 0) {
                this.editingProposal = job.proposals[0];
                this.proposalForm = {
                    price: this.editingProposal.price,
                    notes: this.editingProposal.notes
                };
            } else {
                this.editingProposal = null;
                this.proposalForm = { price: '', notes: '' };
            }
            this.showProposalModal = true;
        },
        async submitProposal() {
            this.submittingProposal = true;
            try {
                let response;
                if (this.editingProposal) {
                     response = await axios.put(`/api/proposals/${this.editingProposal.id}`, this.proposalForm);
                } else {
                     response = await axios.post(`/api/requests/${this.selectedJob.id}/proposals`, this.proposalForm);
                }
                
                if (response.data.success) {
                    window.showToast(this.editingProposal ? 'تم تعديل العرض بنجاح' : 'تم تقديم العرض بنجاح', 'success');
                    this.showProposalModal = false;
                    this.loadAvailableJobs();
                } else {
                    window.showToast(response.data.message, 'error');
                }
            } catch (e) {
                window.showToast('حدث خطأ', 'error');
                console.error(e);
            } finally {
                this.submittingProposal = false;
            }
        },
        updateJobStatus(jobId, status) {
            this.confirmMessage = status === 'in_progress' 
                ? 'هل تود بدء العمل الآن؟ سيتم إشعار العميل بذلك.' 
                : 'هل تود تأكيد إكمال العمل؟ سيتم إشعار العميل وإتاحة التقييم.';
            
            this.showConfirmModal = true;
            this.confirmAction = async () => {
                this.processingConfirm = true;
                try {
                    // Find the job to get proposal id
                    const job = this.myJobs.find(j => j.id === jobId);
                    let url = '';
                    
                    if (status === 'in_progress') {
                        // Use proposal start route
                        url = `/api/proposals/${job.accepted_proposal?.id}/start`;
                    } else {
                        // Use complete route
                        url = `/api/requests/${jobId}/complete`;
                    }
                    
                    const response = await axios.post(url);
                    
                    if (response.data.success) {
                        window.showToast('تم تحديث حالة العمل', 'success');
                        this.loadMyJobs();
                        if (status === 'completed') this.loadStats();
                        this.showConfirmModal = false;
                    } else {
                        window.showToast(response.data.message || 'حدث خطأ', 'error');
                        this.showConfirmModal = false;
                    }
                } catch (e) {
                    console.error(e);
                    window.showToast('حدث خطأ', 'error');
                    this.showConfirmModal = false;
                } finally {
                    this.processingConfirm = false;
                }
            };
        },
        async handleNotificationClick(n) {
             if (!n.is_read) {
                 try { axios.post(`/api/notifications/${n.id}/read`); } catch(e) {}
                 n.is_read = true;
                 this.unreadNotificationsCount = Math.max(0, this.unreadNotificationsCount - 1);
             }
             this.showNotificationsModal = false;
             if (n.data && n.data.request_id) {
                 this.focusRequest(n.data.request_id);
             }
        },
        async loadNotifications() {
            this.loadingNotifications = true;
            try {
                const response = await axios.get('/api/notifications');
                if (response.data.success) {
                    this.notifications = response.data.notifications.data;
                    this.nextNotificationsUrl = response.data.notifications.next_page_url || null;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingNotifications = false;
            }
        },
        async loadMoreNotifications() {
            if (!this.nextNotificationsUrl || this.loadingMoreNotifications) return;
            this.loadingMoreNotifications = true;
            try {
                const response = await axios.get(this.nextNotificationsUrl);
                if (response.data.success) {
                    this.notifications = [...this.notifications, ...response.data.notifications.data];
                    this.nextNotificationsUrl = response.data.notifications.next_page_url || null;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingMoreNotifications = false;
            }
        },
        async markAllNotificationsAsRead() {
            try {
                const response = await axios.post('/api/notifications/read-all');
                if (response.data.success) {
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadNotificationsCount = 0;
                    window.showToast('تم تحديد الكل كمقروء', 'success');
                }
            } catch (e) {
                console.error(e);
                window.showToast('حدث خطأ', 'error');
            }
        },
        async loadUnreadCount() {
            try {
                const response = await axios.get('/api/notifications/unread-count');
                if (response.data.success) {
                    this.unreadNotificationsCount = response.data.count;
                }
            } catch (e) {}
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('ar-EG', {
                month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        },
    },
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.filled { font-variation-settings: 'FILL' 1; }
</style>
