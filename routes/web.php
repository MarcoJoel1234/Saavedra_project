<?php

use App\Http\Controllers\CepilladoController;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\DesbasteExteriorController;
use App\Http\Controllers\GestionOTController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MolduraController;
use App\Http\Controllers\OTController;
use App\Http\Controllers\PrimeraOpeSoldaduraController;
use App\Http\Controllers\ProcesosController;
use App\Http\Controllers\ProgresoProcesosController;
use App\Http\Controllers\PySOpeSoldaduraController;
use App\Http\Controllers\PzasGeneralesController;
use App\Http\Controllers\RecoverPasswordController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RestorePasswordController;
use App\Http\Controllers\RevLateralesController;
use App\Http\Controllers\SegundaOpeSoldaduraController;
use App\Models\RevLaterales;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//Vista registro
Route::get('/register', [RegisterController::class, 'show'])->name('register');
//Registrar usuario
Route::post('/register', [RegisterController::class, 'register'])->name('registerUser');
//Vista login
Route::get('/login', [LoginController::class, 'show'])->name('login');
//Ingresar en el login
Route::post('/login', [LoginController::class, 'login'])->name('loginUser');
//Vista Home
Route::get('/home', [HomeController::class, 'index'])->name('home');
//logout
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
//Vista recuperar contraseña
Route::get('/recoverPassword', [RecoverPasswordController::class, 'show'])->name('recoverPassword');
//Recuperar contraseña
Route::post('/recover', [RecoverPasswordController::class, 'recover'])->name('recover');

//VISTA MOLDURAS
//Vista registrar moldura
Route::get('/registerMoldura', [MolduraController::class, 'create'])->name('registerMoldura');
//Vista buscar moldura
Route::post('/searchMoldura', [MolduraController::class, 'show'])->name('searchMoldura');
//Registrar moldura
Route::post('/registerMolduras', [MolduraController::class, 'store'])->name('registerMolduras');
//Eliminar moldura
Route::get('/deleteMoldura', [MolduraController::class, 'destroy'])->name('deleteMoldura');

//Vista registrar OT
Route::get('/registerOT', [OTController::class, 'show'])->name('registerOT');
//Registrar OT
Route::post('/saveOT', [OTController::class, 'store'])->name('saveOT');
//Vista registrar clase
Route::get('/registerClass/{ot}', [OTController::class, 'registerClass'])->name('registerClass');
//Informacion sobre piezas agregadas
Route::post('/saveClass', [OTController::class, 'saveProcess'])->name('saveClass');
//Eliminar clase
Route::get('/deleteClass/{clase}/{claseIndice}', [OTController::class, 'deleteClass'])->name('deleteClass');
//Eliminar ot
Route::get('/deleteOT/{ot}', [OTController::class, 'deleteOT'])->name('deleteOT');
//Editar clase
Route::get('/editClase/{clase}', [OTController::class, 'editClass'])->name('editClase');
//Mostrar clases
Route::get('/clases/{ot}', [OTController::class, 'mostrarClases'])->name('mostrarClases');

//Guardar datos de HeaderProcess
Route::post('/saveHeader', [OTController::class, 'saveHeader'])->name('saveHeader');

//Vista cepillado
Route::get('/cepillado', [CepilladoController::class, 'show'])->name('cepillado');

//Guardar encabezado de la tabla cepillado
Route::post('/cepilladoHeader', [CepilladoController::class, 'storeheaderTable'])->name('cepilladoHeader');

//Guardar encabezado de la tabla cepillado
Route::get('/cepilladoHeader', [CepilladoController::class, 'storeheaderTable'])->name('cepilladoHeaderGet');

//Ruta para editar datos de la tabla cepillado
Route::post('/editCepillado', [CepilladoController::class, 'edit'])->name('editCepillado');

//Ruta para la interfaz de los procesos para editar las cotas nominales y tolerancias
Route::get('/procesos', [ProcesosController::class, 'show'])->name('procesos');
//Ruta para la interfaz de los procesos para editar las cotas nominales y tolerancias
Route::post('/procesos', [ProcesosController::class, 'verificarProceso'])->name('verificarProceso');

Route::get('/piezas', [GestionOTController::class, 'show'])->name('vistaPiezas');
Route::post('/updatePiezas', [GestionOTController::class, 'terminarPedido'])->name('UpdatePiezas');

//Ruta para ver el progreso de los procesos
Route::get('/progresoOT', [ProgresoProcesosController::class, 'show'])->name('verProcesos');

