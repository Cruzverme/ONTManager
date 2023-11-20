var body = $('#page-wrapper');

$(document).ready(function(){
  var indexLastColumn = $("#tabelaLog").find('tr')[0].cells.length-1;
  $('#tabelaLog').DataTable({
    'language': {
      'url': '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
    },
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
      'url':'dataFile.php',
      "dataSrc": "data",
      "processData": true,
    },
    'columns': [
      { data: 'registro' },
      { data: 'contrato' },
      { data: 'mac' },
      { data: 'cto' },
      { data: 'horario' },
    ],
    'order': [[
      indexLastColumn, 'desc'
    ]],
  });

  $('#tabelaLog').on('dblclick', 'tbody tr', function (e) {
    e.currentTarget.classList.toggle('selected');
  });
});