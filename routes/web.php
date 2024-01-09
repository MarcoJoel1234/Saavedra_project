<?php

use App\Http\Controllers\AcabadoBombilloController;
use App\Http\Controllers\AcabadoMoldeController;
use App\Http\Controllers\AsentadoController;
use App\Http\Controllers\BarrenoManiobraController;
use App\Http\Controllers\CavidadesController;
use App\Http\Controllers\CepilladoController;
use App\Http\Controllers\ClaseController;
use App\Http\Controllers\CopiadoController;
use App\Http\Controllers\DesbasteExteriorController;
use App\Http\Controllers\GestionOTController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MolduraController;
use App\Http\Controllers\OffSetController;
use App\Http\Controllers\OTController;
use App\Http\Controllers\PalomasController;
use App\Http\Controllers\PrimeraOpeSoldaduraController;
use App\Http\Controllers\ProcesosController;
use App\Http\Controllers\ProgresoProcesosController;
use App\Http\Controllers\PySOpeSoldaduraController;
use App\Http\Controllers\PzasGeneralesController;
use App\Http\Controllers\RebajesController;
use App\Http\Controllers\RecoverPasswordController;
use App\Http\Controllers\RectificadoController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RestorePasswordController;
use App\Http\Controllers\revCalificadoController;
use App\Http\Controllers\RevLateralesController;
use App\Http\Controllers\SegundaOpeSoldaduraController;
use App\Http\Controllers\SoldaduraController;
use App\Http\Controllers\SoldaduraPTAController;
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

//Ruta para la vista de piezas por maquina
Route::get('/piezasMaquina', [PzasGeneralesController::class, 'showVistaMaquina'])->name('vistaPzasMaquina');
//Ruta para ver los procesos de las maquinas
Route::post('/piezasMaquina', [PzasGeneralesController::class, 'showMachinesProcess'])->name('showMachinesProcess');

//Vista cepillado
//Guardar encabezado de la tabla cepillado
Route::get('/cepillado/{error}', [CepilladoController::class, 'show'])->name('cepillado');
Route::post('/cepilladoHeader', [CepilladoController::class, 'storeheaderTable'])->name('cepilladoHeader');
//Guardar encabezado de la tabla cepillado
Route::get('/cepilladoHeader', [CepilladoController::class, 'storeheaderTable'])->name('cepilladoHeaderGet');
//Ruta para editar datos de la tabla cepillado
Route::post('/editCepillado', [CepilladoController::class, 'edit'])->name('editCepillado');

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

//Vista de Barreno Maniobra
Route::get('/barrenoManiobra', [BarrenoManiobraController::class, 'show'])->name('barrenoManiobra');
//Guardar encabezado de la tabla Barreno Maniobra
Route::get('/barrenoManiobraHeader', [BarrenoManiobraController::class, 'storeheaderTable'])->name('barrenoManiobraHeaderGet');
//Guardar encabezado de la tabla Barreno Maniobra
Route::post('/barrenoManiobraHeader', [BarrenoManiobraController::class, 'storeheaderTable'])->name('barrenoManiobraHeader');
//Ruta para editar datos de la tabla Barreno Maniobra
Route::post('/editBarrenoManiobra', [BarrenoManiobraController::class, 'edit'])->name('editBarrenoManiobra');

//Vista de Segunda Operacion Soldadura
Route::get('/segundaOpeSoldadura', [SegundaOpeSoldaduraController::class, 'show'])->name('segundaOpeSoldadura');
//Guardar encabezado de la tabla Segunda Operacion Soldadura
Route::get('/segundaOpeSoldaduraHeader', [SegundaOpeSoldaduraController::class, 'storeheaderTable'])->name('segundaOpeSoldaduraHeaderGet');
//Guardar encabezado de la tabla Segunda Operacion Soldadura
Route::post('/segundaOpeSoldaduraHeader', [SegundaOpeSoldaduraController::class, 'storeheaderTable'])->name('segundaOpeSoldaduraHeader');
//Ruta para editar datos de la tabla Segunda Operacion Soldadura
Route::post('/editSegundaOpeSoldadura', [SegundaOpeSoldaduraController::class, 'edit'])->name('editSegundaOpeSoldadura');

