<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Screen Container -->
        <div class="relative h-screen w-full flex flex-col mx-auto max-w-md overflow-hidden">
            <!-- Backdrop Overlay -->
            <div class="absolute inset-0 z-10 bg-[#0d141b]/60 backdrop-blur-[2px]" @click="$emit('close')"></div>
            
            <!-- Bottom Sheet Modal -->
            <div ref="modalSheet"
                 class="absolute bottom-0 left-0 right-0 z-20 flex flex-col max-h-[92vh] w-full rounded-t-3xl bg-surface-light dark:bg-surface-dark shadow-[0_-8px_30px_rgba(0,0,0,0.12)] transition-transform duration-300 ease-out"
                 :style="sheetStyle"
                 dir="rtl">
                <!-- Handle (Draggable area) -->
                <div class="flex w-full items-center justify-center pt-3 pb-2 cursor-grab active:cursor-grabbing touch-none"
                     @mousedown="startDrag"
                     @touchstart="startDrag">
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
        show: { type: Boolean, default: false },
        title: { type: String, default: '' },
    },
    emits: ['close'],
    data() {
        return {
            closedByBack: false,
            // Draggable data
            startY: 0,
            currentY: 0,
            isDragging: false,
        };
    },
    computed: {
        sheetStyle() {
            return {
                transform: `translateY(${this.currentY}px)`,
                transition: this.isDragging ? 'none' : 'transform 0.3s ease-out'
            };
        }
    },
    mounted() {
        document.addEventListener('keydown', this.handleEscape);
    },
    beforeUnmount() {
        document.removeEventListener('keydown', this.handleEscape);
        this.stopDrag(); // Cleanup global listeners
    },
    watch: {
        show(val) {
            if (val) {
                this.currentY = 0;
                window.history.pushState({ modal: true }, '');
                window.addEventListener('popstate', this.handlePopState);
            } else {
                window.removeEventListener('popstate', this.handlePopState);
                if (this.closedByBack) {
                    this.closedByBack = false;
                } else {
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
        // Draggable Methods
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
            
            // Only allow dragging down (positive Y)
            if (deltaY > 0) {
                this.currentY = deltaY;
                // Prevent scrolling if on touch
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
            
            // Check if dragged enough to close (threshold: 100px)
            if (this.currentY > 100) {
                this.$emit('close');
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
</style>
