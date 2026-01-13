<template>
    <Head title="مساعدة" />
    <Toast />
    <div class="relative flex h-[100vh] w-full flex-col overflow-hidden max-w-md mx-auto bg-background-light dark:bg-background-dark font-display" dir="rtl">
        <!-- Header -->
        <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-md border-b border-slate-100 dark:border-slate-800/10 transition-all duration-200" style="padding-top: env(safe-area-inset-top);">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="size-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <span class="material-symbols-outlined">volunteer_activism</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">مساعدة الجيران</span>
                    </div>
                </div>
                <!-- Notifications Button -->
                <button @click="showNotificationsModal = true; loadNotifications()" class="relative p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-slate-600 dark:text-slate-300">notifications</span>
                    <span v-if="unreadNotificationsCount > 0" class="absolute top-1 right-1 size-2.5 bg-red-500 rounded-full border-2 border-white dark:border-background-dark"></span>
                </button>
            </div>
        </header>

        <!-- New Request Button -->
        <div class="px-4 pt-4 pb-2">
            <button @click="showNewRequestModal = true" class="w-full flex items-center justify-center gap-2 bg-primary hover:bg-blue-600 text-white font-bold h-12 rounded-xl shadow-lg shadow-primary/20 active:scale-[0.98] transition-all duration-200">
                <span class="material-symbols-outlined">add_circle</span>
                <span>طلب مساعدة جديد</span>
            </button>
        </div>

        <!-- Tabs -->
        <div class="overflow-x-auto no-scrollbar -mx-4 px-4 pb-2 pt-2">
            <div class="flex gap-2 min-w-max pr-4 px-4">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                    class="h-8 px-4 rounded-full text-sm font-semibold shadow-sm transition-colors border"
                    :class="activeTab === tab.id ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-slate-900 dark:border-white' : 'bg-white dark:bg-surface-dark border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'">
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar pb-24">
            <PullToRefresh :onRefresh="() => loadData(true)" class="min-h-full">
                <div class="flex flex-col gap-3 p-4 min-h-[60vh]">
                    <!-- Loading -->
                    <div v-if="loading" class="text-center py-12 text-slate-400">
                        <span class="material-symbols-outlined text-4xl animate-spin">progress_activity</span>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="requests.length === 0" class="flex-1 flex flex-col items-center justify-center text-center py-12 text-slate-400">
                        <span class="material-symbols-outlined text-5xl">{{ emptyIcon }}</span>
                        <p class="mt-2">{{ emptyMessage }}</p>
                    </div>

                    <!-- Requests List -->
                    <div v-else v-for="req in requests" :key="req.id" @click="openDetails(req)"
                        class="bg-white dark:bg-surface-dark p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 cursor-pointer active:bg-slate-50 dark:active:bg-slate-800 transition-colors">
                        <!-- Header -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-full bg-cover bg-center bg-slate-100" 
                                    :style="`background-image: url('${req.requester?.photo || 'https://ui-avatars.com/api/?name=' + (req.requester?.name || 'U')}');`"></div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm">{{ req.requester?.name }}</p>
                                    <p v-if="req.requester" class="text-xs text-slate-500">
                                        <span v-if="req.requester.block_no">عمارة {{ req.requester.block_no }}</span>
                                        <span v-if="req.requester.floor"> - الدور {{ req.requester.floor }}</span>
                                        <span v-if="req.requester.apt_no"> - شقة {{ req.requester.apt_no }}</span>
                                    </p>
                                    <p class="text-xs text-slate-400">{{ formatDate(req.created_at) }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold"
                                :class="{
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': req.status === 'open',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': req.status === 'picked',
                                    'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400': req.status === 'cancelled'
                                }">
                                {{ req.status === 'open' ? 'مفتوح' : req.status === 'picked' ? 'تم الالتقاط' : 'ملغي' }}
                            </span>
                        </div>
                        
                        <!-- Description -->
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed line-clamp-3">{{ req.description }}</p>
                        
                        <!-- Helper Info (if picked) -->
                        <div v-if="req.helper && (activeTab === 'my' || activeTab === 'helped')" class="mt-3 flex items-center gap-2 bg-blue-50 dark:bg-blue-900/20 p-2 rounded-lg">
                            <div class="size-6 rounded-full bg-cover bg-center" 
                                :style="`background-image: url('${req.helper?.photo || 'https://ui-avatars.com/api/?name=' + (req.helper?.name || 'H')}');`"></div>
                            <div class="flex-1">
                                <span class="text-xs font-medium text-blue-700 dark:text-blue-300">
                                    {{ activeTab === 'my' ? 'يساعدك: ' : 'تساعد: ' }}{{ activeTab === 'my' ? req.helper?.name : req.requester?.name }}
                                </span>
                                <p v-if="activeTab === 'my' && req.helper" class="text-[10px] text-blue-600/70 dark:text-blue-400/70">
                                    <span v-if="req.helper.block_no">عمارة {{ req.helper.block_no }}</span>
                                    <span v-if="req.helper.floor"> - الدور {{ req.helper.floor }}</span>
                                    <span v-if="req.helper.apt_no"> - شقة {{ req.helper.apt_no }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div v-if="activeTab === 'available'" class="mt-3">
                            <button @click.stop="startPick(req)" :disabled="pickingRequest && selectedRequest?.id === req.id" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-bold shadow-sm flex items-center justify-center gap-2">
                                <span v-if="pickingRequest && selectedRequest?.id === req.id" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                                <span>سأساعد 🙋</span>
                            </button>
                        </div>
                        <div v-if="activeTab === 'my' && req.status === 'open'" class="mt-3">
                            <button @click.stop="cancelRequest(req)" :disabled="cancellingRequestId === req.id" class="w-full py-2 bg-red-500 text-white rounded-lg text-sm font-bold flex items-center justify-center gap-2">
                                <span v-if="cancellingRequestId === req.id" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                                <span>إلغاء الطلب</span>
                            </button>
                        </div>
                    </div>

                    <!-- Load More -->
                    <button v-if="nextPageUrl && !loading" @click="loadMore" :disabled="loadingMore"
                        class="w-full py-3 text-sm font-bold text-primary bg-primary/10 rounded-xl flex items-center justify-center gap-2">
                        <span v-if="loadingMore" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                        <span>{{ loadingMore ? 'جاري التحميل...' : 'تحميل المزيد' }}</span>
                    </button>
                </div>
            </PullToRefresh>
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-surface-dark/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 pb-safe z-40">
            <div class="flex items-center justify-around h-16 max-w-md mx-auto">
                <Link href="/dashboard" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined">home</span>
                    <span class="text-[10px] font-medium">الرئيسية</span>
                </Link>
                <Link href="/help" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-primary">
                    <span class="material-symbols-outlined fill-1">volunteer_activism</span>
                    <span class="text-[10px] font-bold">مساعدة</span>
                </Link>
                <Link href="/settings" class="flex flex-col items-center justify-center flex-1 h-full gap-1 text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <span class="material-symbols-outlined">person</span>
                    <span class="text-[10px] font-medium">الحساب</span>
                </Link>
            </div>
        </nav>

        <!-- New Request Modal -->
        <Modal :show="showNewRequestModal" title="طلب مساعدة" @close="showNewRequestModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">وصف المساعدة المطلوبة</label>
                    <textarea v-model="newRequestDescription" 
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 p-4 text-base text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary resize-none transition-colors"
                        placeholder="مثال: محتاج حد يساعدني أنقل أثاث ثقيل..."
                        rows="4"></textarea>
                </div>
                <button @click="submitNewRequest" :disabled="submitting || !newRequestDescription.trim()"
                    class="w-full py-4 bg-primary text-white font-bold rounded-xl shadow-lg shadow-primary/25 flex items-center justify-center gap-2 disabled:opacity-50">
                    <span v-if="submitting" class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>
                    <span>نشر طلب المساعدة</span>
                </button>
            </div>
        </Modal>

        <!-- Details Modal -->
        <Modal :show="showDetailsModal" title="تفاصيل الطلب" @close="showDetailsModal = false">
            <div v-if="selectedRequest" class="space-y-4">
                <!-- Requester -->
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="size-12 rounded-full bg-cover bg-center" 
                        :style="`background-image: url('${selectedRequest.requester?.photo || 'https://ui-avatars.com/api/?name=' + (selectedRequest.requester?.name || 'U')}');`"></div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">{{ selectedRequest.requester?.name }}</p>
                        <p v-if="selectedRequest.requester" class="text-xs text-slate-500">
                            <span v-if="selectedRequest.requester.block_no">عمارة {{ selectedRequest.requester.block_no }}</span>
                            <span v-if="selectedRequest.requester.floor"> - الدور {{ selectedRequest.requester.floor }}</span>
                            <span v-if="selectedRequest.requester.apt_no"> - شقة {{ selectedRequest.requester.apt_no }}</span>
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl">
                    <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ selectedRequest.description }}</p>
                </div>

                <!-- Helper Info -->
                <div v-if="selectedRequest.helper" class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl">
                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 mb-2">المتطوع للمساعدة</p>
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-cover bg-center" 
                            :style="`background-image: url('${selectedRequest.helper?.photo || 'https://ui-avatars.com/api/?name=' + (selectedRequest.helper?.name || 'H')}');`"></div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white text-sm">{{ selectedRequest.helper?.name }}</p>
                            <p v-if="selectedRequest.helper" class="text-[11px] text-blue-600/70 dark:text-blue-400/70">
                                <span v-if="selectedRequest.helper.block_no">عمارة {{ selectedRequest.helper.block_no }}</span>
                                <span v-if="selectedRequest.helper.floor"> - الدور {{ selectedRequest.helper.floor }}</span>
                                <span v-if="selectedRequest.helper.apt_no"> - شقة {{ selectedRequest.helper.apt_no }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div v-if="selectedRequest.status === 'picked' && canComment" class="space-y-3">
                    <div v-if="loadingDetails && (!selectedRequest.comments || selectedRequest.comments.length === 0)" class="text-center py-4 text-slate-400">
                        <span class="material-symbols-outlined animate-spin text-2xl">progress_activity</span>
                        <p class="text-xs mt-1">جاري تحميل التعليقات...</p>
                    </div>

                    <template v-else>
                        <!-- Chat Messages -->
                        <div ref="commentsContainer" class="max-h-60 overflow-y-auto overscroll-contain space-y-3 px-1 py-2 flex flex-col" @touchstart.stop>
                            <!-- Load More (Inside at Top) -->
                            <div v-if="nextCommentsUrl" class="flex justify-center mb-2">
                                <button @click="loadMoreComments" :disabled="loadingMoreComments" class="text-[11px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 px-3 py-1 rounded-full transition-colors flex items-center gap-1">
                                    <span v-if="loadingMoreComments" class="material-symbols-outlined animate-spin text-[12px]">progress_activity</span>
                                    <span>عرض المحادثات الأقدم</span>
                                </button>
                            </div>

                            <div v-for="comment in selectedRequest.comments" :key="comment.id" 
                                class="p-3 rounded-2xl max-w-[85%]"
                                :class="comment.user_id === currentUserId ? 'bg-primary text-white ml-auto rounded-br-none' : 'bg-slate-100 dark:bg-slate-700/50 text-slate-800 dark:text-slate-200 mr-auto rounded-bl-none'">
                                
                                <div class="flex justify-between items-start gap-4 mb-1">
                                    <p class="text-[10px] font-bold opacity-80" :class="comment.user_id === currentUserId ? 'text-blue-100' : 'text-slate-500 dark:text-slate-400'">
                                        {{ getCommentAuthorName(comment) }}
                                    </p>
                                    <p class="text-[9px] opacity-60 font-medium">
                                        {{ formatTime(comment.created_at) }}
                                    </p>
                                </div>
                                <p class="text-sm leading-relaxed">{{ comment.comment }}</p>
                            </div>
                        </div>

                        <!-- Add Comment -->
                        <div v-if="(selectedRequest.comments?.length || 0) < 50" class="flex gap-2">
                            <input v-model="newComment" type="text" 
                                class="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                placeholder="اكتب تعليقاً...">
                            <button @click="addComment" :disabled="!newComment.trim() || addingComment"
                                class="px-4 py-2 bg-primary text-white rounded-xl text-sm font-bold disabled:opacity-50">
                                <span v-if="addingComment" class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                                <span v-else>إرسال</span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Actions -->
                <div v-if="selectedRequest.status === 'open' && selectedRequest.requester_id !== currentUserId" class="pt-2">
                    <button @click="startPick" :disabled="pickingRequest" class="w-full py-3 bg-primary text-white font-bold rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-primary/20 hover:bg-blue-600 transition-colors">
                        <span v-if="pickingRequest" class="material-symbols-outlined animate-spin">progress_activity</span>
                        <span v-else>سأساعد 🙋</span>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Notifications Modal -->
        <Modal :show="showNotificationsModal" title="الإشعارات" @close="showNotificationsModal = false">
            <div class="flex items-center justify-between mb-4 px-1" v-if="notifications.length > 0">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">سجل التنبيهات</h3>
                <button v-if="unreadNotificationsCount > 0" :disabled="loadingMarkAll" @click="markAllNotificationsAsRead" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                    <span v-if="loadingMarkAll" class="material-symbols-outlined animate-spin text-[14px]">progress_activity</span>
                    <span>تحديد الكل كمقروء</span>
                </button>
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
        <!-- Pick Help Confirmation Modal -->
        <Modal :show="showPickModal" title="تقديم المساعدة" @close="showPickModal = false">
            <div class="space-y-4">
                <!-- Request Description Preview -->
                <div v-if="selectedRequest" class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold text-slate-400 mb-1 uppercase tracking-wider">تفاصيل طلب المساعدة:</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed italic">"{{ selectedRequest.description }}"</p>
                </div>

                <div class="p-4 bg-primary/5 rounded-2xl border border-primary/10">
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        أرسل رسالة سريعة لجيرانك لتخبرهم كيف يمكنك مساعدتهم. سيبدأ هذا المحادثة بينكما.
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">رسالتك الأولى</label>
                    <textarea v-model="pickComment" 
                        rows="3"
                        class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary placeholder:text-slate-400"
                        placeholder="مثلاً: أنا في طريقي الآن، هل تحتاج شيئاً آخر؟"></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="showPickModal = false" class="flex-1 py-3 text-sm font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        إلغاء
                    </button>
                    <button @click="confirmPick" 
                        :disabled="!pickComment.trim() || confirmingPick"
                        class="flex-[2] py-3 text-sm font-bold text-white bg-primary rounded-xl shadow-lg shadow-primary/20 hover:bg-blue-600 transition-all flex items-center justify-center gap-2">
                        <span v-if="confirmingPick" class="material-symbols-outlined animate-spin text-[20px]">progress_activity</span>
                        <span v-else>إرسال ومساعدة 🙋</span>
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

export default {
    name: 'Help',
    components: { Head, Link, Modal, Toast, PullToRefresh },
    data() {
        return {
            activeTab: 'available',
            tabs: [
                { id: 'available', label: 'طلبات متاحة' },
                { id: 'my', label: 'طلباتي' },
                { id: 'helped', label: 'ساعدت فيها' },
            ],
            requests: [],
            loading: false,
            loadingMore: false,
            nextPageUrl: null,
            showNewRequestModal: false,
            showDetailsModal: false,
            selectedRequest: null,
            loadingDetails: false,
            pickingRequest: false,
            newRequestDescription: '',
            submitting: false,
            newComment: '',
            addingComment: false,
            nextCommentsUrl: null,
            loadingMoreComments: false,
            // Notifications
            showNotificationsModal: false,
            notifications: [],
            unreadNotificationsCount: 0,
            loadingNotifications: false,
            loadingMarkAll: false,
            loadingMoreNotifications: false,
            nextNotificationsUrl: null,
            showPickModal: false,
            pickComment: '',
            confirmingPick: false,
            cancellingRequestId: null,
        };
    },
    computed: {
        currentUserId() {
            return usePage().props.auth?.user?.id;
        },
        emptyIcon() {
            return {
                'available': 'volunteer_activism',
                'my': 'inbox',
                'helped': 'handshake',
                'cancelled': 'delete_sweep',
            }[this.activeTab] || 'inbox';
        },
        emptyMessage() {
            return {
                'available': 'لا توجد طلبات مساعدة حالياً',
                'my': 'لم تطلب مساعدة بعد',
                'helped': 'لم تساعد أحداً بعد',
                'cancelled': 'لا توجد طلبات ملغية',
            }[this.activeTab] || 'لا توجد بيانات';
        },
        canComment() {
            if (!this.selectedRequest) return false;
            const isOwner = this.selectedRequest.requester_id === this.currentUserId;
            const isHelper = this.selectedRequest.helper_id === this.currentUserId;
            return isOwner || isHelper;
        },
    },
    watch: {
        activeTab() {
            this.loadData();
        },
        '$page.url': {
            handler() {
                const params = new URLSearchParams(window.location.search);
                if (params.has('request_id')) {
                    this.openDetails({ id: params.get('request_id') });
                }
            }
        }
    },
    mounted() {
        this.loadData();
        this.loadUnreadCount();
        window.addEventListener('fcm-message', this.handleFcmMessage);
        
        // Handle direct open (e.g. from notification click opening the app)
        const params = new URLSearchParams(window.location.search);
        if (params.has('request_id')) {
            this.openDetails({id: params.get('request_id')});
        }
    },
    beforeUnmount() {
         window.removeEventListener('fcm-message', this.handleFcmMessage);
    },
    methods: {
        async loadUnreadCount() {
            try {
                const response = await axios.get('/api/notifications/unread-count');
                this.unreadNotificationsCount = response.data.count;
            } catch (e) {
                console.error(e);
            }
        },
        async loadNotifications(url = '/api/notifications') {
            this.loadingNotifications = true;
            try {
                const response = await axios.get(url);
                if (response.data.success) {
                    if (url === '/api/notifications') {
                        this.notifications = response.data.notifications.data;
                    } else {
                        this.notifications = [...this.notifications, ...response.data.notifications.data];
                    }
                    this.nextNotificationsUrl = response.data.notifications.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingNotifications = false;
                this.loadingMoreNotifications = false;
            }
        },
        async loadMoreNotifications() {
            if (!this.nextNotificationsUrl) return;
            this.loadingMoreNotifications = true;
            this.loadNotifications(this.nextNotificationsUrl);
        },
        async markAllNotificationsAsRead() {
            this.loadingMarkAll = true;
            try {
                await axios.post('/api/notifications/read-all');
                this.notifications.forEach(n => n.is_read = true);
                this.unreadNotificationsCount = 0;
            } catch (e) {
                window.showToast('حدث خطأ', 'error');
            } finally {
                this.loadingMarkAll = false;
            }
        },
        async handleNotificationClick(notification) {
            if (!notification.is_read) {
                try {
                    await axios.post(`/api/notifications/${notification.id}/read`);
                    notification.is_read = true;
                    this.unreadNotificationsCount = Math.max(0, this.unreadNotificationsCount - 1);
                } catch (e) {
                    console.error(e);
                }
            }
            
            let data = notification.data || {};
            if (typeof data === 'string') {
                try { data = JSON.parse(data); } catch(e) {}
            }
            
            const rid = data.help_request_id || data.request_id;
            if (rid) {
                this.showNotificationsModal = false;
                this.openDetails({ id: rid });
            } else if (data.click_action && data.click_action !== window.location.pathname) {
                 window.location.href = data.click_action;
            }
        },
        handleFcmMessage(event) {
            const payload = event.detail;

            // Ignore if sender is self
            if (payload?.data?.sender_id && 
                this.currentUserId && 
                payload.data.sender_id == this.currentUserId) {
                return;
            }

            this.unreadNotificationsCount++;
            if (this.showNotificationsModal) {
                this.loadNotifications();
            }
            
            // Check if a comment arrived for the currently open request
            const requestId = payload.data?.help_request_id || payload.data?.request_id;
            const type = payload.data?.type;

            if (type === 'help_comment' && 
                this.showDetailsModal && 
                this.selectedRequest && 
                this.selectedRequest.id == requestId) {
                // Silently refresh details to get new comment
                this.refreshCurrentDetails();
            }
            
            if (this.activeTab === 'available') {
                this.loadData();
            }
        },
        async refreshCurrentDetails() {
            if (!this.selectedRequest) return;
            try {
                const response = await axios.get(`/api/help/${this.selectedRequest.id}`);
                if (response.data.success && this.selectedRequest && this.selectedRequest.id == response.data.request.id) {
                    // Update only comments to avoid UI flicker if possible, 
                    // but here we update the whole object for simplicity and consistency
                    const oldCommentsCount = this.selectedRequest.comments?.length || 0;
                    this.selectedRequest = response.data.request;
                    
                    if (response.data.comments) {
                        this.selectedRequest.comments = response.data.comments.data.slice().reverse();
                        this.nextCommentsUrl = response.data.comments.next_page_url;
                        
                        // Scroll to bottom if new comments arrived
                        if (this.selectedRequest.comments.length > oldCommentsCount) {
                            this.scrollToBottom();
                        }
                    }
                }
            } catch (e) {
                console.error('Failed to refresh details:', e);
            }
        },

        async loadData(isRefresh = false) {
            if (!isRefresh) {
                this.loading = true;
                this.requests = [];
            }
            this.nextPageUrl = null;

            try {
                let endpoint = '/api/help/available';
                if (this.activeTab === 'my') {
                    endpoint = '/api/help/my-requests';
                } else if (this.activeTab === 'helped') {
                    endpoint = '/api/help/my-helps';
                }

                const response = await axios.get(endpoint);
                if (response.data.success) {
                    this.requests = response.data.requests.data;
                    this.nextPageUrl = response.data.requests.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async loadMore() {
            if (!this.nextPageUrl || this.loadingMore) return;
            this.loadingMore = true;
            try {
                const response = await axios.get(this.nextPageUrl);
                if (response.data.success) {
                    this.requests.push(...response.data.requests.data);
                    this.nextPageUrl = response.data.requests.next_page_url;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingMore = false;
            }
        },
        async submitNewRequest() {
            if (!this.newRequestDescription.trim()) return;
            this.submitting = true;
            try {
                const response = await axios.post('/api/help', {
                    description: this.newRequestDescription,
                });
                if (response.data.success) {
                    window.showToast('تم نشر طلب المساعدة', 'success');
                    this.showNewRequestModal = false;
                    this.newRequestDescription = '';
                    this.activeTab = 'my';
                }
            } catch (e) {
                window.showToast('حدث خطأ', 'error');
            } finally {
                this.submitting = false;
            }
        },
        async startPick(req = null) {
            if (req && req.id) {
                this.selectedRequest = req;
            }
            this.pickingRequest = true;
            setTimeout(() => {
                this.showPickModal = true;
                this.pickingRequest = false;
            }, 300);
        },
        async confirmPick() {
            if (!this.pickComment.trim() || this.confirmingPick) return;
            this.confirmingPick = true;
            try {
                const response = await axios.post(`/api/help/${this.selectedRequest.id}/pick`, {
                    comment: this.pickComment
                });
                if (response.data.success) {
                    window.showToast('شكراً لمساعدتك! 🙏', 'success');
                    this.showPickModal = false;
                    this.pickComment = '';
                    this.showDetailsModal = false;
                    this.activeTab = 'helped';
                } else {
                    window.showToast(response.data.message, 'error');
                }
            } catch (e) {
                window.showToast(e.response?.data?.message || 'حدث خطأ', 'error');
            } finally {
                this.confirmingPick = false;
            }
        },
        async cancelRequest(req) {
            if (this.cancellingRequestId) return;
            this.cancellingRequestId = req.id;
            try {
                const response = await axios.post(`/api/help/${req.id}/cancel`);
                if (response.data.success) {
                    window.showToast('تم إلغاء الطلب', 'success');
                    this.loadData();
                } else {
                    window.showToast(response.data.message, 'error');
                }
            } catch (e) {
                window.showToast(e.response?.data?.message || 'حدث خطأ', 'error');
            } finally {
                this.cancellingRequestId = null;
            }
        },
        async openDetails(req) {
            // Optimistic UI: Show modal immediately with available data
            this.selectedRequest = req;
            this.selectedRequest.comments = this.selectedRequest.comments?.reverse();
            this.showDetailsModal = true;
            this.loadingDetails = true;
            this.nextCommentsUrl = null;

            try {
                const response = await axios.get(`/api/help/${req.id}`);
                if (response.data.success) {
                    if (this.selectedRequest && this.selectedRequest.id === req.id) {
                        this.selectedRequest = response.data.request;
                        
                        // Handle paginated comments
                        if (response.data.comments) {
                            this.selectedRequest.comments = response.data.comments.data.slice().reverse(); 
                            this.nextCommentsUrl = response.data.comments.next_page_url;
                            this.scrollToBottom();
                        }
                    }
                }
            } catch (e) {
                // Keep showing basic data if fetch fails, or show error
                if (this.selectedRequest && this.selectedRequest.id === req.id) {
                    window.showToast('تعذر تحميل أحدث التفاصيل', 'error');
                }
            } finally {
                if (this.selectedRequest && this.selectedRequest.id === req.id) {
                    this.loadingDetails = false;
                }
            }
        },
        async loadMoreComments() {
            if (!this.nextCommentsUrl || this.loadingMoreComments) return;
            this.loadingMoreComments = true;
            
            const container = this.$refs.commentsContainer;
            const previousHeight = container ? container.scrollHeight : 0;

            try {
                // Ensure the URL uses the comments endpoint specifically
                const response = await axios.get(this.nextCommentsUrl);
                if (response.data.success) {
                    const newComments = response.data.comments.data.slice().reverse();
                    this.selectedRequest.comments = [...newComments, ...this.selectedRequest.comments];
                    this.nextCommentsUrl = response.data.comments.next_page_url;

                    // Preserve scroll position after prepending items
                    this.$nextTick(() => {
                        if (container) {
                            container.scrollTop = container.scrollHeight - previousHeight;
                        }
                    });
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingMoreComments = false;
            }
        },
        scrollToBottom() {
            // Use setTimeout to ensure the DOM has fully rendered and scrollHeight is calculated
            setTimeout(() => {
                const container = this.$refs.commentsContainer;
                if (container) {
                    container.scrollTo({
                        top: container.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }, 50);
        },
        getCommentAuthorName(comment) {
            if (comment.user_id === this.currentUserId) return 'أنت';
            if (this.selectedRequest) {
                if (comment.user_id === this.selectedRequest.requester_id) return 'طالب المساعدة';
                if (comment.user_id === this.selectedRequest.helper_id) return 'المساعد';
            }
            return 'مستخدم';
        },
        async addComment() {
            if (!this.newComment.trim() || this.addingComment) return;
            this.addingComment = true;
            try {
                const response = await axios.post(`/api/help/${this.selectedRequest.id}/comment`, {
                    comment: this.newComment,
                });
                if (response.data.success) {
                    if (!this.selectedRequest.comments) this.selectedRequest.comments = [];
                    this.selectedRequest.comments.push(response.data.comment);
                    this.newComment = '';
                    this.scrollToBottom();
                }
            } catch (e) {
                window.showToast(e.response?.data?.message || 'حدث خطأ', 'error');
            } finally {
                this.addingComment = false;
            }
        },
        formatTime(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'الآن';
            if (date.toDateString() === now.toDateString()) {
                return this.formatTime(dateStr);
            }
            
            return date.toLocaleDateString('ar-EG', { day: 'numeric', month: 'short' }) + ' ' + this.formatTime(dateStr);
        },
    },
};
</script>
