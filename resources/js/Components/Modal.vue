<template>
    <Teleport to="body">
        <div v-show="show" class="fixed inset-0 z-[100] flex items-end justify-center overflow-hidden">
            <!-- Backdrop Overlay -->
            <Transition
                enter-active-class="transition-opacity duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="show" class="absolute inset-0 bg-[#0d141b]/60 backdrop-blur-[4px]" @click="closeModal"></div>
            </Transition>
            
            <!-- Bottom Sheet Modal -->
            <Transition
                enter-active-class="transition-transform duration-500 cubic-bezier(0.32, 0.72, 0, 1)"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-400 cubic-bezier(0.32, 0.72, 0, 1)"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div v-if="show" 
                     ref="modalSheet"
                     class="relative z-20 flex flex-col max-h-[96vh] w-full max-w-md rounded-t-[2.5rem] bg-surface-light dark:bg-surface-dark shadow-[0_-10px_40px_rgba(0,0,0,0.3)] border-t border-white/5"
                     :style="sheetStyle"
                     dir="rtl">
                    <!-- Handle (Draggable area) -->
                    <div class="flex w-full items-center justify-center pt-4 pb-2 cursor-grab active:cursor-grabbing touch-none"
                         @mousedown="startDrag"
                         @touchstart="startDrag">
                        <div class="h-1.5 w-14 rounded-full bg-slate-200 dark:bg-slate-700/50"></div>
                    </div>
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800/50">
                        <h2 v-if="title" class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ title }}</h2>
                        <button @click="closeModal" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all active:scale-95">
                            <span class="material-symbols-outlined" style="font-size: 22px;">close</span>
                        </button>
                    </div>
                    <!-- Scrollable Content -->
                    <div class="flex-1 overflow-y-auto no-scrollbar p-6 pb-24">
                        <slot></slot>
                    </div>
                </div>
            </Transition>
        </div>
    </Teleport>
</template>

<script>
export default {
    name: 'Modal',
    props: {
        show: { type: Boolean, default: false },
        title: { type: String, default: '' },
    },
    emits: ['close'],
    data() {
        return {
            closedByBack: false,
            startY: 0,
            currentY: 0,
            isDragging: false,
        };
    },
    computed: {
        sheetStyle() {
            return {
                transform: this.isDragging ? `translateY(${this.currentY}px)` : '',
                transition: this.isDragging ? 'none' : ''
            };
        }
    },
    mounted() {
        document.addEventListener('keydown', this.handleEscape);
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.handleEscape);
        this.stopDrag();
    },
    watch: {
        show(val) {
            if (val) {
                this.currentY = 0;
                window.history.pushState({ modal: true }, '');
                window.addEventListener('popstate', this.handlePopState);
                document.body.style.overflow = 'hidden';
            } else {
                window.removeEventListener('popstate', this.handlePopState);
                if (this.closedByBack) {
                    this.closedByBack = false;
                } else {
                    if (window.history.state?.modal) {
                        window.history.back();
                    }
                }
                document.body.style.overflow = '';
            }
        }
    },
    methods: {
        closeModal() {
            this.$emit('close');
        },
        handleEscape(e) {
            if (e.key === 'Escape' && this.show) {
                this.closeModal();
            }
        },
        handlePopState() {
            if (this.show) {
                this.closedByBack = true;
                this.closeModal();
            }
        },
        startDrag(e) {
            this.isDragging = true;
            this.startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            
            document.addEventListener('mousemove', this.onDrag);
            document.addEventListener('touchmove', this.onDrag, { passive: false });
            document.addEventListener('mouseup', this.stopDrag);
            document.addEventListener('touchend', this.stopDrag);
        },
        onDrag(e) {
            if (!this.isDragging) return;
            
            const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            const deltaY = clientY - this.startY;
            
            if (deltaY > 0) {
                this.currentY = deltaY;
                if (e.cancelable) e.preventDefault();
            }
        },
        stopDrag() {
            if (!this.isDragging) return;
            this.isDragging = false;
            
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('touchmove', this.onDrag);
            document.removeEventListener('mouseup', this.stopDrag);
            document.removeEventListener('touchend', this.stopDrag);
            
            if (this.currentY > 120) {
                this.closeModal();
            } else {
                this.currentY = 0;
            }
        }
    },
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.translate-y-full {
    transform: translateY(100%);
}
.translate-y-0 {
    transform: translateY(0);
}
</style>
