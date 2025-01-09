import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";
import preset from "./vendor/filament/support/tailwind.config.preset";

/** @type {import('tailwindcss').Config} */
export default {
    presets: [preset],
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
        },
    },
    safelist: [
        // Blue
        "text-blue-700",
        "dark:text-blue-300",
        "bg-blue-100",
        "dark:bg-blue-900",
        "border-blue-300",
        "dark:border-blue-700",

        // Red (danger)
        "text-red-700",
        "dark:text-red-300",
        "bg-red-100",
        "dark:bg-red-900",
        "border-red-300",
        "dark:border-red-700",

        // Green
        "text-green-700",
        "dark:text-green-300",
        "bg-green-100",
        "dark:bg-green-900",
        "border-green-300",
        "dark:border-green-700",

        // Yellow (warning)
        "text-yellow-700",
        "dark:text-yellow-300",
        "bg-yellow-100",
        "dark:bg-yellow-900",
        "border-yellow-300",
    ],

    plugins: [forms, typography],
};
