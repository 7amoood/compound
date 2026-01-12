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
            default: 80 // Increased from 80
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
            const progress = Math.min(this.pullDelta / this.threshold, 1);
            return {
                transform: `translateY(${this.pullDelta - 40}px)`, // Offset so it stays hidden initially
                opacity: progress,
                transition: this.isDragging ? 'none' : 'transform 0.4s cubic-bezier(0.19, 1, 0.22, 1), opacity 0.3s ease'
            };
        },
        contentStyle() {
            return {
                transform: `translateY(${this.pullDelta}px)`,
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
            
            // Deliberate "Long Pull" logic:
            // First 50px: Dead zone (nothing moves)
            // 50px - 150px: UI starts moving
            const startThreshold = 50;

            if (delta > startThreshold) {
                this.isDragging = true;
                const activeDelta = delta - startThreshold;
                
                // Linear movement after threshold for predictable feel
                // max pull allowed to prevent breaking layout
                this.pullDelta = Math.min(150, activeDelta * 0.8);
                
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

            const thresholdMet = this.pullDelta >= 80;
            this.isDragging = false;
            this.canPull = false;

            if (thresholdMet) {
                this.isLoading = true;
                this.pullDelta = 60; 
                
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
    overscroll-behavior-y: contain;
}

.ptr-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 60px; /* Fixed height for the container */
    background: transparent;
    pointer-events: none;
    position: absolute;
    top: -60px; /* Start outside the view */
    left: 0;
    z-index: 50; /* Higher z-index to be sure */
}

.ptr-icon-box {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 12px rgba(0,0,0,0.15);
    color: #137fec;
    border: 1px solid rgba(0,0,0,0.05);
}

.dark .ptr-icon-box {
    background: #1e293b;
    color: #3b82f6;
    border: 1px solid #334155;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
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
