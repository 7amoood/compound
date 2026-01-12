<template>
    <div class="fixed left-4 right-4 z-[9999] flex flex-col gap-2 items-center pointer-events-none" style="top: max(1rem, calc(env(safe-area-inset-top) + 0.5rem));" dir="rtl">
        <transition-group name="toast">
            <div 
                v-for="toast in toasts" 
                :key="toast.id"
                class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl text-white shadow-lg max-w-sm"
                :class="colors[toast.type] || colors.info"
            >
                <span class="material-symbols-outlined text-[20px]">{{ icons[toast.type] || icons.info }}</span>
                <span class="text-sm font-medium">{{ toast.message }}</span>
            </div>
        </transition-group>
    </div>
</template>

<script>
export default {
    name: 'Toast',
    data() {
        return {
            toasts: [],
            icons: {
                success: 'check_circle',
                error: 'error',
                warning: 'warning',
                info: 'info'
            },
            colors: {
                success: 'bg-emerald-500',
                error: 'bg-red-500',
                warning: 'bg-amber-500',
                info: 'bg-primary'
            }
        };
    },
    mounted() {
        // Expose to window for global access
        window.showToast = this.add;
    },
    methods: {
        add(message, type = 'info') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.remove(id), 3000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }
};
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

div {
    font-family: 'Cairo', sans-serif !important;
}
</style>
