<h1>Administrador de contenido dinámico</h1>

<code>MySQL, PHP, Javascript, CSS3, HTML5. PHP Slim, AngularJS. ng-admin.</code>

El admin cuenta con herramientas para subir archivos, editar el contenido de las páginas con formato, establecer varias secciones en una página, pequeño sistema de permisos para usuarios entre otras cosas.

Pasos a seguir cuando se crea una nueva sección para la página:

- Nueva tabla con el nombre y la info de la sección. 
- Nueva Columna en tabla permisos para la sección.
- Cargar permisos en la tabla recién creada para cada usuario.
- Nuevo archivo PHP con el nombre de la sección y sus rutas con lógica de negocio.
- Nueva línea require con ruta al codigo recién mencionado en index.php dentro de la carpeta public.
- Nueva entidad en admin.js.
- Crear vistas para la entidad recien creada.
- Agregar la entidad al menú, incluirle ícono y titulo.

Levantar info en la landing:

- Crear un nuevo controlador de angular en el archivo data.js (ejemplo en home de modernización).
- Crear los ng-repeat con su correspondente información en el HTML.


<strong>TODO:</strong>

- Inspeccionar si alguno de los lugares donde hay hardcodeada una URL puede esta ser cambiada por una dinámica.
- Borrar archivos sin usar (que no estén registrados en la base de datos).
- Elegir los permisos al crear usuario nuevo.

<h5>Desarrollado integramente por el LAB IT de la Secretaria de Modernizacion de La Plata.</h5>
