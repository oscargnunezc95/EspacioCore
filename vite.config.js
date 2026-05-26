import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import os from 'os';
import path from 'path';

const homedir = os.homedir();
const host = 'estadoprisma.test';

// 1. Definimos las rutas
const keyPath = path.resolve(homedir, `.config/herd/config/valet/Certificates/${host}.key`);
const certPath = path.resolve(homedir, `.config/herd/config/valet/Certificates/${host}.crt`);

// 2. Comprobamos si los archivos existen realmente
let httpsConfig = false;
if (fs.existsSync(keyPath) && fs.existsSync(certPath)) {
    httpsConfig = {
        key: fs.readFileSync(keyPath),
        cert: fs.readFileSync(certPath),
    };
}

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
        host,
        hmr: { host },
        https: httpsConfig, // Automático: usa los certificados solo si existen
    },
});