<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSPP - Sistema de Saúde Pública e Particular - Cadastro de Paciente</title>
</head>
<body> 
</body>
</html>

<?php
include "conexao.php";
$destino = 'menu_medico.php';
$destino2 = 'cadastropaciente2.html';

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

// Adicionar as informações do paciente no banco de dados
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $pac_nome = $_POST["pac_nome"];
    $pac_cpf = $_POST["pac_cpf"];
    $pac_fone = $_POST["pac_fone"];
    $pac_dtnasc = $_POST["pac_dtnasc"];
    $pac_uf = $_POST["pac_uf"];
    $pac_senha = $_POST["pac_senha"];
    $pac_cidade = $_POST["pac_cidade"];
    $pac_email = $_POST["pac_email"];
    $pac_cfsenha = $_POST["pac_cfsenha"];
    $vsenha = validaSenha($pac_senha, $pac_cfsenha);
    $vcpf = validaCPF($pac_cpf);

    if ($vcpf == true && $vsenha == true){
        $insertDados = "INSERT INTO paciente(pac_nome, pac_cpf, pac_fone, pac_dtnasc, pac_uf, pac_senha, pac_cidade, pac_email, pac_cfsenha) 
        VALUES ('$pac_nome', '$pac_cpf', '$pac_fone', '$pac_dtnasc', '$pac_uf', '$pac_senha', '$pac_cidade', '$pac_email', '$pac_cfsenha')";
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