//Vista de Soldadura
Route::get('/soldadura', [SoldaduraController::class, 'show'])->name('soldadura');
//Guardar encabezado de la tabla Soldadura
Route::get('/soldaduraHeaderGet', [SoldaduraController::class, 'storeheaderTable'])->name('soldaduraHeaderGet');
//Guardar encabezado de la tabla Soldadura
Route::post('/soldaduraHeader', [SoldaduraController::class, 'storeheaderTable'])->name('soldaduraHeader');
//Ruta para editar datos de la tabla Soldadura
Route::post('/editSoldadura', [SoldaduraController::class, 'edit'])->name('editSoldadura');

//Vista soldadura PTA
Route::get('/soldaduraPTA', [SoldaduraPTAController::class, 'show'])->name('soldaduraPTA');
//Guardar encabezado de la tabla Soldadura PTA
Route::get('/soldaduraPTAHeaderGet', [SoldaduraPTAController::class, 'storeheaderTable'])->name('soldaduraPTAHeaderGet');
//Guardar encabezado de la tabla Soldadura PTA
Route::post('/soldaduraPTAHeader', [SoldaduraPTAController::class, 'storeheaderTable'])->name('soldaduraPTAHeader');
//Ruta para editar datos de la tabla Soldadura PTA
Route::post('/editSoldaduraPTA', [SoldaduraPTAController::class, 'edit'])->name('editSoldaduraPTA');

//Vista Rectificado
Route::get('/rectificado', [RectificadoController::class, 'show'])->name('rectificado');
//Guardar encabezado de la tabla Rectificado
Route::get('/rectificadoHeader', [RectificadoController::class, 'storeheaderTable'])->name('rectificadoHeaderGet');
//Guardar encabezado de la tabla Rectificado
Route::post('/rectificadoHeader', [RectificadoController::class, 'storeheaderTable'])->name('rectificadoHeader');
//Ruta para editar datos de la tabla Rectificado
Route::post('/editRectificado', [RectificadoController::class, 'edit'])->name('editRectificado');

//Vista de Asentado
Route::get('/asentado', [AsentadoController::class, 'show'])->name('asentado');
//Guardar encabezado de la tabla Asentado
Route::get('/asentadoHeader', [AsentadoController::class, 'storeheaderTable'])->name('asentadoHeaderGet');
//Guardar encabezado de la tabla Asentado
Route::post('/asentadoHeader', [AsentadoController::class, 'storeheaderTable'])->name('asentadoHeader');
//Ruta para editar datos de la tabla Asentado
Route::post('/editAsentado', [AsentadoController::class, 'edit'])->name('editAsentado');

//Vista de Calificado
Route::get('/calificado', [revCalificadoController::class, 'show'])->name('calificado');
//Guardar encabezado de la tabla Calificado
Route::get('/calificadoHeader', [revCalificadoController::class, 'storeheaderTable'])->name('calificadoHeaderGet');
//Guardar encabezado de la tabla Calificado
Route::post('/calificadoHeader', [revCalificadoController::class, 'storeheaderTable'])->name('calificadoHeader');
//Ruta para editar datos de la tabla Calificado
Route::post('/editCalificado', [revCalificadoController::class, 'edit'])->name('editCalificado');

//Vista de acabado bombillo
Route::get('/acabadoBombillo', [AcabadoBombilloController::class, 'show'])->name('acabadoBombillo');
//Guardar encabezado de la tabla acabado bombillo
Route::get('/acabadoBombilloHeader', [AcabadoBombilloController::class, 'storeheaderTable'])->name('acabadoBombilloHeaderGet');
//Guardar encabezado de la tabla acabado bombillo
Route::post('/acabadoBombilloHeader', [AcabadoBombilloController::class, 'storeheaderTable'])->name('acabadoBombilloHeader');
//Ruta para editar datos de la tabla acabado bombillo
Route::post('/editAcabadoBombillo', [AcabadoBombilloController::class, 'edit'])->name('editAcabadoBombillo');

//Vista de acabado molde
Route::get('/acabadoMolde', [AcabadoMoldeController::class, 'show'])->name('acabadoMolde');
//Guardar encabezado de la tabla acabado molde
Route::get('/acabadoMoldeHeader', [AcabadoMoldeController::class, 'storeheaderTable'])->name('acabadoMoldeHeaderGet');
//Guardar encabezado de la tabla acabado molde
Route::post('/acabadoMoldeHeader', [AcabadoMoldeController::class, 'storeheaderTable'])->name('acabadoMoldeHeader');
//Ruta para editar datos de la tabla acabado molde
Route::post('/editAcabadoMolde', [AcabadoMoldeController::class, 'edit'])->name('editAcabadoMolde');

