<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Ver todos los proyectos
$app->get('/proyectos', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
    }
    if(!($crud->proyectos & $_SESSION['READ'])){
        return $response->withJson(array());
    }

    // Paginación y filtros

    $_start     = (null !== $request->getParam('_start'))?     (int)$request->getParam('_start') : 0;
    $_end       = (null !== $request->getParam('_end'))?       (int)$request->getParam('_end')   : 50;
    $_order     = ("ASC" == $request->getParam('_order'))?     "ASC" : "DESC";
    $_end       = $_end - $_start;
    
    if      ($request->getParam('_sort')=='titulo')       $_sort = 'titulo';
    else if ($request->getParam('_sort')=='creado')       $_sort = 'creado';
    else if ($request->getParam('_sort')=='modificado')   $_sort = 'modificado';
    else if ($request->getParam('_sort')=='usuario_id')   $_sort = 'usuario_id';
    else    $_sort = 'id';
    //$filters    = (null !== $request->getParam('_filters'))?   $request->getParam('_filters')   : false;
    
    // SQL    

    $sql = "SELECT * FROM proyectos ORDER BY $_sort $_order LIMIT :start, :end";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start',  $_start, PDO::PARAM_INT);
        $stmt->bindParam(':end',    $_end,   PDO::PARAM_INT);
        $stmt->execute();

        $proyectos   = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db         = null;

        $response = $response->withJson($proyectos);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Ver todos los proyectos
$app->get('/proyectos/activos', function(Request $request, Response $response) use ($db)
{
    // Paginación y filtros

    $_start     = (null !== $request->getParam('_start'))?     (int)$request->getParam('_start') : 0;
    $_end       = (null !== $request->getParam('_end'))?       (int)$request->getParam('_end')   : 50;
    $_order     = ("ASC" == $request->getParam('_order'))?     "ASC" : "DESC";
    $_end       = $_end - $_start;
    
    if      ($request->getParam('_sort')=='titulo')       $_sort = 'titulo';
    else if ($request->getParam('_sort')=='creado')       $_sort = 'creado';
    else if ($request->getParam('_sort')=='modificado')   $_sort = 'modificado';
    else if ($request->getParam('_sort')=='usuario_id')   $_sort = 'usuario_id';
    else    $_sort = 'id';
    //$filters    = (null !== $request->getParam('_filters'))?   $request->getParam('_filters')   : false;
    
    // SQL    

    $sql = "SELECT * FROM proyectos WHERE activo = 1 ORDER BY $_sort $_order LIMIT :start, :end";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':start',  $_start, PDO::PARAM_INT);
        $stmt->bindParam(':end',    $_end,   PDO::PARAM_INT);
        $stmt->execute();

        $proyectos   = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db         = null;

        $response = $response->withJson($proyectos);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Ver un proyecto
$app->get('/proyectos/{id}', function(Request $request, Response $response) use ($db)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }
    if(!($crud->proyectos & $_SESSION['READ'])){
        return $response->withJson(array());
    }

    $id  = $request->getAttribute('id');
    $sql = "SELECT * FROM proyectos WHERE id = :id";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $proyecto= $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $proyecto = (sizeof($proyecto)>0)? $proyecto[0] : ['error' => 'Noticia inexistente'];
        $response = $response->withJson($proyecto);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});


