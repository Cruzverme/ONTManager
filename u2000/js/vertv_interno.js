var body = $('#page-wrapper');

$(document).ready(function () {
    var table = $('#errorTable').DataTable({
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
        },
        'ajax': {
            'url': 'Model/fetch.php',
            'dataSrc': 'data'
        },
        'columns': [
            { 'data': 'code' },
            { 'data': 'description' },
            { 'data': 'occurrences_number' },
            { 'data': 'updated_at' },
            {
                'data': null,
                'render': function (data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm edit-btn" data-code="${row.code}">
                            Editar
                        </button>
                        <button class="btn btn-danger btn-sm delete-btn" data-code="${row.code}">
                            Remover
                        </button>`;
                }
            }
        ],
        'order': [[2,'desc']]
    });

    $('#errorTable').on('click', '.edit-btn', function () {
        var code = $(this).data('code');
        var row = table.row($(this).parents('tr'));
        var data = row.data();

        var cell = row.node().getElementsByTagName('td')[1];
        var currentDescription = data.description;
        $(cell).html(`
            <input type="text" class="form-control edit-description" value="${currentDescription}" />
            <button class="btn btn-success btn-sm save-btn" data-code="${code}">Salvar</button>
            <button class="btn btn-secondary btn-sm cancel-btn">Cancelar</button>
        `);
    });

    $('#errorTable').on('click', '.save-btn', function () {
        var code = $(this).data('code');
        var row = table.row($(this).parents('tr'));
        var newDescription = $(this).siblings('.edit-description').val();


        $.ajax({
            url: 'Model/update.php',
            type: 'POST',
            data: { code: code, description: newDescription },
            success: function (response) {
                response = JSON.parse(response);
                if (response.status === 'success') {

                    row.data({
                        ...row.data(),
                        description: newDescription,
                        updated_at: response.updated_at,
                    }).draw(false);
                    alert('Descrição atualizada com sucesso.');
                } else {
                    alert('Erro ao atualizar a descrição: ' + response.message);
                }
            },
            error: function () {
                alert('Erro ao tentar atualizar a descrição.');
            }
        });
    });


    $('#errorTable').on('click', '.cancel-btn', function () {
        var row = table.row($(this).parents('tr'));
        row.invalidate().draw();
    });

    $('#errorTable').on('click', '.delete-btn', function () {
        var code = $(this).data('code');

        if (confirm('Tem certeza de que deseja remover este item?')) {

            $.ajax({
                url: 'Model/delete.php',
                type: 'POST',
                data: { code: code },
                success: function (response) {
                    response = JSON.parse(response);
                    if (response.status === 'success') {

                        table.ajax.reload(null, false);
                        alert('Item removido com sucesso.');
                    } else {
                        alert('Erro ao remover o item: ' + response.message);
                    }
                },
                error: function () {
                    alert('Erro ao tentar remover o item.');
                }
            });
        }
    });

    $('#errorForm').on('submit', function (e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: 'Model/process.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    table.row.add({
                        'code': response.data.code,
                        'description': response.data.description,
                        'occurrences_number': response.data.occurrences_number,
                        'updated_at': response.data.updated_at
                    }).draw(false);

                    $('#errorForm')[0].reset();
                } else {
                    alert('Erro: ' + response.message);
                }
            },
            error: function () {
                alert('Erro ao tentar cadastrar os dados.');
            }
        });
    });
});
