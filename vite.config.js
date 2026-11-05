import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'packages/teo/admin/resources/js/wpadmin/main.js',
                'resources/js/public/app.js'
            ],
            refresh: true,
        }),
        vue(),
    ],
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
            '@': path.resolve(__dirname, 'resources/js'),
            '@T': path.resolve(__dirname, 'resources'),
            '@acf': path.resolve(__dirname, 'packages/teo/acf/resources/js'),
            '@chat': path.resolve(__dirname, 'packages/teo/chat/resources/js'),
            '@pages': path.resolve(__dirname, 'packages/teo/pages/resources/js'),
        },
    },
    server: {
        https: false,
        host: '127.0.0.1',
        port: 5173,
        // Разрешаем загрузку модулей с локальных доменов OSPanel
        // (например, http://chatlaranuxt), иначе браузер блокирует их по CORS
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
    },
});
