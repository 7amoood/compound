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
                <button @click="showNewRequestModal = true" class="flex items-center gap-1 px-3 py-2 bg-primary text-white rounded-xl text-sm font-bold shadow-lg shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>طلب مساعدة</span>
                </button>
            </div>
        </header>

        <!-- Tabs -->
        <div class="overflow-x-auto no-scrollbar border-b border-slate-100 dark:border-slate-800/10 bg-white dark:bg-surface-dark">
            <div class="flex min-w-max px-4 py-2 gap-2">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                    class="h-9 px-4 rounded-full text-sm font-semibold transition-colors whitespace-nowrap"
                    :class="activeTab === tab.id ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'">
                    {{ tab.label }}
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden no-scrollbar pb-24">
            <PullToRefresh :onRefresh="loadData" class="min-h-full">
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
                                    <p v-if="activeTab === 'available' && req.requester" class="text-xs text-slate-500">
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
                            <span class="text-xs font-medium text-blue-700 dark:text-blue-300">
                                {{ activeTab === 'my' ? 'يساعدك: ' : 'تساعد: ' }}{{ activeTab === 'my' ? req.helper?.name : req.requester?.name }}
                            </span>
                        </div>

                        <!-- Quick Actions -->
                        <div v-if="activeTab === 'available'" class="mt-3">
                            <button @click.stop="pickRequest(req)" class="w-full py-2 bg-primary text-white rounded-lg text-sm font-bold shadow-sm">
                                سأساعد 🙋
                            </button>
                        </div>
                        <div v-if="activeTab === 'my' && req.status === 'open'" class="mt-3">
                            <button @click.stop="cancelRequest(req)" class="w-full py-2 bg-red-500 text-white rounded-lg text-sm font-bold">
                                إلغاء الطلب
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
                        <p v-if="canSeePhone" class="text-sm text-slate-500">{{ selectedRequest.requester?.phone }}</p>
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
                            <p v-if="canSeeHelperPhone" class="text-xs text-slate-500">{{ selectedRequest.helper?.phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Comments Section -->
                <div v-if="selectedRequest.status === 'picked' && canComment" class="space-y-3">
                    <p class="text-sm font-bold text-slate-900 dark:text-white">التعليقات ({{ selectedRequest.comments?.length || 0 }}/50)</p>
                    
                    <div class="max-h-48 overflow-y-auto space-y-2">
                        <div v-for="comment in selectedRequest.comments" :key="comment.id" 
                            class="flex gap-2 p-2 rounded-lg"
                            :class="comment.user_id === currentUserId ? 'bg-primary/10' : 'bg-slate-50 dark:bg-slate-800'">
                            <div class="size-8 rounded-full bg-cover bg-center shrink-0" 
                                :style="`background-image: url('${comment.user?.photo || 'https://ui-avatars.com/api/?name=' + (comment.user?.name || 'U')}');`"></div>
                            <div>
                                <p class="text-xs font-bold text-slate-900 dark:text-white">{{ comment.user?.name }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ comment.comment }}</p>
                            </div>
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
                </div>

                <!-- Actions -->
                <div v-if="selectedRequest.status === 'open' && selectedRequest.requester_id !== currentUserId" class="pt-2">
                    <button @click="pickRequest(selectedRequest)" class="w-full py-3 bg-primary text-white font-bold rounded-xl">
                        سأساعد 🙋
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
            newRequestDescription: '',
            submitting: false,
            newComment: '',
            addingComment: false,
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
        canSeePhone() {
            if (!this.selectedRequest) return false;
            const isOwner = this.selectedRequest.requester_id === this.currentUserId;
            const isHelper = this.selectedRequest.helper_id === this.currentUserId;
            return isOwner || isHelper;
        },
        canSeeHelperPhone() {
            if (!this.selectedRequest) return false;
            const isOwner = this.selectedRequest.requester_id === this.currentUserId;
            return isOwner;
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
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        async loadData() {
            this.loading = true;
            this.requests = [];
            this.nextPageUrl = null;

            try {
                let endpoint = '/api/help/available';
                if (this.activeTab === 'my') {
                    endpoint = '/api/help/my-requests';
                } else if (this.activeTab === 'helped') {
                    endpoint = '/api/help/my-helps';
                } else if (this.activeTab === 'cancelled') {
                    endpoint = '/api/help/my-requests?status=cancelled';
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
        async pickRequest(req) {
            try {
                const response = await axios.post(`/api/help/${req.id}/pick`);
                if (response.data.success) {
                    window.showToast('شكراً لمساعدتك! 🙏', 'success');
                    this.showDetailsModal = false;
                    this.loadData();
                } else {
                    window.showToast(response.data.message, 'error');
                }
            } catch (e) {
                window.showToast(e.response?.data?.message || 'حدث خطأ', 'error');
            }
        },
        async cancelRequest(req) {
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
            }
        },
        async openDetails(req) {
            try {
                const response = await axios.get(`/api/help/${req.id}`);
                if (response.data.success) {
                    this.selectedRequest = response.data.request;
                    this.showDetailsModal = true;
                }
            } catch (e) {
                window.showToast('تعذر تحميل التفاصيل', 'error');
            }
        },
        async addComment() {
            if (!this.newComment.trim() || this.addingComment) return;
            this.addingComment = true;
            try {
                const response = await axios.post(`/api/help/${this.selectedRequest.id}/comment`, {
                    comment: this.newComment,
                });
                if (response.data.success) {
                    this.selectedRequest.comments.push(response.data.comment);
                    this.newComment = '';
                }
            } catch (e) {
                window.showToast(e.response?.data?.message || 'حدث خطأ', 'error');
            } finally {
                this.addingComment = false;
            }
        },
        formatDate(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000);
            if (diff < 60) return 'الآن';
            if (diff < 3600) return `منذ ${Math.floor(diff / 60)} دقيقة`;
            if (diff < 86400) return `منذ ${Math.floor(diff / 3600)} ساعة`;
            if (diff < 604800) return `منذ ${Math.floor(diff / 86400)} يوم`;
            return date.toLocaleDateString('ar-EG');
        },
    },
};
</script>
