<template>
    <Teleport to="body">
        <div v-show="show" class="fixed inset-0 z-[100] flex items-end justify-center overflow-hidden">
            <!-- Backdrop Overlay -->
            <Transition
                enter-active-class="transition-opacity duration-500 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300 ease-in"
                leave-to-class="opacity-0"
            >
                <div v-if="show" class="absolute inset-0 bg-[#0d141b]/60 backdrop-blur-[4px]" @click="closeModal"></div>
            </Transition>
            
            <!-- Bottom Sheet Modal -->
            <Transition
                enter-active-class="transition-transform duration-600 cubic-bezier(0.32, 0.72, 0, 1)"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-500 cubic-bezier(0.32, 0.72, 0, 1)"
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
                    <div ref="contentArea" 
                         class="flex-1 overflow-y-auto no-scrollbar p-6 pb-24"
                         @touchstart="startDrag"
                         @mousedown="startDrag">
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
            startTime: 0,
            canDrag: false,
            initialScrollTop: 0,
            isClosing: false,
        };
    },
    computed: {
        sheetStyle() {
            return {
                transform: `translateY(${this.currentY}px)`,
                transition: this.isDragging ? 'none' : 'transform 0.5s cubic-bezier(0.32, 0.72, 0, 1)'
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
                // Save current URL with query params before pushing modal state
                const currentUrl = window.location.href;
                window.history.pushState({ modal: true, previousUrl: currentUrl }, '');
                window.addEventListener('popstate', this.handlePopState);
                document.body.style.overflow = 'hidden';
            } else {
                window.removeEventListener('popstate', this.handlePopState);
                if (this.closedByBack) {
                    this.closedByBack = false;
                } else {
                    // Go back to restore the previous URL with query params
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
            if (this.isClosing) return;
            this.isClosing = true;
            
            // Animate the modal sliding down
            this.currentY = window.innerHeight;
            
            // Wait for the transition to complete before actually closing
            setTimeout(() => {
                this.isClosing = false;
                this.currentY = 0;
                this.$emit('close');
            }, 500); // Match the leave-active-class duration
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
            // Store initial touch/mouse position
            this.startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            this.startTime = Date.now();
            this.canDrag = false;
            this.isDragging = false;
            
            // Check if we're at the top of scrollable content
            const contentArea = this.$refs.contentArea;
            if (contentArea) {
                this.initialScrollTop = contentArea.scrollTop;
            }
            
            document.addEventListener('mousemove', this.onDrag);
            document.addEventListener('touchmove', this.onDrag, { passive: false });
            document.addEventListener('mouseup', this.stopDrag);
            document.addEventListener('touchend', this.stopDrag);
        },
        onDrag(e) {
            const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
            const deltaY = clientY - this.startY;
            
            // Check current scroll position
            const contentArea = this.$refs.contentArea;
            const currentScrollTop = contentArea ? contentArea.scrollTop : 0;
            
            // Only allow drag-to-close if:
            // 1. We're at the top of the content (scrollTop <= 5)
            // 2. User is dragging DOWN (deltaY > 0)
            if (currentScrollTop <= 5 && deltaY > 0) {
                // Enable dragging after a small threshold
                if (!this.canDrag && deltaY > 10) {
                    this.canDrag = true;
                }
                
                if (this.canDrag) {
                    if (!this.isDragging && deltaY > 15) {
                        this.isDragging = true;
                    }
                    
                    if (this.isDragging) {
                        // Apply resistance for smoother feel
                        this.currentY = Math.pow(deltaY, 0.9);
                        if (e.cancelable) e.preventDefault();
                    }
                }
            } else {
                // User is either scrolling up or not at top - allow normal scroll
                this.canDrag = false;
                this.isDragging = false;
            }
        },
        stopDrag() {
            if (!this.canDrag && !this.isDragging) {
                this.cleanup();
                return;
            }
            
            const wasDragging = this.isDragging;
            this.isDragging = false;
            this.canDrag = false;
            
            this.cleanup();
            
            if (!wasDragging) return;
            
            // Calculate velocity for natural swipe detection
            const timeDiff = Date.now() - this.startTime;
            const velocity = this.currentY / timeDiff;
            
            // Close if:
            // 1. Dragged more than 80px (reduced from 120px)
            // 2. Fast swipe down (velocity > 0.5)
            if (this.currentY > 80 || velocity > 0.5) {
                // Animate to bottom before closing
                this.currentY = window.innerHeight;
                this.closeModal();
            } else {
                // Bounce back smoothly
                this.currentY = 0;
            }
        },
        cleanup() {
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('touchmove', this.onDrag);
            document.removeEventListener('mouseup', this.stopDrag);
            document.removeEventListener('touchend', this.stopDrag);
        }
    },
};
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.translate-y-full {
    transform: translateY(100%) !important;
}
.translate-y-0 {
    transform: translateY(0);
}
</style>
