<?php
$destino = 'login.html';

// Iniciar a sessão
session_start();

// Dados de conexão com o banco de dados
$servername = "localhost";
$username = "root";  // Seu usuário do banco de dados
$password = "";      // Sua senha do banco de dados
$dbname = "sspp"; // Nome do seu banco de dados

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar se a conexão deu certo
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Receber dados do formulário
$crm_cpf = $_POST['crm_cpf'];
$senha = $_POST['senha'];

//Valida se o login informado é CPF ou CRM e faz o redirecionamento correto
if(strlen($crm_cpf) == 11){
 $sql = "SELECT * FROM paciente WHERE pac_cpf = ? AND pac_senha = ?";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("ss", $crm_cpf, $senha); // "ss" significa que estamos esperando 2 strings
 $stmt->execute();

 // Pegar o resultado
 $result = $stmt->get_result();

 if ($result->num_rows > 0) {
    // Usuário encontrado, então iniciar a sessão
    $_SESSION['crm_cpf'] = $crm_cpf;

    // Redirecionar para a página de usuário logado
    header("Location: menu_paciente.php");
 } else {
    // Usuário ou senha inválidos
    echo '<script type="text/javascript">';
    echo 'alert("CPF e/ou senha inválidos!");';
    echo 'window.location.href = "'.$destino.'";';
    echo '</script>';
 }
}
else{
 $sql = "SELECT * FROM medico WHERE med_crm = ? AND med_senha = ?";
 $stmt = $conn->prepare($sql);
 $stmt->bind_param("ss", $crm_cpf, $senha); // "ss" significa que estamos esperando 2 strings
 $stmt->execute();

 // Pegar o resultado
 $result = $stmt->get_result();

 if ($result->num_rows > 0) {
    // Usuário encontrado, então iniciar a sessão
    $_SESSION['crm_cpf'] = $crm_cpf;

    // Redirecionar para a página de usuário logado
    header("Location: menu_medico.php");
 } else {
    // Usuário ou senha inválidos
    echo '<script type="text/javascript">';
    echo 'alert("CRM e/ou senha inválidos!");';
    echo 'window.location.href = "'.$destino.'";';
    echo '</script>';
 }
}

// Fechar a conexão
$stmt->close();
$conn->close();
?>