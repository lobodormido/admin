var adminApp = angular.module('adminApp', ['ng-admin']);

adminApp.config(['NgAdminConfigurationProvider', function (nga) {
    // var admin = nga.application('Home | Secretaría de Modernización')
    var admin = nga.application('Admin Home | Secretaría de Modernización',false) //<-- PARA PRODUCCION
         .baseApiUrl('http://modernizacion.laplata.gov.ar/admin/data/public/');
    //   .baseApiUrl('http://localhost:8888/proyectomuni/modernizacion/admin/data/public/');

    var user = nga.entity('usuarios');
    
    user.listView()
        .fields([
            nga.field('nombre').isDetailLink(true),
            nga.field('username'),
            nga.field('mail')
        ]);

    user.creationView().fields([
        nga.field('nombre')
            .validation({ required: true, minlength: 3, maxlength: 100 }),
        nga.field('username')
            .attributes({ placeholder: 'No se permiten espacios, mínimo 5 caracteres' })
            .validation({ required: true, pattern: '[A-Za-z0-9\.\-_]{5,20}' }),
        nga.field('mail','email')
            .validation({ required: true }),
        nga.field('direccion'),
        nga.field('ciudad'),
        nga.field('password', 'password')
    ]);

    user.editionView().fields(user.creationView().fields());

    admin.addEntity(user)

    var noticias = nga.entity('noticias');

    noticias.listView()
        .fields([
            nga.field('titulo')
                .isDetailLink(true)
                .label('Título'),
            nga.field('descripcion', 'text')
                .map(function truncate(value) {
                    if (!value) return '';
                    return value.length > 50 ? value.substr(0, 50) + '...' : value;
                })
                .label('Descripción'),
            nga.field('usuario_id', 'reference')
                .targetEntity(user)
                .targetField(nga.field('username'))
                .label('Autor'),
            nga.field('activo','choice')
            .choices([
                { value: '1', label: 'Activo' },
                { value: '0', label: 'Oculto' }
            ])
        ])
        .listActions(['show','edit'])
        .batchActions([])
        .filters([
            nga.field('q')
                .label('')
                .pinned(true)
                .template('<div class="input-group"><input type="text" ng-model="value" placeholder="Buscar" class="form-control"></input><span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span></div>'),
            nga.field('usuario_id', 'reference')
                .targetEntity(user)
                .targetField(nga.field('username'))
                .label('Autor')
        ]);

    noticias.showView().fields([
        nga.field('titulo')
            .label('Título'),
        nga.field('descripcion', 'text'),
        nga.field('cuerpo', 'wysiwyg'),
        nga.field('imagen1', 'template')
            .label('Portada')
            .template('<div ng-show="entry.values.imagen1" class="fill" style="background-image : url({{entry.values.imagen1}})"" \"></div><div ng-hide="entry.values.imagen1">Ninguna imagen cargada.</div>'),
        nga.field('imagen2', 'file')
            .label('Imagen')
            .template('<div ng-show="entry.values.imagen2" class="fill" style="background-image : url({{entry.values.imagen2}})"" \"></div><div ng-hide="entry.values.imagen2">Ninguna imagen cargada.</div>'),
        nga.field('usuario_id', 'reference')
            .targetEntity(user)
            .targetField(nga.field('username'))
            .label('Autor'),
        nga.field('creado', 'datetime'),
        nga.field('modificado', 'datetime'),
        nga.field('comentarios', 'referenced_list') // display list of related comments
            .targetEntity(nga.entity('comentarios'))
            .targetReferenceField('post_id')
            .targetFields([
                nga.field('id'),
                nga.field('creado').label('Creado'),
                nga.field('cuerpo').label('Comentario')
            ])
            .sortField('creado')
            .sortDir('DESC')
            .listActions(['edit'])
    ]);

    noticias.editionView().fields([
        nga.field('titulo')
            .label('Título')
            .validation({ required: true }),
        nga.field('imagen1', 'file')
            .label('Portada')
            //.uploadInformation((response, entry) => ({'url': `data/public/upload/${entry.id}`,'apifilename': 'imagen'})),
            .uploadInformation({ 'url': 'data/public/upload', 'data': 'noticias-imagen1', 'apifilename': 'imagen' }),
        nga.field('imagen2', 'file')
            .label('Imagen')
            .uploadInformation({ 'url': 'data/public/upload', 'data': 'noticias-imagen2'+'', 'apifilename': 'imagen' }),
        nga.field('descripcion', 'text')
            .label('Descripción')
            .validation({ required: true }),
        nga.field('cuerpo', 'wysiwyg')
            .validation({ required: true }),
        nga.field('activo', 'choice')
        .choices([
            { value: '1', label: 'Mostrar' },
            { value: '0', label: 'Ocultar' }
        ])
        .label('Activo')
    ]);

    noticias.creationView().fields(noticias.editionView().fields());
    admin.addEntity(noticias);

    var proyectos = nga.entity('proyectos');
    
    proyectos.listView()
        .fields([
            nga.field('titulo')
                .isDetailLink(true)
                .label('Título'),
            nga.field('descripcion', 'text')
                .map(function truncate(value) {
                    if (!value) return '';
                    return value.length > 50 ? value.substr(0, 50) + '...' : value;
                })
                .label('Descripción'),
            nga.field('link', 'template')
                .template('<a href="entry.values.link">{{entry.values.link}}</a>'),
            nga.field('usuario_id', 'reference')
                .targetEntity(user)
                .targetField(nga.field('username'))
                .label('Autor')
        ])
        .listActions(['show','edit'])
        .batchActions([])
        .filters([
            nga.field('q')
                .label('')
                .pinned(true)
                .template('<div class="input-group"><input type="text" ng-model="value" placeholder="Buscar" class="form-control"></input><span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span></div>'),
            nga.field('usuario_id', 'reference')
                .targetEntity(user)
                .targetField(nga.field('username'))
                .label('Autor')
        ]);

    proyectos.showView().fields([
        nga.field('titulo')
            .label('Título'),
        nga.field('link', 'template')
            .template('<a href="entry.values.link">{{entry.values.link}}</a>'),
        nga.field('descripcion', 'text'),
        nga.field('cuerpo', 'wysiwyg'),
        nga.field('imagen1', 'template')
            .label('Imagen')
            .template('<div ng-show="entry.values.imagen1" class="fill" style="background-image : url({{entry.values.imagen1}})"" \"></div><div ng-hide="entry.values.imagen1">Ninguna imagen cargada.</div>'),
        nga.field('usuario_id', 'reference')
            .targetEntity(user)
            .targetField(nga.field('username'))
            .label('Autor'),
        nga.field('comentarios', 'referenced_list') // display list of related comments
            .targetEntity(nga.entity('comentarios'))
            .targetReferenceField('post_id')
            .targetFields([
                nga.field('id'),
                nga.field('creado').label('Creado'),
                nga.field('cuerpo').label('Comentario')
            ])
            .sortField('creado')
            .sortDir('DESC')
            .listActions(['edit'])
    ]);

    proyectos.editionView().fields([
        nga.field('titulo').label('Título'),
        nga.field('link', 'template')
            .template('<a href="entry.values.link">{{entry.values.link}}</a>'),
        nga.field('imagen1', 'file')
            .label('Imagen')
            .uploadInformation({ 'url': 'data/public/upload', 'data': 'proyectos-imagen1'+'', 'apifilename': 'imagen' }),
        nga.field('descripcion', 'text').label('Descripción'),
        nga.field('cuerpo', 'wysiwyg')
    ]);

    proyectos.creationView().fields(proyectos.editionView().fields());  
    admin.addEntity(proyectos);

    var comment = nga.entity('comentarios'); // the API endpoint for users will be 'http://jsonplaceholder.typicode.com/comments/:id
    
    comment.listView().fields([
        nga.field('post_id', 'reference')
            .isDetailLink(false)
            .label('Post')
            .targetEntity(noticias)
            .targetField(nga.field('titulo').map(function truncate(value) {
                if (!value) return '';
                return value.length > 50 ? value.substr(0, 50) + '...' : value;
            }))
            .singleApiCall(ids => ({'id': ids }))
    ]);
    comment.editionView().fields([
        nga.field('post_id', 'reference')
            .label('Post')
            .targetEntity(noticias)
            .targetField(nga.field('titulo').map(function truncate(value) {
                if (!value) return '';
                return value.length > 50 ? value.substr(0, 50) + '...' : value;
            }))
            .sortField('titulo')
            .sortDir('ASC')
            .validation({ required: true })
            .remoteComplete(true, {
                refreshDelay: 200,
                searchQuery: search => ({ q: search })
            }),
        nga.field('cuerpo', 'text')
    ]);
  
    admin.addEntity(comment);

    // Agregar lo correspondiente a los permisos que tenga el usuario
    
    //admin.icon('<span class="glyphicon glyphicon-sunglasses"></span>')

    admin.menu(nga.menu()
        .addChild(nga.menu(user).icon('<span class="glyphicon glyphicon-sunglasses"></span>'))
        .addChild(nga.menu().title('Secciones')
            .addChild(nga.menu(noticias).icon('<span class="glyphicon glyphicon-fire"></span>'))
            .addChild(nga.menu(proyectos).icon('<span class="glyphicon glyphicon-tree-deciduous"></span>'))
        )
        .addChild(nga.menu().template(`
            <a href="`+admin.baseApiUrl()+'logout'+`">
                <span class="glyphicon glyphicon-log-out"></span>
                Salir
            </a>`
        ))
    );

    nga.configure(admin);
}]);

adminApp.config(['RestangularProvider', function (RestangularProvider) {
    RestangularProvider.addFullRequestInterceptor(function(element, operation, what, url, headers, params) {
        if (operation == "getList") {
            // custom pagination params
            if (params._page) {
                params._start = (params._page - 1) * params._perPage;
                params._end = params._page * params._perPage;
            }
            delete params._page;
            delete params._perPage;
            // custom sort params
            if (params._sortField) {
                params._sort = params._sortField;
                params._order = params._sortDir;
                delete params._sortField;
                delete params._sortDir;
            }
            // custom filters
            if (params._filters) {
                for (var filter in params._filters) {
                    params[filter] = params._filters[filter];
                }
                delete params._filters;
            }
        }
        return { params: params };
    });
}]);