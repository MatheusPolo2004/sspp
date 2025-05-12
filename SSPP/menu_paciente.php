<?php
// Iniciar a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['crm_cpf'])) {
    header("Location: login.html");
    exit();
}

// Obtém o CPF da sessão
$pacienteCpf = $_SESSION['crm_cpf'];

// Configurações de conexão com o banco de dados
$host = "localhost";
$user = "root";
$password = "";
$dbname = "sspp";

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Busca o nome do paciente logado
$sqlNome = "SELECT pac_nome as nome FROM paciente WHERE pac_cpf = ?";
$stmtNome = $conn->prepare($sqlNome);
$stmtNome->bind_param("s", $pacienteCpf);
$stmtNome->execute();
$resultNome = $stmtNome->get_result();
$nomeUsuario = ($resultNome->num_rows > 0) ? $resultNome->fetch_assoc()['nome'] : "Paciente";

// Consulta para buscar os exames do paciente logado
$sql = "SELECT e.ex_id, date_format(e.ex_data,'%d/%m/%Y') as ex_data, e.ex_descricao, m.med_nome, e.ex_sistema, e.ex_area, e.ex_arquivo 
        FROM exames AS e
        INNER JOIN medico AS m ON e.ex_med_crm = m.med_crm
        WHERE e.ex_pac_cpf = ? ORDER BY e.ex_data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $pacienteCpf);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSPP - Sistema de Saúde Pública e Particular</title>
    <link rel="stylesheet" href="menu_paciente.css">
    <link rel="icon" type="image/x-icon" href="img/icon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>

    <!-- Cabeçalho -->
    <header class="header">
        <h1 id="titulo"><strong id="sigla">SSPP - </strong>SISTEMA DE SAÚDE PÚBLICA E PARTICULAR</h1>
    </header>

    <!-- Mensagem de boas-vindas com o nome do usuário -->
    <section class="welcome">
        <h2>Olá, <?php echo htmlspecialchars($nomeUsuario); ?>!</h2>
        <p>Aqui estão seus prontuários e exames lançados! Clique em "Download" para baixar o PDF do exame.</p>

        <!-- Tabela de Prontuários -->
        <section id="prontuarios" class="records">
            <h2>Histórico</h2>
            <div class="table-container">
                <table class="centered-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo de Exame</th>
                            <th>Médico</th>
                            <th>Sistema</th>
                            <th>Área</th>
                            <th>Baixar PDF</th>
                            <th>Visualizar PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Verifica se há registros
                        if ($result->num_rows > 0) {
                            // Exibe os dados de cada linha
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row["ex_data"] . "</td>";
                                echo "<td>" . $row["ex_descricao"] . "</td>";
                                echo "<td>" . $row["med_nome"] . "</td>";
                                echo "<td>" . $row["ex_sistema"] . "</td>";
                                echo "<td>" . $row["ex_area"] . "</td>";
                                echo "<td><a href='download.php?id=" . $row["ex_id"] . "'>Download</a></td>";
                                echo "<td><a href='view.php?id=" . $row["ex_id"] . "' class='btn-visualizar' target='_blank'>Visualizar</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>Nenhum prontuário encontrado!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Botão para sair -->
        <a href="logout.php" class="btn btn-sair">Sair</a>
    </section>

</body>
</html>

<?php
// Fecha a conexão com o banco de dados
$stmt->close();
$stmtNome->close();
$conn->close();
?>
