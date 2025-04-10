import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                //app
                "resources/css/app.css",
                "resources/js/app.js",

                //auth
                "resources/css/auth/login.css",
                
                //Home
                "resources/css/home.css",
                
                //Molding
                "resources/css/molding_views/create_molding.css",
                
                //WOrder_vies
                "resources/css/wOrder_views/manageWO.css",
                "resources/js/wOrder_views/manageWO.js",
                "resources/css/wOrder_views/showWO.css",
                "resources/js/wOrder_views/showWO.js",

                //Clases_views
                "resources/css/classes_views/procesos.css",

                //User_views
                "resources/css/users_views/recoverPassword.css",
                "resources/css/cepillado.css",
                "resources/css/barreno.css",
                "resources/js/editarInterfaz.js",
                "resources/js/editarTabla.js",
                "resources/css/editarClase.css",
                "resources/css/viewpiezas.css",
                "resources/css/verProcesos.css",
                "resources/css/adminPzas.css",
                'resources/css/maquinas.css',
                'resources/css/maquinas2.css',
                'resources/css/rectificado.css',
                'resources/css/copiado.css',
                'resources/css/elegirPieza.css',
                'resources/css/tiemposProduccion.css',
                'resources/css/dashboard.css',
                'resources/css/viewUsers.css',
                'resources/js/viewUsers.js',
            ],
            refresh: true,
        }),
    ],
    // build: {
    //     b1ase: 'http://192.168.1.106:80/',
    // },
});
