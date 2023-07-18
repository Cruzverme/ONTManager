var body = $('#page-wrapper');

$(document).ready(function(){
  $('#tabelaLog').DataTable({
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'ajax': {
      'url':'dataFile.php',
      "dataSrc": "data",
      "processData": true,
    },
    'columns': [
      { data: 'contrato' },
      { data: 'registro' },
      { data: 'codigo_usuario' },
      { data: 'mac' },
      { data: 'cto' },
      { data: 'horario' },
    ]
  });
});