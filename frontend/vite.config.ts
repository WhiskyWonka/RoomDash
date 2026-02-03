import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://laravel.test',
        changeOrigin: false,
        headers: {
          'X-Forwarded-Port': '80',
        },
        configure: (proxy) => {
          proxy.on('proxyReq', (proxyReq, req) => {
            // Forward the original host without the port so Stancl can parse subdomains
            const host = req.headers.host?.replace(/:\d+$/, '') ?? 'localhost';
            proxyReq.setHeader('Host', host);
          });
        },
      },
    },
  },
})
