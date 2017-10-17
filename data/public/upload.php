<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Agregar un archivo
$app->post('/upload', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
        die();
    }

    $seccion = $request->getParams()['data'];

    if(!($crud->upload & $_SESSION['CREATE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
    if(!$seccion){
        $response = $response->withJson(['error' => 'Error en la sección']);
        return $response;
    }
    
    if(isset($_FILES['file']))
    {
        $file_name      = $_FILES['file']['name'];
        $file_size      = $_FILES['file']['size'];
        $file_tmp       = $_FILES['file']['tmp_name'];
        $file_type      = $_FILES['file']['type'];
        $file_ext       = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $extensions     = array("jpeg", "jpg", "png", "pdf", "gif", "image/png", "image/jpg", "image/jpeg");
        
        $arraynames     = explode("-", $seccion, 2);
        $folder         = $arraynames[0];
        $new_file_name  = $arraynames[1];

        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

        if(in_array($file_ext, $extensions) === false)
        {
            $response = $response->withJson(['error' => "La extensión del archivo no está permitida, por favor suba un archivo JPEG, PNG o PDF"]);
        }
        else if($file_size > 509242880)
        {
            $response = $response->withJson(['error' => 'El archivo no puede exeder los 5MB']);
        }
        else
        {
            $random     = rand();
            $folderPath = realpath(dirname())."/uploads/".$folder;
            $urlPath    = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."s/".$folder;
            $filePath   = "/".$new_file_name."-".$random.".".$file_ext;
            
            if (!file_exists($folderPath))
                mkdir($folderPath, 0777, true);

            if (!move_uploaded_file($file_tmp, $folderPath.$filePath))
            {
                $response = $response->withJson(['error' => 'No se pudo almacenar el archivo']);
            }
            else
            {
                $response = $response->withJson(['imagen' => $urlPath.$filePath]);
            }
        }
    }
    else{
        $response = $response->withJson(['error' => 'No se recibió archivo']);
    }
    
    return $response;

});

// Borrar un archivo
$app->delete('/upload/{file}', function(Request $request, Response $response) use ($db, $crud)
{
    if (!isset($_SESSION['usuario_id'])) {
        return $response->withStatus(200)->withHeader('Location', $_SESSION['dir_base'].'/login/');
        die();
    }

    $id  = $request->getAttribute('file');

    if(!($crud->upload & $_SESSION['DELETE'])){
        $response = $response->withJson(['error' => 'No posee permiso para realizar esta acción']);
        return $response;
    }
});