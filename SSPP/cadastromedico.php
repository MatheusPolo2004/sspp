<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSPP - Sistema de Saúde Pública e Particular - Cadastro Médico</title>
</head>
<body> 
</body>
</html>

<?php
include "conexao.php";
$destino = 'index.html';
$destino2 = 'cadastromedico.html';

function validaCPF($cpf) {
 
    // Extrai somente os números
    $cpf = preg_replace( '/[^0-9]/is', '', $cpf );
     
    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;

}

//Verifica se as duas senhas são iguais
function validaSenha($senha, $confsenha){
    if($senha === $confsenha){ 
        return true;
    } else {
        return false;
    }
}

// Adicionar as informações do médico no banco de dados
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $med_nome = $_POST["med_nome"];
    $med_cpf = $_POST["med_cpf"];
    $med_email = $_POST["med_email"];
    $med_senha = $_POST["med_senha"];
    $med_dtnasc = $_POST["med_dtnasc"];
    $med_uf = $_POST["med_uf"];
    $med_sistema = $_POST["med_sistema"];
    $med_cfsenha = $_POST["med_cfsenha"];
    $med_cidade = $_POST["med_cidade"];
    $med_crm = strtoupper($_POST['med_crm']);
    $med_fone = $_POST["med_fone"];
    $med_consultorio = $_POST["med_consultorio"];
    $vsenha = validaSenha($med_senha, $med_cfsenha);
    $vcpf = validaCPF($med_cpf);

    if ($vcpf == true && $vsenha == true){
        $insertDados = "INSERT INTO medico(med_nome, med_cpf, med_email, med_senha, med_dtnasc, med_uf, med_sistema, med_cfsenha, med_cidade, med_crm, med_fone, med_consultorio) 
        VALUES ('$med_nome', '$med_cpf', '$med_email', '$med_senha', '$med_dtnasc', '$med_uf', '$med_sistema', '$med_cfsenha', '$med_cidade', '$med_crm', '$med_fone', '$med_consultorio')";
        $connection->query($insertDados);
        echo '<script type="text/javascript">';
        echo 'alert("Cadastro efetuado com sucesso!");';
        echo 'window.location.href = "'.$destino.'";';
        echo '</script>';
        $connection->close();
    }
    elseif ($vcpf == false && $vsenha == true) {
        echo '<script type="text/javascript">';
        echo 'alert("CPF inválido!");';
        echo 'window.location.href = "'.$destino2.'";';
        echo '</script>';
    }
    elseif ($vsenha == false && $vcpf == true){
        echo '<script type="text/javascript">';
        echo 'alert("As senhas não conferem!");';
        echo 'window.location.href = "'.$destino2.'";';
        echo '</script>';
    }
    else{
        echo '<script type="text/javascript">';
        echo 'alert("Dados inválidos, verifique o CPF e as senhas!");';
        echo 'window.location.href = "'.$destino2.'";'; 
        echo '</script>';
    }
}

?>