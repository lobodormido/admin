<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Ver los comentarios
$app->get('/comentarios', function(Request $request, Response $response) use ($db)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }

    // Paginación y filtros

    $_start     = (null !== $request->getParam('_start'))?     (int)$request->getParam('_start')   : 0;
    $_end       = (null !== $request->getParam('_end'))?       (int)$request->getParam('_end') : 50;
    $_order     = ("ASC" == $request->getParam('_order'))?     "ASC" : "DESC";
    $_end       = $_end - $_start;
    
    if      ($request->getParam('_sort')=='mail')      $_sort = 'mail';
    else if ($request->getParam('_sort')=='nombre')    $_sort = 'nombre';
    else if ($request->getParam('_sort')=='cuerpo')    $_sort = 'cuerpo';
    else if ($request->getParam('_sort')=='post_id')   $_sort = 'post_id';
    else    $_sort = 'id';
    //$filters    = (null !== $request->getParam('_filters'))?   $request->getParam('_filters')   : false;
    
    // SQL

    $sql = "SELECT * FROM comentarios ORDER BY $_sort $_order LIMIT :start, :end";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start',  $_start, PDO::PARAM_INT);
        $stmt->bindParam(':end',    $_end,   PDO::PARAM_INT);
        $stmt->execute();
        
        $comentarios   = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db            = null;

        $response = $response->withJson($comentarios);
        return $response;
    }
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

$app->get('/comentarios/activos', function(Request $request, Response $response) use ($db)
{
    // Paginación y filtros

    $_start     = (null !== $request->getParam('_start'))?     (int)$request->getParam('_start')   : 0;
    $_end       = (null !== $request->getParam('_end'))?       (int)$request->getParam('_end') : 50;
    $_order     = ("ASC" == $request->getParam('_order'))?     "ASC" : "DESC";
    $_end       = $_end - $_start;
    
    if      ($request->getParam('_sort')=='mail')      $_sort = 'mail';
    else if ($request->getParam('_sort')=='nombre')    $_sort = 'nombre';
    else if ($request->getParam('_sort')=='cuerpo')    $_sort = 'cuerpo';
    else if ($request->getParam('_sort')=='post_id')   $_sort = 'post_id';
    else    $_sort = 'id';
    //$filters    = (null !== $request->getParam('_filters'))?   $request->getParam('_filters')   : false;
    
    // SQL

    $sql = "SELECT * FROM comentarios WHERE activo = 1 ORDER BY $_sort $_order LIMIT :start, :end";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start',  $_start, PDO::PARAM_INT);
        $stmt->bindParam(':end',    $_end,   PDO::PARAM_INT);
        $stmt->execute();
        
        $comentarios   = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db            = null;

        $response = $response->withJson($comentarios);
        return $response;
    }
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Ver un comentario
$app->get('/comentarios/{id}', function(Request $request, Response $response) use ($db)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $id  = $request->getAttribute('id');
    $sql = "SELECT * FROM comentarios WHERE id = :id AND activo = 1";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $comentario = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $comentario = (sizeof($comentario)>0)? $comentario[0] : ['error' => 'Comentario inexistente'];
        $response = $response->withJson($comentario);
        return $response;
    }
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Ver un comentario activo (publico)
$app->get('/comentarios/activos/{id}', function(Request $request, Response $response) use ($db)
{
    $id  = $request->getAttribute('id');
    $sql = "SELECT * FROM comentarios WHERE id = :id AND activo = 1";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $comentario = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $comentario = (sizeof($comentario)>0)? $comentario[0] : ['error' => 'Comentario inexistente'];
        $response = $response->withJson($comentario);
        return $response;
    }
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});


// Agregar una noticia
$app->post('/comentarios', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }

    $nombre     = $request->getParam('nombre');
    $mail       = $request->getParam('mail');
    $cuerpo     = $request->getParam('cuerpo');
    $post_id    = $request->getParam('post_id');

    if($nombre==""||$nombre==null||$mail==""||$mail==null||$cuerpo==""||$cuerpo==null||$post_id==""||$post_id==null){
        $response = $response->withJson(['error' => 'Datos insuficientes']);
        return $response;
    }
    if(!($crud->comentarios & $_SESSION['CREATE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    $sql = "INSERT INTO comentarios (nombre, mail, cuerpo, post_id, activo) VALUES (:nombre, :mail, :cuerpo, :post_id, 1)";
    
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':mail',   $mail); 
        $stmt->bindParam(':cuerpo', $cuerpo);
        $stmt->bindParam(':post_id',$post_id); 
        
        $stmt->execute();
        $response = $response->withJson(['resultado' => 'Comentario agregado']);
        return $response;
        $db = null;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Actualizar una noticia
$app->put('/comentarios/{id}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }

    $id         = $request->getAttribute('id');
    $nombre     = $request->getParam('nombre');
    $mail       = $request->getParam('mail');
    $cuerpo     = $request->getParam('cuerpo');
    $post_id    = $request->getParam('post_id');
    $usuario_id = $_SESSION['usuario_id'];

    if($usuario_id==""||$usuario_id==null||$nombre==""||$nombre==null||$mail==""||$mail==null||$cuerpo==""||$cuerpo==null||$post_id==""||$post_id==null){
        $response = $response->withJson(['error' => 'Datos insuficientes']);
        return $response;
    }
    if(!($crud->comentarios & $_SESSION['UPDATE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    
    $sql = "UPDATE usuarios SET 
                nombre      = :nombre, 
                mail        = :mail, 
                cuerpo      = :cuerpo, 
                post_id     = :post_id
                WHERE id    = :id";
    
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':mail',   $mail); 
        $stmt->bindParam(':cuerpo', $cuerpo);
        $stmt->bindParam(':post_id',$post_id); 
        $stmt->bindParam(':id',     $id);

        $stmt->execute();
        $response = $response->withJson(['resultado' => 'Comentario modificado']);
        return $response;
        $db = null;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Borrar una noticia
$app->delete('/comentarios/{id}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }

    $id  = $request->getAttribute('id');
    $sql = "DELETE FROM comentarios WHERE id = :id";

    if(!($crud->comentarios & $_SESSION['DELETE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $db = null;

        $response = $response->withJson(['resultado' => 'Comentario eliminado']);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});