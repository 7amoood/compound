<template>
    <div 
        ref="container"
        class="pull-to-refresh-wrapper"
    >
        <!-- The Indicator -->
        <div 
            class="ptr-indicator"
            :style="indicatorStyle"
        >
            <div class="ptr-icon-box" :class="{ 'ptr-loading': isLoading }">
                <span class="material-symbols-outlined font-bold" :style="iconRotateStyle">
                    {{ isLoading ? 'progress_activity' : 'arrow_downward' }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div :style="contentStyle" class="ptr-content">
            <slot />
        </div>
    </div>
</template>

<script>
export default {
    name: 'PullToRefresh',
    props: {
        threshold: {
            type: Number,
            default: 60 // Reduced from 80 for easier trigger
        },
        onRefresh: {
            type: Function,
            required: true
        }
    },
    data() {
        return {
            startY: 0,
            currentY: 0,
            pullDelta: 0,
            isLoading: false,
            isDragging: false,
            canPull: false
        };
    },
    computed: {
        indicatorStyle() {
            const opacity = Math.min(this.pullDelta / this.threshold, 1);
            return {
                height: `${this.pullDelta}px`,
                opacity: opacity,
                transition: this.isDragging ? 'none' : 'height 0.4s cubic-bezier(0.19, 1, 0.22, 1), opacity 0.3s ease'
            };
        },
        contentStyle() {
            // Calculate opacity based on pull distance (fade out content to focus on loader)
            const minOpacity = 0.25;
            const progress = Math.min(this.pullDelta / this.threshold, 1);
            const opacity = 1 - (progress * (1 - minOpacity));

            return {
                transform: `translateY(${this.pullDelta}px)`,
                opacity: opacity,
                transition: this.isDragging ? 'none' : 'transform 0.4s cubic-bezier(0.19, 1, 0.22, 1), opacity 0.4s ease'
            };
        },
        iconRotateStyle() {
            if (this.isLoading) return {};
            return {
                transform: `rotate(${this.pullDelta > this.threshold ? 180 : 0}deg)`,
                transition: 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)'
            };
        }
    },
    mounted() {
        // We use non-passive listeners to allow e.preventDefault()
        this.$refs.container.addEventListener('touchstart', this.onTouchStart, { passive: true });
        this.$refs.container.addEventListener('touchmove', this.onTouchMove, { passive: false });
        this.$refs.container.addEventListener('touchend', this.onTouchEnd, { passive: true });
    },
    beforeUnmount() {
        if (this.$refs.container) {
            this.$refs.container.removeEventListener('touchstart', this.onTouchStart);
            this.$refs.container.removeEventListener('touchmove', this.onTouchMove);
            this.$refs.container.removeEventListener('touchend', this.onTouchEnd);
        }
    },
    methods: {
        onTouchStart(e) {
            if (this.isLoading) return;
            
            const scrollParent = this.getScrollParent(this.$refs.container);
            let scrollTop = 0;
            
            if (scrollParent) {
                scrollTop = scrollParent.scrollTop;
            } else {
                scrollTop = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
            }

            if (scrollTop <= 0) {
                this.canPull = true;
                this.startY = e.touches[0].clientY;
            } else {
                this.canPull = false;
            }
        },
        onTouchMove(e) {
            if (!this.canPull || this.isLoading) return;

            const currentY = e.touches[0].clientY;
            const delta = currentY - this.startY;

            if (delta > 0) {
                this.isDragging = true;
                // Increased sensitivity (removed resistance slightly)
                this.pullDelta = Math.pow(delta, 0.85);
                
                // Prevent native scrolling when pulling down
                if (e.cancelable) {
                    e.preventDefault();
                }
            } else {
                this.pullDelta = 0;
                this.isDragging = false;
            }
        },
        async onTouchEnd() {
            if (!this.canPull || this.isLoading) return;

            const thresholdMet = this.pullDelta >= this.threshold;
            this.isDragging = false;
            this.canPull = false;

            if (thresholdMet) {
                this.isLoading = true;
                this.pullDelta = 50; // Fixed loading position
                
                try {
                    await this.onRefresh();
                } finally {
                    this.isLoading = false;
                    this.pullDelta = 0;
                }
            } else {
                this.pullDelta = 0;
            }
        },
        getScrollParent(el) {
            let parent = el.parentElement;
            while (parent) {
                const style = window.getComputedStyle(parent);
                const overflowY = style.getPropertyValue('overflow-y');
                if ((overflowY === 'auto' || overflowY === 'scroll') && parent.scrollHeight > parent.clientHeight) {
                    return parent;
                }
                parent = parent.parentElement;
            }
            return null;
        }
    }
};
</script>

<style scoped>
.pull-to-refresh-wrapper {
    position: relative;
    width: 100%;
    overscroll-behavior-y: contain; /* Prevent native pull-to-refresh */
}

.ptr-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    width: 100%;
    background: transparent;
    pointer-events: none;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 10;
}

.ptr-icon-box {
    width: 36px;
    height: 36px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    color: #137fec;
}

.dark .ptr-icon-box {
    background: #1e293b;
    color: #3b82f6;
    border: 1px solid #334155;
}

.ptr-loading span {
    animation: ptr-spin 1s linear infinite;
}

@keyframes ptr-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.ptr-content {
    will-change: transform;
}
</style>
