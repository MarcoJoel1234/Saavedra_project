import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                //Layout appMenu
                "resources/css/layouts/appMenu.css",
                "resources/js/layouts/appMenu.js",
                
                //View home
                "resources/css/home.css",

                //View login
                "resources/css/auth/login.css",
                
                //View moldings
                "resources/css/moldings_views/create_molding.css",
                
                //Views OT
                "resources/css/wo_views/manage_wo.css", 
                "resources/css/wo_views/show_wo.css",

                //Views users
                "resources/css/users_views/recoverPassword.css",
                
                //Views processes
                "resources/css/processes_views/cNominals.css",

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
