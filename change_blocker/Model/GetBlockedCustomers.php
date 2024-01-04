<?php

include_once "../../db/db_config_mysql.php";
include "../../classes/verifica_sessao.php";

if ($_SESSION["block_customer_changes"] == 0) {
    echo '
        <script language= "JavaScript">
            alert("Sem Permissão de Acesso!");
            location.href="../classes/redirecionador_pagina.php";
        </script>
    ';
}

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($conectar, $_POST['search']['value']); // Search value

## Search
$searchQuery = " ";
if ($searchValue != '') {
    $searchQuery = " and (contract like '%".$searchValue."%' or  
        created_at like'%".$searchValue."%' ) ";
}

## Total number of records without filtering
$sel = mysqli_query($conectar,"select count(*) as allcount from customer_change_blocked");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($conectar,"select count(*) as allcount from customer_change_blocked WHERE 1".$searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$sqlLog = "select * from customer_change_blocked  WHERE 1".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;

$executeShowIP = mysqli_query($conectar, $sqlLog);

$logs = [];
while($logsResult = mysqli_fetch_assoc($executeShowIP)) {
    $logs[] = [
        'contract' => (string)$logsResult['contract'],
        'created_at' => $logsResult['created_at']
    ];
}
$response = [
    "draw" => intval($draw),
    "iTotalRecords" => $totalRecords,
    "iTotalDisplayRecords" => $totalRecordwithFilter,
    'data' => $logs
];

echo json_encode($response);