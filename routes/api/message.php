use App\Http\Controllers\MessageController;
use App\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API File Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

// Messages
Route::apiResource('messages', MessageController::class)->except(['index', 'show']);
Route::delete('messages/force/{message}', [MessageController::class,
'forceDestroy'])->name('messages.force.destroy')->withTrashed();
Route::put('messages/restore/{message}', [MessageController::class,
'restore'])->name('messages.restore')->withTrashed();

// Groups
Route::apiResource('groups', GroupController::class)->except(['index', 'show']);
Route::delete('groups/force/{group}', [GroupController::class,
'forceDestroy'])->name('groups.force.destroy')->withTrashed();
Route::put('groups/restore/{group}', [GroupController::class, 'restore'])->name('groups.restore')->withTrashed();


Route::post('/messages/user/{receiverId}', [MessageController::class, 'sendToUser']);
Route::post('/messages/group/{groupId}', [MessageController::class, 'sendToGroup']);


Route::get('/', [MessageController::class, 'index']); // Liste des messages
Route::post('/', [MessageController::class, 'store']); // Envoyer un message
Route::get('{message}', [MessageController::class, 'show']); // Détails d'un message
Route::put('{message}', [MessageController::class, 'update']); // Modifier un message
Route::delete('{message}', [MessageController::class, 'destroy']); // Supprimer un message
Route::post('{message}/read', [MessageController::class, 'markAsRead']); // Marquer un message comme lu
Route::get('group/{group}', [MessageController::class, 'getGroupMessages']); // Récupérer les messages d'un groupe


// Routes pour les Groupes
Route::get('/', [GroupController::class, 'index']); // Liste des groupes
Route::post('/', [GroupController::class, 'store']); // Créer un groupe
Route::get('{group}', [GroupController::class, 'show']); // Détails d'un groupe
Route::put('{group}', [GroupController::class, 'update']); // Mettre à jour un groupe
Route::delete('{group}', [GroupController::class, 'destroy']); // Supprimer un groupe
Route::post('{group}/users/{user}', [GroupController::class, 'addUser']); // Ajouter un utilisateur à un groupe
Route::delete('{group}/users/{user}', [GroupController::class, 'removeUser']); // Retirer un utilisateur d'un groupe



});