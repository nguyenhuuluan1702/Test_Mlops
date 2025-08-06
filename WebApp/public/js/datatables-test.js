// DataTables Test Script
console.log('DataTables Test: Script loaded');

$(document).ready(function() {
    console.log('DataTables Test: Document ready');
    console.log('jQuery version:', $.fn.jquery || 'jQuery not found');
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    
    if (typeof $.fn.DataTable !== 'undefined') {
        console.log('DataTables version:', $.fn.dataTable.version || 'Version unknown');
    }
    
    // Test basic DataTable initialization
    var tables = $('table.table');
    console.log('Found tables:', tables.length);
    
    tables.each(function(index) {
        console.log('Table ' + (index + 1) + ':', $(this).attr('id') || 'No ID', 'Rows:', $(this).find('tbody tr').length);
    });
});
