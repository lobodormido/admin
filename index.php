<?php 
if (!isset($_SESSION['usuario_id'])) {
    header("Location: http://localhost:8888/proyectomuni/modernizacion/admin/login/");
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Administrador | Secretaría de Modernización</title>
        <link rel="stylesheet" href="node_modules/ng-admin/build/ng-admin.min.css">
        <link rel="stylesheet" href="admin-styles.css">
    </head>
    <body ng-app="adminApp" ng-strict-di>       <!-- PARA PRODUCCION  -->
    <!-- <body ng-app="adminApp"> -->
        <div ui-view="ng-admin"></div>
        <script src="node_modules/ng-admin/build/ng-admin.min.js" type="text/javascript"></script>
        <script src="admin.js" type="text/javascript"></script>
    </body>
</html>

<style type="text/css">
    .ng-admin-entity-posts .ng-admin-column-title {
        width: 300px;
    }
</style>