<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Ver todos los usuarios
$app->get('/usuarios', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }

    $sqlPermiso = "";
    if(!($crud->usuarios & $_SESSION['READ'])){
        $sqlPermiso = " AND id = ".$_SESSION['usuario_id']." ";
    }

    // Paginación y filtros

    $_start     = (null !== $request->getParam('_start'))?     (int)$request->getParam('_start') : 0;
    $_end       = (null !== $request->getParam('_end'))?       (int)$request->getParam('_end')   : 50;
    $_order     = ("ASC" == $request->getParam('_order'))?     "ASC" : "DESC";
    $_end       = $_end - $_start;

    if      ($request->getParam('_sort')=='username')  $_sort = 'username';
    else if ($request->getParam('_sort')=='nombre')    $_sort = 'nombre';
    else if ($request->getParam('_sort')=='mail')      $_sort = 'mail';
    else if ($request->getParam('_sort')=='direccion') $_sort = 'direccion';
    else if ($request->getParam('_sort')=='ciudad')    $_sort = 'ciudad';
    else    $_sort = 'id';
    //$filters    = (null !== $request->getParam('_filters'))?   $request->getParam('_filters')   : false;
    
    // SQL -- El filtro "_end" en realidad indica cuantos elementos por pagina se quieren mostrar

    $sql = "SELECT * FROM usuarios WHERE activo = 1 ".$sqlPermiso." ORDER BY $_sort $_order LIMIT :start, :end";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start',  $_start, PDO::PARAM_INT);
        $stmt->bindParam(':end',    $_end,   PDO::PARAM_INT);
        $stmt->execute();
        
        $usuarios   = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db         = null;

        $response = $response->withJson($usuarios);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Ver un usuario
$app->get('/usuarios/{id}', function(Request $request, Response $response) use ($db)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $id  = $request->getAttribute('id');
    $sql = "SELECT * FROM usuarios WHERE id = :id AND activo = 1";

    if(!($crud->usuarios & $_SESSION['READ'])){
        $id = $_SESSION['usuario_id'];
    }

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $usuario = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        $usuario = (sizeof($usuario)>0)? $usuario[0] : ['error' => 'Usuario inexistente'];
        $response = $response->withJson($usuario);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Agregar un usuario
$app->post('/usuarios', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $username   = $request->getParam('username');
    $mail       = $request->getParam('mail');
    $nombre     = $request->getParam('nombre');
    $direccion  = $request->getParam('direccion');
    $ciudad     = $request->getParam('ciudad');
    $permisos   = 15;
    $password   = $request->getParam('password');
    // $activo     = $request->getParam('activo');
    $activo     = 1;

    if($username==""||$username==null||$mail==""||$mail==null){
        $response = $response->withJson(['error' => 'Datos insuficientes']);
        return $response;
    }
    if(!($crud->usuarios & $_SESSION['CREATE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    $sql = "INSERT INTO usuarios (username, mail, nombre, direccion, ciudad, permisos, activo, password) VALUES (:username, :mail, :nombre, :direccion, :ciudad, :permisos, :activo, :password)";
    
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username',   $username);
        $stmt->bindParam(':mail',       $mail); 
        $stmt->bindParam(':nombre',     $nombre);
        $stmt->bindParam(':direccion',  $direccion); 
        $stmt->bindParam(':ciudad',     $ciudad); 
        $stmt->bindParam(':permisos',   $permisos); 
        $stmt->bindParam(':activo',     $activo); 
        $stmt->bindParam(':password',   $password); 
        
        $stmt->execute();
        $response = $response->withJson(['resultado' => 'Usuario agregado']);
        $db = null;
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Actualizar un usuario
$app->put('/usuarios/{id}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }

    $id         = $request->getAttribute('id');
    $username   = $request->getParam('username');
    $mail       = $request->getParam('mail');
    $nombre     = $request->getParam('nombre');
    $direccion  = $request->getParam('direccion');
    $ciudad     = $request->getParam('ciudad');
    $permisos   = $request->getParam('permisos');
    $activo     = $request->getParam('activo');
    $usuario_id = $_SESSION['usuario_id'];

    if($username==""||$username==null||$usuario_id==""||$usuario_id==null||$id==""||$id==null){
        return $response->withJson(['error' => 'Datos insuficientes']);
    }
    if((!($crud->usuarios & $_SESSION['UPDATE']))&&($id != $_SESSION['usuario_id'])){
        return $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
    }
    
    $sql = "UPDATE usuarios SET 
                username    = :username, 
                mail        = :mail, 
                nombre      = :nombre, 
                direccion   = :direccion,
                ciudad      = :ciudad, 
                permisos    = :permisos, 
                activo      = :activo
                WHERE id    = :id";
    
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username',   $username);
        $stmt->bindParam(':mail',       $mail); 
        $stmt->bindParam(':nombre',     $nombre);
        $stmt->bindParam(':direccion',  $direccion); 
        $stmt->bindParam(':ciudad',     $ciudad); 
        $stmt->bindParam(':permisos',   $permisos); 
        $stmt->bindParam(':activo',     $activo); 
        $stmt->bindParam(':id',         $id);

        $stmt->execute();

        $response = $response->withJson(['resultado' => 'Usuario modificado']);
        $db = null;
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Borrar un usuario
$app->delete('/usuarios/{id}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $id  = $request->getAttribute('id');
    $sql = "DELETE FROM usuarios WHERE id = :id";

    if(!($crud->usuarios & $_SESSION['DELETE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $db = null;

        $response = $response->withJson(['resultado' => 'Usuario eliminado']);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});