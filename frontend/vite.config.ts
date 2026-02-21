import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export default defineConfig({
    root: path.resolve(__dirname),
    plugins: [react(), tailwindcss()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'src'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        allowedHosts: [
            'roomdash.test',
            '.roomdash.test', // Permite todos los subdominios (tenants)
            'localhost'
        ],
        watch: {
            usePolling: true,
        },
        proxy: {
            '/api': {
                target: 'http://backend:80',
                changeOrigin: false,
            },
            '/sanctum': {
                target: 'http://backend:80',
                changeOrigin: false,
            },
        },
    },
})
