import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/css/login.css",
                "resources/css/index.css",
                "resources/css/cepillado.css",
                "resources/css/recoverPassword.css",
                "resources/css/RegistrarOT/agregarOT.css",
                "resources/js/registrarOT.js",
                "resources/js/editarInterfaz.js",
                "resources/js/editarTabla.js",
                "resources/css/editarClase.css",
                "resources/css/procesos.css",
                "resources/css/viewpiezas.css",
                "resources/css/verProcesos.css",
                "resources/css/adminPzas.css",
            ],
            refresh: true,
        }),
    ],
    build: {
        base: 'http://192.168.1.106:80/',
    },
});