// Ver un proyecto activo (publico)
$app->get('/proyectos/activos/{id}', function(Request $request, Response $response) use ($db)
{
    $id  = $request->getAttribute('id');
    $sql = "SELECT * FROM proyectos WHERE activo = 1 AND id = :id";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $proyecto= $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $proyecto = (sizeof($proyecto)>0)? $proyecto[0] : ['error' => 'Noticia inexistente'];
        $response = $response->withJson($proyecto);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Agregar un proyecto
$app->post('/proyectos', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $titulo         = $request->getParam('titulo');
    $descripcion    = $request->getParam('descripcion');
    $cuerpo         = $request->getParam('cuerpo');
    $imagen1        = $request->getParam('imagen1');
    $imagen2        = $request->getParam('imagen2');
    $creado         = $request->getParam('creado'); //fecha actual
    $modificado     = $request->getParam('modificado'); // blanco
    $activo         = $request->getParam('activo');
    $usuario_id     = $_SESSION['usuario_id'];
    $link           = $request->getParam('link');

    if($titulo==""||$titulo==null||$usuario_id==""||$usuario_id==null){
        $response = $response->withJson(['error' => 'Datos insuficientes']);
        return $response;
    }
    if(!($crud->proyectos & $_SESSION['CREATE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    $sql = "INSERT INTO proyectos (titulo, descripcion, cuerpo, imagen1, imagen2, creado, modificado, activo, usuario_id, link) VALUES (:titulo, :descripcion, :cuerpo, :imagen1, :imagen2, :creado, :modificado, :activo, :usuario_id, :link)";
    
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':titulo',         $titulo);
        $stmt->bindParam(':descripcion',    $descripcion); 
        $stmt->bindParam(':cuerpo',         $cuerpo);
        $stmt->bindParam(':imagen1',        $imagen1); 
        $stmt->bindParam(':imagen2',        $imagen2); 
        $stmt->bindParam(':creado',         $creado); 
        $stmt->bindParam(':modificado',     $modificado); 
        $stmt->bindParam(':activo',         $activo); 
        $stmt->bindParam(':usuario_id',     $usuario_id); 
        $stmt->bindParam(':link',           $link);

        $stmt->execute();
        $response = $response->withJson(['resultado' => 'Noticia agregada']);
        $db = null;
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Actualizar un proyecto
$app->put('/proyectos/{id}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $id             = $request->getAttribute('id');
    $titulo         = $request->getParam('titulo');
    $descripcion    = $request->getParam('descripcion');
    $cuerpo         = $request->getParam('cuerpo');
    $imagen1        = $request->getParam('imagen1');
    $imagen2        = $request->getParam('imagen2');
    $modificado     = $request->getParam('modificado'); //fecha actual
    $activo         = $request->getParam('activo');
    $usuario_id     = $_SESSION['usuario_id'];
    $link           = $request->getParam('link');

    if($titulo==""||$titulo==null||$usuario_id==""||$usuario_id==null||$id==""||$id==null){
        $response = $response->withJson(['error' => 'Datos insuficientes']);
        return $response;
    }
    if(!($crud->proyectos & $_SESSION['UPDATE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    
    $sql = "UPDATE proyectos SET 
                titulo      = :titulo, 
                descripcion = :descripcion, 
                cuerpo      = :cuerpo, 
                imagen1     = :imagen1,
                imagen2     = :imagen2, 
                modificado  = :modificado, 
                activo      = :activo, 
                usuario_id  = :usuario_id, 
                link        = :link 
                WHERE id    = :id";
    
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':titulo',         $titulo);
        $stmt->bindParam(':descripcion',    $descripcion); 
        $stmt->bindParam(':cuerpo',         $cuerpo);
        $stmt->bindParam(':imagen1',        $imagen1); 
        $stmt->bindParam(':imagen2',        $imagen2); 
        $stmt->bindParam(':modificado',     $modificado); 
        $stmt->bindParam(':activo',         $activo); 
        $stmt->bindParam(':usuario_id',     $usuario_id); 
        $stmt->bindParam(':link',           $link);
        $stmt->bindParam(':id',             $id);

        $stmt->execute();
        $response = $response->withJson(['resultado' => 'Noticia modificada']);
        $db = null;
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});

// Borrar un proyecto
$app->delete('/proyectos/{id}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');

    }

    $id  = $request->getAttribute('id');
    $sql = "DELETE FROM proyectos WHERE id = :id";

    if(!($crud->proyectos & $_SESSION['DELETE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
        return;
    }
    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $db = null;

        $response = $response->withJson(['resultado' => 'Noticia eliminada']);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;
    }
});