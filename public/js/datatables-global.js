$(document).ready(function() {
    
    // Lista de todos los IDs de tus tablas
    const tableIds = [
        '#tickets', 
        '#equipos', 
        '#departamentos-table', 
        '#empleados-table', 
        '#usuarios-table', 
        '#logs-table'
    ];

    // Configuración común para todas las tablas
    const dataTableOptions = {
        "language": {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            "sInfoEmpty":      "Mostrando 0 a 0 de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sSearch":         "Buscar:",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        },
        "responsive": true,
        "autoWidth": false
    };

    // Iteramos sobre cada ID para aplicar DataTables de forma SEGURA
    tableIds.forEach(function(selector) {
        var $el = $(selector);

        // 1. Verificamos si el elemento existe en la página actual
        if ($el.length) {
            
            // 2. CORRECCIÓN DEL ERROR "Non-table node initialisation (DIV)"
            // Si el selector apunta a un DIV, buscamos la tabla dentro de él.
            if ($el.prop('tagName') !== 'TABLE') {
                var $tableInside = $el.find('table');
                if ($tableInside.length) {
                    $el = $tableInside; // Ahora $el es la tabla real
                }
            }

            // 3. Inicializamos DataTables SOLO si el elemento final es una tabla
            if ($el.prop('tagName') === 'TABLE') {
                $el.DataTable(dataTableOptions);
            }
        }
    });
});