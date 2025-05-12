<?php
// Iniciar a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['crm_cpf'])) {
    header("Location: login.html");
    exit();
}

// Obtém o CRM do médico logado
$medicoCrm = $_SESSION['crm_cpf'];

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

// Busca o nome do médico logado
$sqlNome = "SELECT med_nome as nome FROM medico WHERE med_crm = ?";
$stmtNome = $conn->prepare($sqlNome);
$stmtNome->bind_param("s", $medicoCrm);
$stmtNome->execute();
$resultNome = $stmtNome->get_result();
$nomeMedico = ($resultNome->num_rows > 0) ? $resultNome->fetch_assoc()['nome'] : "Médico";

// Consulta para obter os prontuários apenas do médico logado
$sql = "SELECT e.ex_id, date_format(e.ex_data,'%d/%m/%Y') as data_formatada, e.ex_descricao, p.pac_nome, 
        INSERT(INSERT(INSERT(e.ex_pac_cpf, 10, 0, '-' ), 7, 0, '.'), 4, 0, '.') as cpf_formatado,
        TIMESTAMPDIFF(YEAR, p.pac_dtnasc, CURDATE()) as idade,
        e.ex_sistema, e.ex_area, e.ex_obs, e.ex_arquivo
        FROM exames AS e
        INNER JOIN paciente AS p ON e.ex_pac_cpf = p.pac_cpf
        WHERE e.ex_med_crm = ? ORDER BY e.ex_data DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $medicoCrm);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSPP - Sistema de Saúde Pública e Particular</title>
    <link rel="stylesheet" href="menu_medico2.css">
    <link rel="icon" type="image/x-icon" href="img/icon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Cabeçalho -->
    <header class="header">
        <h1><strong>SSPP - </strong>SISTEMA DE SAÚDE PÚBLICA E PARTICULAR</h1>
    </header>

    <!-- Mensagem de boas-vindas com o nome do médico -->
    <section class="welcome">
        <h2>Olá, <?php echo htmlspecialchars($nomeMedico); ?>!</h2>
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
                            <th>Paciente</th>
                            <th>CPF</th>
                            <th>Idade</th>
                            <th>Sistema</th>
                            <th>Área</th>
                            <th>Observações</th>
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
                                echo "<td>" . $row["data_formatada"] . "</td>";
                                echo "<td>" . $row["ex_descricao"] . "</td>";
                                echo "<td>" . $row["pac_nome"] . "</td>";
                                echo "<td>" . $row["cpf_formatado"] . "</td>";
                                echo "<td>" . $row["idade"] . "</td>";
                                echo "<td>" . $row["ex_sistema"] . "</td>";
                                echo "<td>" . $row["ex_area"] . "</td>";
                                echo "<td>" . $row["ex_obs"] . "</td>";
                                echo "<td><a href='download.php?id=" . $row["ex_id"] . "' class='btn-download'>Download</a></td>";
                                echo "<td><a href='view.php?id=" . $row["ex_id"] . "' class='btn-visualizar' target='_blank'>Visualizar</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10'>Nenhum prontuário encontrado!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Botões de navegação -->
        <a href="menu_medico.php" class="btn btn-sair">Voltar</a>
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
