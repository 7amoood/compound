<template>
    <Head title="لوحة التحكم - ساكن" />
    <Toast />
    <div class="relative flex h-[100dvh] w-full flex-col overflow-hidden max-w-md mx-auto bg-background-light dark:bg-background-dark font-display" dir="rtl">
        <!-- Top App Bar & Profile Header Combined -->
        <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md border-b border-slate-100 dark:border-slate-800/10 transition-all duration-200">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 ring-2 ring-white dark:ring-slate-700 shadow-sm" :style="`background-image: url('${user.photo || 'https://ui-avatars.com/api/?name=' + user.name}');`"></div>
                    <div class="flex flex-col">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">ساكن</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white leading-none">
                            {{ (user.block_no && user.apt_no) ? `مبنى ${user.block_no}${user.floor ? '، الطابق ' + user.floor : ''}، شقة ${user.apt_no}` : 'يرجى تحديث العنوان' }}
                        </span>
                    </div>
                </div>
                <button @click="showNotificationsModal = true; loadNotifications()" class="flex items-center justify-center size-10 rounded-full bg-white dark:bg-surface-dark text-slate-600 dark:text-slate-300 shadow-sm border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors relative">
                    <span class="material-symbols-outlined text-[24px]">notifications</span>
                    <span v-if="unreadNotificationsCount > 0" class="absolute top-2 right-2 size-2 bg-red-500 rounded-full border border-white dark:border-surface-dark"></span>
                </button>
            </div>
        </header>

        <!-- Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-24">
            <PullToRefresh :onRefresh="triggerRefresh">
                <main class="flex flex-col gap-6 pt-4">
                    <!-- Welcome Title -->
                    <div class="px-4">
                        <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">صباح الخير،<br/>{{ user.name?.split(' ')[0] }}</h1>
                    </div>

                    <!-- Quick Actions Grid -->
                    <div class="px-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">خدمات سريعة</h2>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <button v-for="(cat, index) in categories" :key="cat.id" @click="newRequestForm.service_category_id = cat.id; showNewRequestModal = true" class="flex flex-col items-center gap-2 group">
                                <div class="flex items-center justify-center size-14 rounded-2xl border group-hover:scale-105 transition-transform duration-200 shadow-sm" :class="getServiceColor(index)">
                                    <span class="material-symbols-outlined text-[28px]">{{ cat.icon }}</span>
                                </div>
                                <span class="text-xs font-medium text-slate-600 dark:text-slate-300">{{ cat.name }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Primary Action Button -->
                    <div class="px-4">
                        <button @click="showNewRequestModal = true" class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-blue-600 text-white font-bold h-14 rounded-xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all duration-200">
                            <span class="material-symbols-outlined">add_circle</span>
                            <span>طلب خدمة جديدة</span>
                        </button>
                    </div>

                    <!-- Service History Section -->
                    <div class="flex flex-col gap-4">
                        <!-- Header & Filters -->
                        <div class="flex flex-col gap-3">
                            <div class="px-4 flex items-center justify-between">
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">سجل الطلبات</h2>
                                <a class="text-sm font-semibold text-primary hover:text-blue-500" href="#">عرض الكل</a>
                            </div>
                            <!-- Filter Chips -->
                            <div class="flex gap-2 px-4 overflow-x-auto no-scrollbar pb-1">
                                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                    class="shrink-0 h-8 px-4 rounded-full text-sm font-semibold shadow-sm transition-colors"
                                    :class="activeTab === tab.id ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'bg-white dark:bg-surface-dark border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                                    {{ tab.label }}
                                </button>
                            </div>
                        </div>

                        <!-- List of Cards -->
                        <div class="flex flex-col gap-3 px-4">
                            <div v-if="loading" class="text-center py-8 text-slate-400">
                                <span class="material-symbols-outlined text-4xl animate-spin">progress_activity</span>
                            </div>
                            <div v-else-if="requests.length === 0" class="text-center py-8 text-slate-400">
                                <span class="material-symbols-outlined text-4xl">inbox</span>
                                <p class="mt-2">لا توجد طلبات</p>
                            </div>

                            <div v-for="(req, index) in requests" :key="req.id" @click="openRequestDetails(req.id)"
                                 class="group flex flex-col bg-white dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 active:bg-slate-50 dark:active:bg-slate-800 transition-colors cursor-pointer">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center justify-center size-12 rounded-xl shrink-0" :class="getServiceColor(index)">
                                            <span class="material-symbols-outlined">{{ req.category?.icon }}</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight">{{ req.category?.name }}</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ formatDate(req.created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="shrink-0">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                              :class="{
                                                  'bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300': req.status === 'pending',
                                                  'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300': req.status === 'accepted_offer',
                                                  'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300': req.status === 'in_progress',
                                                  'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300': req.status === 'completed',
                                                  'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300': req.status === 'cancelled'
                                              }">
                                            {{ 
                                                req.status === 'pending' ? 'قيد الانتظار' : 
                                                req.status === 'accepted_offer' ? 'تم قبول عرض' : 
                                                req.status === 'in_progress' ? 'جاري التنفيذ' : 
                                                req.status === 'completed' ? 'مكتمل' :
                                                req.status === 'cancelled' ? 'ملغي' : req.status
                                            }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="req.delivery_method" class="mt-3 flex items-center justify-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 px-3 py-2 rounded-xl w-full">
                                    <span class="material-symbols-outlined text-[16px]">directions_car</span>
                                    <span>{{ req.delivery_method }}</span>
                                </div>

                                <!-- Rating Button -->
                                <button v-if="req.status === 'completed' && !req.review" @click.stop="openRating(req)"
                                        class="mt-3 w-full py-2 bg-amber-400 text-white rounded-lg text-sm font-bold shadow-sm hover:bg-amber-500 transition-colors flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[18px]">star</span>
                                    تقييم الخدمة
                                </button>
                            </div>
                        </div>
                    </div>
                </main>
            </PullToRefresh>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-surface-dark/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 pb-safe z-40">
            <div class="flex items-center justify-around h-16 max-w-md mx-auto">
                <Link href="/dashboard" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-primary">
                    <span class="material-symbols-outlined fill-1">home</span>
                    <span class="text-[10px] font-bold">الرئيسية</span>
                </Link>
                <Link href="/settings" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined">person</span>
                    <span class="text-[10px] font-medium">الحساب</span>
                </Link>
            </div>
        </nav>

        <!-- New Request Modal -->
        <Modal :show="showNewRequestModal" title="طلب خدمة جديدة" @close="showNewRequestModal = false">
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">نوع الخدمة</label>
                        <div class="relative">
                            <select v-model="newRequestForm.service_category_id" class="w-full appearance-none rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4 pr-10 text-base font-medium text-slate-900 dark:text-white focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary transition-colors">
                                <option disabled value="">اختر نوع الخدمة</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-4 text-slate-500">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2" v-if="availableDeliveryMethods.length > 0">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">طريقة تقديم الخدمة</label>
                        <div class="flex flex-col gap-3">
                            <div v-for="(method, index) in availableDeliveryMethods" :key="index"
                                 @click="newRequestForm.delivery_method = method"
                                 class="cursor-pointer rounded-xl border p-3 text-center transition-all w-full"
                                 :class="newRequestForm.delivery_method === method ? 'border-primary bg-primary/5 text-primary font-bold' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 hover:border-slate-300'">
                                {{ method }}
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">تفاصيل الطلب</label>
                        <textarea v-model="newRequestForm.notes" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4 text-base font-normal text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary resize-none transition-colors" placeholder="اكتب تفاصيل المشكلة..." rows="4"></textarea>
                    </div>
                    <button @click="submitNewRequest" :disabled="submittingRequest || !newRequestForm.service_category_id" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-4 text-base font-bold text-white shadow-lg shadow-primary/25 hover:bg-primary/90 focus:outline-none focus:ring-4 focus:ring-primary/20 transition-all active:scale-[0.98] disabled:opacity-50">
                        <span v-if="submittingRequest" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span>تأكيد الطلب</span>
                        <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span>
                    </button>
                </div>
            </Modal>

            <!-- Request Details Modal -->
            <Modal :show="showRequestDetailsModal" title="تفاصيل الطلب والعروض" @close="showRequestDetailsModal = false">
                <div v-if="selectedRequest" class="space-y-6">
                    <!-- Header Info -->
                    <div class="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div class="flex items-center justify-center size-14 rounded-2xl bg-slate-50 dark:bg-slate-800 text-primary">
                            <span class="material-symbols-outlined text-[28px]">{{ selectedRequest.category?.icon }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ selectedRequest.category?.name }}</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ formatDate(selectedRequest.created_at) }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold mt-1" :class="{
                                'bg-orange-100 text-orange-700': selectedRequest.status === 'pending',
                                'bg-blue-100 text-blue-700': selectedRequest.status === 'accepted_offer',
                                'bg-purple-100 text-purple-700': selectedRequest.status === 'in_progress',
                                'bg-emerald-100 text-emerald-700': selectedRequest.status === 'completed',
                                'bg-red-100 text-red-700': selectedRequest.status === 'cancelled'
                            }">{{ 
                                selectedRequest.status === 'pending' ? 'قيد الانتظار' : 
                                selectedRequest.status === 'accepted_offer' ? 'تم قبول عرض' : 
                                selectedRequest.status === 'in_progress' ? 'جاري التنفيذ' : 
                                selectedRequest.status === 'completed' ? 'مكتمل' :
                                selectedRequest.status === 'cancelled' ? 'ملغي' : selectedRequest.status
                            }}</span>
                        </div>
                    </div>

                    <!-- Accepted Proposal (if any) -->
                    <div v-if="selectedRequest.accepted_proposal" class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800">
                        <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2">العرض المقبول</h4>
                        <div class="flex items-center justify-between">
                             <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-white dark:bg-slate-800 bg-cover bg-center" :style="`background-image: url('${selectedRequest.accepted_proposal.provider?.photo || 'https://ui-avatars.com/api/?name=' + selectedRequest.accepted_proposal.provider?.name}');`"></div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ selectedRequest.accepted_proposal.provider?.name }}</p>
                                    <p class="text-xs text-slate-500">{{ selectedRequest.accepted_proposal.provider?.phone }}</p>
                                </div>
                            </div>
                            <span class="font-bold text-lg text-emerald-600">{{ selectedRequest.accepted_proposal.price }} ج.م</span>
                        </div>
                    </div>

                    <!-- Proposals List -->
                    <div v-if="selectedRequest.status === 'pending'" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-900 dark:text-white">العروض المقدمة ({{ currentProposals.length }})</h4>
                            <select v-model="proposalsSort" class="text-sm border-0 bg-transparent text-slate-500 focus:ring-0 cursor-pointer">
                                <option value="default">الأفضل (تقييم وسعر)</option>
                                <option value="price_asc">الأقل سعراً</option>
                                <option value="rating_desc">الأعلى تقييماً</option>
                            </select>
                        </div>

                        <div v-if="loadingProposals" class="text-center py-6 text-slate-400">
                             <span class="material-symbols-outlined animate-spin text-2xl">progress_activity</span>
                        </div>
                        <div v-else-if="currentProposals.length === 0" class="text-center py-6 text-slate-400 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            <p class="text-sm">لم يتم تقديم عروض بعد</p>
                        </div>
                        <div v-else class="space-y-3">
                            <div v-for="proposal in sortedProposals" :key="proposal.id" class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-full bg-slate-100 dark:bg-slate-700 bg-cover bg-center" :style="`background-image: url('${proposal.provider?.photo || 'https://ui-avatars.com/api/?name=' + proposal.provider?.name}');`"></div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ proposal.provider?.name }}</p>
                                            <div class="flex items-center gap-1 text-xs text-amber-500">
                                                <span class="material-symbols-outlined text-[14px] filled">star</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ proposal.provider?.rating_average || '0.0' }}</span>
                                                <span class="text-slate-400">({{ proposal.provider?.rating_count || 0 }})</span>
                                            </div>
                                        </div>
                                    </div>
                                    <span class="font-bold text-lg text-emerald-600">{{ proposal.price }} ج.م</span>
                                </div>
                                <p v-if="proposal.notes" class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/50 p-2 rounded-lg mb-3">
                                    {{ proposal.notes }}
                                </p>
                                <button @click="acceptProposal(proposal)" class="w-full py-2.5 bg-primary text-white font-bold rounded-lg text-sm hover:bg-blue-600 transition-colors shadow-sm">
                                    قبول العرض
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons if Completed -->
                    <div v-if="selectedRequest.status === 'completed' && !selectedRequest.review" class="pt-2">
                         <button @click="openRating(selectedRequest)" class="w-full py-3 bg-amber-400 text-white font-bold rounded-xl flex items-center justify-center gap-2 hover:bg-amber-500 transition-colors">
                            <span class="material-symbols-outlined">star</span>
                            تقييم الخدمة المكتملة
                        </button>
                    </div>
                    
                    <!-- Show Review if Completed and Reviewed -->
                    <div v-if="selectedRequest.status === 'completed' && selectedRequest.review" class="pt-2">
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-xl border border-amber-200 dark:border-amber-800">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm">تقييمك للخدمة</h4>
                                <div class="flex items-center gap-1 bg-amber-100 dark:bg-amber-900/40 px-2 py-1 rounded-lg">
                                    <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ selectedRequest.review.rating }}</span>
                                    <span class="material-symbols-outlined text-[16px] text-amber-500 filled">star</span>
                                </div>
                            </div>
                            <p v-if="selectedRequest.review.comment" class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                                {{ selectedRequest.review.comment }}
                            </p>
                            <p class="text-xs text-slate-400 mt-2">{{ formatDate(selectedRequest.review.created_at) }}</p>
                        </div>
                    </div>
                    
                    <!-- Cancel Button if Pending -->
                    <div v-if="selectedRequest.status === 'pending'" class="pt-2">
                         <button @click="confirmCancelRequest(selectedRequest)" class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl flex items-center justify-center gap-2 transition-colors">
                            <span class="material-symbols-outlined">cancel</span>
                            إلغاء الطلب
                        </button>
                    </div>
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
                        <div class="size-10 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm bg-slate-100 text-slate-600">
                            <span class="material-symbols-outlined">notifications</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ n.title }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ n.body }}</p>
                            <p class="text-[10px] text-slate-400 mt-1">{{ formatDate(n.created_at) }}</p>
                        </div>
                        <span v-if="!n.is_read" class="size-2 bg-primary rounded-full flex-shrink-0 mt-2"></span>
                    </div>
                    <button v-if="nextNotificationsUrl" @click="loadMoreNotifications" :disabled="loadingMoreNotifications" class="w-full mt-2 py-3 text-sm font-bold text-primary bg-primary/10 dark:bg-primary/20 hover:bg-primary/20 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <span v-if="loadingMoreNotifications" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        <span>{{ loadingMoreNotifications ? 'جاري التحميل...' : 'تحميل المزيد' }}</span>
                    </button>
                </div>
            </Modal>

            <!-- Rating Modal -->
            <Modal :show="showRatingModal" title="تقييم الخدمة" @close="showRatingModal = false">
                <div class="text-center space-y-4">
                    <p class="text-slate-600 dark:text-slate-300">كيف كانت تجربتك؟</p>
                    <div class="flex justify-center gap-2" dir="ltr">
                        <button v-for="i in 5" :key="i" @click="currentRating = i" class="text-4xl transition-colors" :class="i <= currentRating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'">★</button>
                    </div>
                    <textarea v-model="ratingComment" placeholder="اكتب تعليقك هنا..." class="w-full p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border-0 focus:ring-2 focus:ring-amber-400 text-sm h-24 text-right resize-none"></textarea>
                    <button @click="submitRating" :disabled="submittingRate" class="w-full py-3 bg-primary text-white font-bold rounded-xl flex items-center justify-center gap-2">
                        <span v-if="submittingRate" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span>إرسال التقييم</span>
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
    name: 'ResidentDashboard',
    components: { Head, Link, Modal, Toast, PullToRefresh },
    data() {
        return {
            requests: [],
            categories: [],
            loading: true,
            activeTab: (new URLSearchParams(window.location.search)).get('tab') || 'pending',
            showNewRequestModal: false,
            showRequestDetailsModal: false,
            showRatingModal: false,
            showNotificationsModal: false,
            selectedRequest: null,
            currentRating: 5,
            ratingComment: '',
            submittingRate: false,
            notifications: [],
            loadingNotifications: false,
            nextNotificationsUrl: null,
            loadingMoreNotifications: false,
            unreadNotificationsCount: 0,
            newRequestForm: {
                service_category_id: '',
                delivery_method: '',
                notes: '',
            },
            submittingRequest: false,
            tabs: [
                { id: 'pending', label: 'قيد الانتظار' },
                { id: 'accepted_offer', label: 'تم قبول عرض' },
                { id: 'in_progress', label: 'جاري التنفيذ' },
                { id: 'completed', label: 'مكتمل' },
                { id: 'cancelled', label: 'ملغي' }
            ],
            currentProposals: [],
            loadingProposals: false,
            proposalsSort: 'default',
            showConfirmModal: false,
            confirmMessage: '',
            confirmAction: null,
            processingConfirm: false,
        };
    },
    computed: {
        user() {
            const page = usePage();
            return page.props.auth ? page.props.auth.user : {};
        },
        selectedServiceCategory() {
             return this.categories.find(c => c.id === this.newRequestForm.service_category_id);
        },
        availableDeliveryMethods() {
            return this.selectedServiceCategory?.delivery_methods || [];
        },
        sortedProposals() {
            let sorted = [...this.currentProposals];
            if (this.proposalsSort === 'price_asc') {
                sorted.sort((a, b) => a.price - b.price);
            } else if (this.proposalsSort === 'rating_desc') {
                sorted.sort((a, b) => (b.provider?.rating_average || 0) - (a.provider?.rating_average || 0));
            } else {
                 // Default: Rating Desc then Price Asc
                 sorted.sort((a, b) => {
                     const ratingA = a.provider?.rating_average || 0;
                     const ratingB = b.provider?.rating_average || 0;
                     if (ratingB !== ratingA) return ratingB - ratingA;
                     return a.price - b.price;
                 });
            }
            return sorted;
        }
    },
    watch: {
        activeTab(newTab) {
            const url = new URL(window.location);
            url.searchParams.set('tab', newTab);
            // Remove request_id when changing tabs
            url.searchParams.delete('request_id');
            window.history.replaceState(window.history.state, '', url);
            this.loadRequests(); // Load requests when tab changes
        },
        showRequestDetailsModal(isOpen) {
            if (!isOpen) {
                // Remove request_id from URL when closing modal
                const url = new URL(window.location);
                url.searchParams.delete('request_id');
                window.history.replaceState(window.history.state, '', url);
            }
        }
    },
    mounted() {
        const params = new URLSearchParams(window.location.search);
        
        // Handle direct request detail opening (e.g. from push notifications)
        if (params.has('request_id')) {
            this.openRequestDetails(parseInt(params.get('request_id')));
        }
        
        this.loadCategories();
        this.loadRequests();
        this.loadUnreadCount();
        // Lazy load notification permission request
        setTimeout(() => requestNotificationPermission(), 1000);
        
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
            
            // Optionally reload requests if it's a request-related notification
            const payload = event.detail;
            if (payload?.data?.request_id) {
                this.loadRequests();
            }
        },
        async triggerRefresh() {
            // Refresh in parallel but don't block UI
            this.loadRequests();
            this.loadCategories();
            this.loadUnreadCount();
        },
        getServiceColor(index) {
             const colors = [
                'bg-blue-50 text-blue-600 border-blue-100 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/50',
                'bg-emerald-50 text-emerald-600 border-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-400 dark:border-emerald-800/50',
                'bg-purple-50 text-purple-600 border-purple-100 dark:bg-purple-900/20 dark:text-purple-400 dark:border-purple-800/50',
                'bg-amber-50 text-amber-600 border-amber-100 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800/50',
                'bg-rose-50 text-rose-600 border-rose-100 dark:bg-rose-900/20 dark:text-rose-400 dark:border-rose-800/50',
                'bg-cyan-50 text-cyan-600 border-cyan-100 dark:bg-cyan-900/20 dark:text-cyan-400 dark:border-cyan-800/50',
            ];
            return colors[index % colors.length];
        },
        async loadRequests() {
            this.loading = true;
            try {
                const response = await axios.get(`/api/requests?status=${this.activeTab}`);
                if (response.data.success) {
                    this.requests = response.data.requests.data;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async loadCategories() {
            try {
                const response = await axios.get('/api/active-categories');
                if (response.data.success) {
                    this.categories = response.data.categories;
                }
            } catch (e) {
                console.error(e);
            }
        },
        async submitNewRequest() {
            this.submittingRequest = true;
            try {
                const response = await axios.post('/api/requests', this.newRequestForm);
                if (response.data.success) {
                    this.showNewRequestModal = false;
                    this.newRequestForm = { service_category_id: '', notes: '' };
                    window.showToast('تم إرسال طلبك بنجاح', 'success');
                    this.activeTab = 'pending';
                    // loadRequests called by watch
                } else {
                    window.showToast(response.data.message || 'حدث خطأ', 'error');
                }
            } catch (e) {
                window.showToast('حدث خطأ أثناء إرسال الطلب', 'error');
            } finally {
                this.submittingRequest = false;
            }
        },
        async openRequestDetails(id) {
            this.showRequestDetailsModal = true;
            
            // Update URL AFTER modal opens and pushes its state
            this.$nextTick(() => {
                const url = new URL(window.location);
                url.searchParams.set('request_id', id);
                window.history.replaceState(window.history.state, '', url);
            });

            this.loadingProposals = true;
            this.currentProposals = [];
            
            // Try to find in loaded list first
            this.selectedRequest = this.requests.find(r => r.id === id);

            try {
                // Single request - includes proposals
                const response = await axios.get(`/api/requests/${id}`);
                
                if (response.data.success) {
                    this.selectedRequest = response.data.request;
                    // Proposals are included in the request response
                    this.currentProposals = response.data.request?.proposals || [];
                }
            } catch (e) {
                console.error(e);
                window.showToast('تعذر تحميل التفاصيل', 'error');
            } finally {
                this.loadingProposals = false;
            }
        },
        acceptProposal(proposal) {
            this.confirmMessage = 'هل أنت متأكد من قبول هذا العرض؟ سيتم بدء العمل فوراً وسيتم خصم المبلغ المتفق عليه.';
            this.showConfirmModal = true;
            this.confirmAction = async () => {
                this.processingConfirm = true;
                try {
                    const response = await axios.post(`/api/proposals/${proposal.id}/accept`);
                    if (response.data.success) {
                        window.showToast('تم قبول العرض بنجاح', 'success');
                        this.showRequestDetailsModal = false;
                        this.showConfirmModal = false;
                        this.activeTab = 'accepted_offer'; 
                        this.loadRequests();
                    } else {
                        window.showToast(response.data.message, 'error');
                        this.showConfirmModal = false;
                    }
                } catch (e) {
                    window.showToast('حدث خطأ أثناء قبول العرض', 'error');
                    this.showConfirmModal = false;
                } finally {
                    this.processingConfirm = false;
                }
            };
        },
        handleNotificationClick(n) {
             // Mark as read
             if (!n.is_read) {
                 axios.post(`/api/notifications/${n.id}/read`).catch(() => {});
                 n.is_read = true;
                 this.unreadNotificationsCount = Math.max(0, this.unreadNotificationsCount - 1);
             }
             
             const requestId = n.data?.request_id;
             
             if (requestId) {
                 // 1. Preserving history state and remove modal flag BEFORE closing the prop
                 // This prevents the Modal watcher from calling history.back()
                 if (window.history.state?.modal) {
                     const newState = { ...window.history.state };
                     delete newState.modal;
                     window.history.replaceState(newState, '', window.location.href);
                 }
                 
                 // 2. Clear notifications modal
                 this.showNotificationsModal = false;
                 
                 // 3. Open request details
                 this.openRequestDetails(requestId);
             } else {
                 this.showNotificationsModal = false;
             }
        },
        openRating(request) {
            this.selectedRequest = request;
            this.showRatingModal = true;
            this.currentRating = 5;
            this.ratingComment = '';
        },
        async submitRating() {
            if (!this.selectedRequest) return;
            this.submittingRate = true;
            try {
                const response = await axios.post('/api/reviews', {
                    request_id: this.selectedRequest.id,
                    rating: this.currentRating,
                    comment: this.ratingComment
                });
                if (response.data.success) {
                    window.showToast('شكراً لتقييمك!', 'success');
                    this.showRatingModal = false;
                    this.loadRequests();
                } else {
                    window.showToast(response.data.message, 'error');
                }
            } catch (e) {
                window.showToast('حدث خطأ', 'error');
            } finally {
                this.submittingRate = false;
            }
        },
        confirmCancelRequest(request) {
            this.confirmMessage = 'هل أنت متأكد من إلغاء هذا الطلب؟ لن تتمكن من التراجع عن هذا الإجراء.';
            this.showConfirmModal = true;
            this.confirmAction = async () => {
                this.processingConfirm = true;
                try {
                    const response = await axios.post(`/api/requests/${request.id}/cancel`);
                    if (response.data.success) {
                        window.showToast('تم إلغاء الطلب بنجاح', 'success');
                        this.showRequestDetailsModal = false;
                        this.showConfirmModal = false;
                        this.activeTab = 'cancelled';
                        this.loadRequests();
                    } else {
                        window.showToast(response.data.message || 'حدث خطأ', 'error');
                        this.showConfirmModal = false;
                    }
                } catch (e) {
                    console.error(e);
                    window.showToast('حدث خطأ أثناء إلغاء الطلب', 'error');
                    this.showConfirmModal = false;
                } finally {
                    this.processingConfirm = false;
                }
            };
        },
        async loadNotifications() {
            this.loadingNotifications = true;
            try {
                const response = await axios.get('/api/notifications');
                if (response.data.success) {
                    this.notifications = response.data.notifications.data || response.data.notifications;
                    this.nextNotificationsUrl = response.data.notifications.next_page_url || null;
                    // Use unread_count from response instead of separate API call
                    if (response.data.unread_count !== undefined) {
                        this.unreadNotificationsCount = response.data.unread_count;
                    }
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
                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
            });
        },
    },
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.pb-safe { padding-bottom: env(safe-area-inset-bottom); }
.fill-1 { font-variation-settings: 'FILL' 1; }
</style>
