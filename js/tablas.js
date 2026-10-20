// tablas.js
// Inicializa DataTables en todas las tablas con la clase "tabla-datatable".
// Requiere DataTables 2.x o superior (build standalone, no necesita jQuery)
// cargado antes de este archivo.
// Las columnas de acciones se marcan en el <th> con la clase "no-ordenar".

(function () {
    'use strict';

    // Si DataTables no cargó (por ejemplo, sin conexión al CDN),
    // las tablas se muestran como HTML plano sin romper la página.
    if (typeof DataTable === 'undefined') {
        return;
    }

    var IDIOMA_ES = {
        emptyTable: 'No hay datos disponibles en la tabla',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
        infoFiltered: '(filtrado de _MAX_ registros totales)',
        lengthMenu: '_MENU_ registros por página',
        loadingRecords: 'Cargando...',
        processing: 'Procesando...',
        search: 'Buscar:',
        zeroRecords: 'No se encontraron resultados',
        paginate: {
            first: 'Primero',
            last: 'Último',
            next: 'Siguiente',
            previous: 'Anterior'
        },
        aria: {
            sortAscending: ': activar para ordenar la columna de forma ascendente',
            sortDescending: ': activar para ordenar la columna de forma descendente'
        }
    };

    function indicesNoOrdenables(tabla) {
        var indices = [];
        tabla.querySelectorAll('thead th').forEach(function (th, indice) {
            if (th.classList.contains('no-ordenar')) {
                indices.push(indice);
            }
        });
        return indices;
    }

    function inicializarTablas() {
        document.querySelectorAll('table.tabla-datatable').forEach(function (tabla) {
            var opciones = {
                language: IDIOMA_ES
            };

            var noOrdenables = indicesNoOrdenables(tabla);
            if (noOrdenables.length > 0) {
                opciones.columnDefs = [{ targets: noOrdenables, orderable: false }];
            }

            new DataTable(tabla, opciones);
        });
    }

    document.addEventListener('DOMContentLoaded', inicializarTablas);
})();
