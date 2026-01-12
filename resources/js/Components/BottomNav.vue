<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 flex justify-around items-center px-2 fixed bottom-0 left-0 right-0 max-w-md mx-auto z-20 py-2">
        <Link href="/dashboard" class="flex flex-col items-center justify-center w-16 gap-1" :class="$page.url.startsWith('/dashboard') ? 'text-primary' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'">
            <span class="material-symbols-outlined">home</span>
            <span class="text-[10px] font-medium">الرئيسية</span>
        </Link>
        <Link href="/settings" class="flex flex-col items-center justify-center w-16 gap-1" :class="$page.url.startsWith('/settings') ? 'text-primary' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'">
            <span class="material-symbols-outlined">settings</span>
            <span class="text-[10px] font-medium">الإعدادات</span>
        </Link>
        <Link href="/logout" method="post" as="button" class="flex flex-col items-center justify-center w-16 gap-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
            <span class="material-symbols-outlined">logout</span>
            <span class="text-[10px] font-medium">خروج</span>
        </Link>
    </div>
</template>
