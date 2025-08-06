$(document).ready(function() {
    $('#models-table').DataTable({
        responsive: true,
        autoWidth: false,
        lengthChange: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 25,
        order: [[0, 'asc']], // Sort by model name ascending
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4] // Exclude Actions column (column 5)
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm',
                filename: 'ml_models_' + new Date().toISOString().slice(0,10),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'ml_models_' + new Date().toISOString().slice(0,10),
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                },
                customize: function (doc) {
                    doc.content[1].table.widths = ['20%', '15%', '30%', '15%', '20%'];
                    doc.styles.tableHeader.fontSize = 8;
                    doc.defaultStyle.fontSize = 7;
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                filename: 'ml_models_' + new Date().toISOString().slice(0,10),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4]
                }
            },
            {
                extend: 'colvis',
                text: '<i class="fas fa-eye"></i> Columns',
                className: 'btn btn-secondary btn-sm',
                columns: ':not(.no-export)'
            }
        ],
        columnDefs: [
            { responsivePriority: 1, targets: 0 }, // Model Name
            { responsivePriority: 2, targets: 1 }, // Library Type
            { responsivePriority: 3, targets: 3 }, // Status
            { responsivePriority: 4, targets: 5 }, // Actions (back to column 5)
        ],
        language: {
            search: "Search models:",
            lengthMenu: "Show _MENU_ models per page",
            info: "Showing _START_ to _END_ of _TOTAL_ models",
            infoEmpty: "No models found",
            infoFiltered: "(filtered from _MAX_ total models)",
            emptyTable: "No ML models available",
            zeroRecords: "No matching models found",
            buttons: {
                copy: 'Copy to clipboard',
                csv: 'Export to CSV',
                excel: 'Export to Excel',
                pdf: 'Export to PDF',
                print: 'Print table',
                colvis: 'Column visibility'
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>' +
             '<"row"<"col-sm-12 col-md-7"B><"col-sm-12 col-md-5"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
});