//Vista de Cavidades
Route::get('/cavidades', [CavidadesController::class, 'show'])->name('cavidades');
//Guardar encabezado de la tabla Cavidades
Route::get('/cavidadesHeader', [CavidadesController::class, 'storeheaderTable'])->name('cavidadesHeaderGet');
//Guardar encabezado de la tabla Cavidades
Route::post('/cavidadesHeader', [CavidadesController::class, 'storeheaderTable'])->name('cavidadesHeader');
//Ruta para editar datos de la tabla Cavidades
Route::post('/editCavidades', [CavidadesController::class, 'edit'])->name('editCavidades');

//Vista de Copiado
Route::get('/copiado', [CopiadoController::class, 'show'])->name('copiado');
//Guardar encabezado de la tabla Copiado
Route::get('/copiadoHeader', [CopiadoController::class, 'storeheaderTable'])->name('copiadoHeaderGet');
//Guardar encabezado de la tabla Copiado
Route::post('/copiadoHeader', [CopiadoController::class, 'storeheaderTable'])->name('copiadoHeader');
//Ruta para editar datos de la tabla Copiado
Route::post('/editCopiado', [CopiadoController::class, 'edit'])->name('editCopiado');

//Vista de OffSet
Route::get('/offSet', [OffSetController::class, 'show'])->name('offSet');
//Guardar encabezado de la tabla OffSet
Route::get('/offSetHeader', [OffSetController::class, 'storeheaderTable'])->name('offSetHeaderGet');
//Guardar encabezado de la tabla OffSet
Route::post('/offSetHeader', [OffSetController::class, 'storeheaderTable'])->name('offSetHeader');
//Ruta para editar datos de la tabla OffSet
Route::post('/editOffSet', [OffSetController::class, 'edit'])->name('editOffSet');

//Vista de Palomas
Route::get('/palomas', [PalomasController::class, 'show'])->name('palomas');
//Guardar encabezado de la tabla Palomas
Route::get('/palomasHeader', [PalomasController::class, 'storeheaderTable'])->name('palomasHeaderGet');
//Guardar encabezado de la tabla Palomas
Route::post('/palomasHeader', [PalomasController::class, 'storeheaderTable'])->name('palomasHeader');
//Ruta para editar datos de la tabla Palomas
Route::post('/editPalomas', [PalomasController::class, 'edit'])->name('editPalomas');

//Vista de Rebajes
Route::get('/rebajes', [RebajesController::class, 'show'])->name('rebajes');
//Guardar encabezado de la tabla Rebajes
Route::get('/rebajesHeader', [RebajesController::class, 'storeheaderTable'])->name('rebajesHeaderGet');
//Guardar encabezado de la tabla Rebajes
Route::post('/rebajesHeader', [RebajesController::class, 'storeheaderTable'])->name('rebajesHeader');
//Ruta para editar datos de la tabla Rebajes
Route::post('/editRebajes', [RebajesController::class, 'edit'])->name('editRebajes');



// //Vista de Primera y Segunda Operacion Soldadura Equipo
// Route::get('/1y2OpeSoldadura', [PySOpeSoldaduraController::class, 'show'])->name('1y2OpeSoldadura');
// //Guardar encabezado de la tabla Primera y Segunda Operacion Soldadura
// Route::get('/1y2OpeSoldaduraHeader', [PySOpeSoldaduraController::class, 'storeheaderTable'])->name('1y2OpeSoldaduraHeaderGet');
// //Guardar encabezado de la tabla Primera y Segunda Operacion Soldadura
// Route::post('/1y2OpeSoldaduraHeader', [PySOpeSoldaduraController::class, 'storeheaderTable'])->name('1y2OpeSoldaduraHeader');
// //Ruta para editar datos de la tabla Primera y Segunda Operacion Soldaduraedit
// Route::post('/edit1y2OpeSoldadura', [PySOpeSoldaduraController::class, 'edit'])->name('edit1y2OpeSoldadura');