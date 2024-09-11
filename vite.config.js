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
                "resources/css/barreno.css",
                "resources/css/recoverPassword.css",
                "resources/css/RegistrarOT/agregarOT.css",
                "resources/js/editarInterfaz.js",
                "resources/js/editarTabla.js",
                "resources/css/editarClase.css",
                "resources/css/procesos.css",
                "resources/css/viewpiezas.css",
                "resources/css/verProcesos.css",
                "resources/css/adminPzas.css",
                "resources/css/RegistrarOT/agregarClass.css",
                'resources/css/maquinas.css',
                'resources/css/maquinas2.css',
                'resources/css/rectificado.css',
                'resources/css/copiado.css',
                'resources/css/elegirPieza.css',
                'resources/css/tiemposProduccion.css',
            ],
            refresh: true,
        }),
    ],
    // build: {
    //     b1ase: 'http://192.168.1.106:80/',
    // },
});
