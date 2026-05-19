import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import fs from 'fs';
import os from 'os';
import path from 'path';

// Detecta el directorio del usuario actual dinámicamente (Ej: C:\Users\TuUsuario)
const homedir = os.homedir();
const host = 'estadoprisma.test';

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
        https: {
            // Construye la ruta dinámicamente sin importar en qué PC estés
            key: fs.readFileSync(path.resolve(homedir, `.config/herd/config/valet/Certificates/${host}.key`)),
            cert: fs.readFileSync(path.resolve(homedir, `.config/herd/config/valet/Certificates/${host}.crt`)),
        },
    },
});