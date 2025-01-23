<!-- <script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ShieldCheck, Calculator, FileText, BarChart } from 'lucide-vue-next';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    user: {
        type: Object,
        default: null
    }
});
</script>

<template>
    <Head title="Loan Tracking System" />
    <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-teal-500 selection:text-white">
            <div class="relative w-full max-w-4xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <h1 class="text-3xl font-bold text-teal-600 dark:text-teal-400">
                            Loan Management System
                        </h1>
                    </div>
                    
                    <nav v-if="canLogin" class="-mx-3 flex flex-1 justify-end">
                        <Link
                            v-if="user"
                            :href="route('dashboard')"
                            class="rounded-md px-3 py-2 text-teal-600 ring-1 ring-transparent transition hover:bg-teal-50 focus:outline-none focus-visible:ring-teal-500 dark:text-teal-400 dark:hover:bg-teal-900"
                        >
                            Dashboard
                        </Link>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-md px-3 py-2 text-teal-600 ring-1 ring-transparent transition hover:bg-teal-50 focus:outline-none focus-visible:ring-teal-500 dark:text-teal-400 dark:hover:bg-teal-900"
                            >
                                Log in
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="ml-3 rounded-md px-3 py-2 text-teal-600 ring-1 ring-transparent transition hover:bg-teal-50 focus:outline-none focus-visible:ring-teal-500 dark:text-teal-400 dark:hover:bg-teal-900"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </header>

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 xl:grid-cols-4">
                        <div 
                            class="flex flex-col items-start gap-4 rounded-lg bg-white p-6 shadow-md ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
                        >
                            <ShieldCheck class="w-12 h-12 text-teal-500" />
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                                    Secure Loan Management
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Advanced security features to protect sensitive loan and borrower information.
                                </p>
                            </div>
                        </div>

                        <div 
                            class="flex flex-col items-start gap-4 rounded-lg bg-white p-6 shadow-md ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
                        >
                            <Calculator class="w-12 h-12 text-green-500" />
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                                    Loan Calculation
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Powerful loan calculation tools with comprehensive interest and repayment tracking.
                                </p>
                            </div>
                        </div>

                        <div 
                            class="flex flex-col items-start gap-4 rounded-lg bg-white p-6 shadow-md ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
                        >
                            <FileText class="w-12 h-12 text-purple-500" />
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                                    Detailed Reporting
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Generate comprehensive reports for loan performance and financial analysis.
                                </p>
                            </div>
                        </div>

                        <div 
                            class="flex flex-col items-start gap-4 rounded-lg bg-white p-6 shadow-md ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800"
                        >
                            <BarChart class="w-12 h-12 text-red-500" />
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                                    Performance Insights
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Real-time analytics and visualization of loan portfolios.
                                </p>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-gray-600 dark:text-gray-400">
                    © {{ new Date().getFullYear() }} Dr. Khamis Al Ajmi Loan Management System
                </footer>
            </div>
        </div>
    </div>
</template> -->

<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import {
    ShieldCheck,
    Calculator,
    FileText,
    BarChart,
    BookOpen,
    Briefcase,
} from "lucide-vue-next";
import { computed } from "vue";

defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    user: {
        type: Object,
        default: null,
    },
});

const page = usePage();

const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Loan Tracking System" />
    <div
        class="relative min-h-screen bg-gradient-to-br from-indigo-100 to-violet-200 dark:from-gray-900 dark:to-gray-800 overflow-hidden"
    >
        <!-- Blur background effect -->
        <div
            class="absolute inset-0 backdrop-blur-sm bg-white/30 dark:bg-black/30"
        ></div>

        <div class="relative container mx-auto px-4 py-16 z-10">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-12">
                    <h1
                        class="text-4xl font-bold text-teal-800 dark:text-teal-300 mb-4"
                    >
                        Dr. Khamis Al Ajmi Loan Management System
                    </h1>
                    <p
                        class="text-xl text-gray-700 dark:text-gray-300 max-w-3xl mx-auto"
                    >
                        A comprehensive solution for tracking, managing, and
                        analyzing loan portfolios with precision and efficiency.
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div
                        class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-lg p-6 rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/20 transform transition hover:scale-105"
                    >
                        <ShieldCheck class="w-12 h-12 text-teal-600 mb-4" />
                        <h2
                            class="text-xl font-semibold mb-3 text-gray-800 dark:text-white"
                        >
                            Comprehensive Loan Tracking
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300">
                            Monitor every aspect of your loan portfolio with
                            detailed tracking of borrowers, loan terms,
                            repayment schedules.
                        </p>
                    </div>

                    <div
                        class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-lg p-6 rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/20 transform transition hover:scale-105"
                    >
                        <Calculator class="w-12 h-12 text-green-600 mb-4" />
                        <h2
                            class="text-xl font-semibold mb-3 text-gray-800 dark:text-white"
                        >
                            Advanced Financial Analysis
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300">
                            Powerful analytics tools to assess loan performance,
                            calculate interest, generate financial reports.
                        </p>
                    </div>

                    <div
                        class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-lg p-6 rounded-2xl shadow-xl border border-white/20 dark:border-gray-700/20 transform transition hover:scale-105"
                    >
                        <Briefcase class="w-12 h-12 text-purple-600 mb-4" />
                        <h2
                            class="text-xl font-semibold mb-3 text-gray-800 dark:text-white"
                        >
                            Risk Management
                        </h2>
                        <p class="text-gray-600 dark:text-gray-300">
                            Integrated risk assessment tools to evaluate
                            borrower creditworthiness and manage potential loan
                            defaults.
                        </p>
                    </div>
                </div>

                <div class="text-center">
                    <a
                        v-if="!user"
                        :href="route('filament.admin.auth.login')"
                        class="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition duration-300 inline-block text-lg font-semibold"
                    >
                        Login to Access System
                    </a>
                    <a
                        v-else
                        :href="route('filament.admin.pages.dashboard')"
                        class="px-6 py-3 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition duration-300 inline-block text-lg font-semibold"
                    >
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>
