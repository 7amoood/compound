<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Screen Container -->
        <div class="relative h-screen w-full flex flex-col mx-auto max-w-md overflow-hidden">
            <!-- Backdrop Overlay -->
            <div class="absolute inset-0 z-10 bg-[#0d141b]/60 backdrop-blur-[2px]" @click="$emit('close')"></div>
            
            <!-- Bottom Sheet Modal -->
            <div class="absolute bottom-0 left-0 right-0 z-20 flex flex-col max-h-[92vh] w-full rounded-t-3xl bg-surface-light dark:bg-surface-dark shadow-[0_-8px_30px_rgba(0,0,0,0.12)] transition-transform duration-300 ease-out transform translate-y-0" dir="rtl">
                <!-- Handle -->
                <div class="flex w-full items-center justify-center pt-3 pb-1 cursor-grab active:cursor-grabbing">
                    <div class="h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-600"></div>
                </div>
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                    <h2 v-if="title" class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ title }}</h2>
                    <button @click="$emit('close')" class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
                    </button>
                </div>
                <!-- Scrollable Content -->
                <div class="flex-1 overflow-y-auto no-scrollbar p-6 pb-24">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'Modal',
    props: {
        show: Boolean,
        title: String,
    },
    emits: ['close'],
    mounted() {
        document.addEventListener('keydown', this.handleEscape);
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.handleEscape);
    },
    data() {
        return {
            closedByBack: false,
        };
    },
    watch: {
        show(val) {
            if (val) {
                // Modal opened: Push state and add listener
                window.history.pushState({ modal: true }, '');
                window.addEventListener('popstate', this.handlePopState);
            } else {
                // Modal closed: Remove listener
                window.removeEventListener('popstate', this.handlePopState);
                if (this.closedByBack) {
                    // Closed by back button, state already popped
                    this.closedByBack = false;
                } else {
                    // Closed manually, pop state manually
                    window.history.back();
                }
            }
        }
    },
    methods: {
        handleEscape(e) {
            if (e.key === 'Escape' && this.show) {
                this.$emit('close');
            }
        },
        handlePopState() {
            if (this.show) {
                this.closedByBack = true;
                this.$emit('close');
            }
        },
    },
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
