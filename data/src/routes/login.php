<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Ver todas las noticias
$app->get('/login', function(Request $request, Response $response) use ($db)
{
    $_user = $request->getParam('user');
    $_pass = $request->getParam('pass');

    $sql = "SELECT id, username, nombre, mail, direccion, ciudad, permisos FROM usuarios WHERE activo = 1 AND username = :user AND password = :pass";

    try{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':user', $_user);
        $stmt->bindParam(':pass', $_pass);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_OBJ);
        $db = null;

        if($usuario)    $_SESSION['usuario_id'] = $usuario->id;
        else            $usuario = array('error','Usuario no encontrado');

        $response = $response->withJson($usuario);
        return $response;
    } 
    catch(PDOException $e){
        $response = $response->withJson(['error' => $e->getMessage()]);
        return $response;   
    }
});

$app->get('/logout', function(Request $request, Response $response) use ($db, $app)
{
    $_SESSION['usuario_id'] = null;
    session_destroy();
    return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
});
