<?php
// Se obtienen los permisos del usuario y se conecta con la base
try{
    $db = new db();
    $db = $db->connect();

    $permiso    = "SELECT * FROM permisos WHERE usuario_id = :usuario_id";
    $stmtPermi  = $db->prepare($permiso);
    $stmtPermi->bindParam(':usuario_id', $_SESSION['usuario_id']);
    $stmtPermi->execute();
    $crud       = $stmtPermi->fetch(PDO::FETCH_OBJ);
}
catch(PDOException $e){
    echo '{"error": {"text": '.$e->getMessage().'}';
}