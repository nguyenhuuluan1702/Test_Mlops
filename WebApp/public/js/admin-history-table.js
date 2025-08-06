$(document).ready(function() {
    $('#admin-history-table').DataTable({
        responsive: true,
        autoWidth: false,
        lengthChange: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 25,
        order: [[0, 'desc']], // Sort by date descending (newest first)
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6] // Exclude Access Type column
                }
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm',
                filename: 'admin_predictions_' + new Date().toISOString().slice(0,10),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm',
                filename: 'admin_predictions_' + new Date().toISOString().slice(0,10),
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                },
                customize: function (doc) {
                    doc.content[1].table.widths = ['15%', '15%', '12%', '12%', '12%', '12%', '12%'];
                    doc.styles.tableHeader.fontSize = 8;
                    doc.defaultStyle.fontSize = 7;
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm',
                filename: 'admin_predictions_' + new Date().toISOString().slice(0,10),
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
                }
            },            
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm',
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6]
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
            { responsivePriority: 1, targets: 0 }, // Date & Time
            { responsivePriority: 2, targets: 1 }, // Model Used
            { responsivePriority: 3, targets: 6 }, // Result
            { responsivePriority: 4, targets: 7 }, // Access Type
        ],
        language: {
            search: "Search predictions:",
            lengthMenu: "Show _MENU_ predictions per page",
            info: "Showing _START_ to _END_ of _TOTAL_ predictions",
            infoEmpty: "No predictions found",
            infoFiltered: "(filtered from _MAX_ total predictions)",
            emptyTable: "No admin predictions available",
            zeroRecords: "No matching predictions found",
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
