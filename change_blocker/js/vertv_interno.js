$(document).ready(function() {
    var indexLastColumn = $("#blocked_changes_customer_table").find('tr')[0].cells.length-1;

    $('#blocked_changes_customer_table').DataTable({
        'language': {
            'url': '//cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json',
        },
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url':'Model/GetBlockedCustomers.php',
            "dataSrc": "data",
            "processData": true,
        },
        'columns': [
            { data: 'contract' },
            { data: 'created_at' }
        ],
        'order': [[
            indexLastColumn, 'desc'
        ]],
        select: true
    })

    $('#add_blocker').on('click', () => {
       bootbox.prompt({
           title: 'Digite o Contrato a ser bloqueado',
           inputType: 'number',
           required: true,
           callback: (result) => {
               if (result) {
                    addContractToBlock(result).then(r => dataTable.draw());
               }
           }
       });
    });
    var dataTable = $('#blocked_changes_customer_table').DataTable();
    //select the table row
    $('#blocked_changes_customer_table tbody').on('click', 'tr', function () {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
            $('#remove_block').prop('disabled', true);
        }
        else {
            dataTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            $('#remove_block').removeAttr('disabled');
        }
    });

    //remove the table row and unblock customer
    $('#remove_block').click(function () {
        let contract = dataTable.$('tr.selected')[0].firstElementChild['textContent'];
        removeBlockedContract(contract).then(
            r => {
                dataTable.row('.selected').remove().draw(false);
                $('#remove_block').prop('disabled', true);
            }
        )
    });

    async function addContractToBlock(contract)
    {
        let url = 'Model/BlockContractChanges.php'
        let body = JSON.stringify({
            contract: contract
        });
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: body,
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            bootbox.alert(data.message);
        } catch (error) {
            console.error('Erro na requisição:', error);
            bootbox.alert(error.message);
        }
    }

    async function removeBlockedContract(contract)
    {
        let url = `Model/BlockContractChanges.php?contract=${contract}`

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            bootbox.alert(data.message);
        } catch (error) {
            console.error('Erro na requisição:', error);
            bootbox.alert(error.message);
        }
    }
});