//Ruta para la vista general de piezas
Route::get('/piezasGenerales', [PzasGeneralesController::class, 'show'])->name('vistaPzasGenerales');
//Ruta para el controlador de piezas generales
Route::post('/searchPiezas', [PzasGeneralesController::class, 'search'])->name('searchPzasGenerales');

//Vista de Desbaste exterior
Route::get('/desbasteExterior', [DesbasteExteriorController::class, 'show'])->name('desbasteExterior');
//Guardar encabezado de la tabla Desbaste Exterior
Route::get('/desbasteHeader', [DesbasteExteriorController::class, 'storeheaderTable'])->name('desbasteHeaderGet');
//Guardar encabezado de la tabla Desbaste Exterior
Route::post('/desbasteHeader', [DesbasteExteriorController::class, 'storeheaderTable'])->name('desbasteHeader');
//Ruta para editar datos de la tabla Desbaste Exterior
Route::post('/editDesbaste', [DesbasteExteriorController::class, 'edit'])->name('editDesbaste');

//Vista de Revision laterales
Route::get('/revisionLaterales', [RevLateralesController::class, 'show'])->name('revisionLaterales');
//Guardar encabezado de la tabla Desbaste Exterior
Route::get('/revLateralesHeader', [RevLateralesController::class, 'storeheaderTable'])->name('revLateralesHeaderGet');
//Guardar encabezado de la tabla Desbaste Exterior
Route::post('/revLateralesHeader', [RevLateralesController::class, 'storeheaderTable'])->name('revLateralesHeader');
//Ruta para editar datos de la tabla Desbaste Exterior
Route::post('/editRevLaterales', [RevLateralesController::class, 'edit'])->name('editRevLaterales');

//Vista de Primera Operacion Soldadura
Route::get('/primeraOpeSoldadura', [PrimeraOpeSoldaduraController::class, 'show'])->name('primeraOpeSoldadura');
//Guardar encabezado de la tabla Primera Operacion Soldadura
Route::get('/primeraOpeSoldaduraHeader', [PrimeraOpeSoldaduraController::class, 'storeheaderTable'])->name('primeraOpeSoldaduraHeaderGet');
//Guardar encabezado de la tabla Primera Operacion Soldadura
Route::post('/primeraOpeSoldaduraHeader', [PrimeraOpeSoldaduraController::class, 'storeheaderTable'])->name('primeraOpeSoldaduraHeader');
//Ruta para editar datos de la tabla Primera Operacion Soldaduraedit
Route::post('/editPrimeraOpeSoldadura', [PrimeraOpeSoldaduraController::class, 'edit'])->name('editPrimeraOpeSoldadura');

//Vista de Segunda Operacion Soldadura
Route::get('/segundaOpeSoldadura', [SegundaOpeSoldaduraController::class, 'show'])->name('segundaOpeSoldadura');
//Guardar encabezado de la tabla Segunda Operacion Soldadura
Route::get('/segundaOpeSoldaduraHeader', [SegundaOpeSoldaduraController::class, 'storeheaderTable'])->name('segundaOpeSoldaduraHeaderGet');
//Guardar encabezado de la tabla Segunda Operacion Soldadura
Route::post('/segundaOpeSoldaduraHeader', [SegundaOpeSoldaduraController::class, 'storeheaderTable'])->name('segundaOpeSoldaduraHeader');
//Ruta para editar datos de la tabla Segunda Operacion Soldadura
Route::post('/editSegundaOpeSoldadura', [SegundaOpeSoldaduraController::class, 'edit'])->name('editSegundaOpeSoldadura');

//Vista de Primera y Segunda Operacion Soldadura Equipo
Route::get('/1y2OpeSoldadura', [PySOpeSoldaduraController::class, 'show'])->name('1y2OpeSoldadura');
//Guardar encabezado de la tabla Primera y Segunda Operacion Soldadura
Route::get('/1y2OpeSoldaduraHeader', [PySOpeSoldaduraController::class, 'storeheaderTable'])->name('1y2OpeSoldaduraHeaderGet');
//Guardar encabezado de la tabla Primera y Segunda Operacion Soldadura
Route::post('/1y2OpeSoldaduraHeader', [PySOpeSoldaduraController::class, 'storeheaderTable'])->name('1y2OpeSoldaduraHeader');
//Ruta para editar datos de la tabla Primera y Segunda Operacion Soldaduraedit
Route::post('/edit1y2OpeSoldadura', [PySOpeSoldaduraController::class, 'edit'])->name('edit1y2OpeSoldadura');