import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs'; // Necesario para leer los certificados
import { homedir } from 'os';
import { resolve } from 'path';

// Cambia esto si tu dominio de Herd es distinto
const host = 'espaciocore.test'; 

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // Permite conexiones externas si fuera necesario
        port: 5173,
        strictPort: true,
        https: {
            // Buscamos los certificados que Herd creó automáticamente
            key: fs.readFileSync(resolve(homedir(), `.config/herd/config/valet/Certificates/${host}.key`)),
            cert: fs.readFileSync(resolve(homedir(), `.config/herd/config/valet/Certificates/${host}.crt`)),
        },
        hmr: {
            host: host,
        },
    },
});