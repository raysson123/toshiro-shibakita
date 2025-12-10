<?php
// ===============================================
//  LÓGICA DO BACKEND — NÃO REMOVER OU MOVER
// ===============================================

// Ativa erros na tela (opcional em produção)
ini_set("display_errors", 1);

// Header deve estar no INÍCIO do arquivo
header("Content-Type: text/html; charset=utf-8");

// Configurações do banco
$servername = "db";
$username   = "root";
$password   = "Senha123";
$database   = "meubanco";

// Conexão
$link = new mysqli($servername, $username, $password, $database);

// Status inicial
$status_conexao = "Conectado";
$alerta_tipo    = "success";
$mensagem_erro  = "";

// Verifica erros de conexão
if ($link->connect_errno) {
    $status_conexao = "Falha";
    $alerta_tipo    = "danger";
    $mensagem_erro  = $link->connect_error;
}

// Dados randômicos
$valor_rand1 = rand(1, 999);
$valor_rand2 = strtoupper(substr(bin2hex(random_bytes(4)), 1));
$host_name   = gethostname();

// Query INSERT
$query = "INSERT INTO dados 
          (AlunoID, Nome, Sobrenome, Endereco, Cidade, Host) 
          VALUES 
          ('$valor_rand1', '$valor_rand2', '$valor_rand2', '$valor_rand2', '$valor_rand2', '$host_name')";

$resultado_insert = false;

if ($status_conexao == "Conectado") {
    if ($link->query($query) === TRUE) {
        $resultado_insert = true;
    } else {
        $mensagem_erro = $link->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shibakita - Microsserviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body { background-color: #f8f9fa; }
        .host-badge { font-size: 1.2rem; }
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); border: none; }
        .header-bg { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card overflow-hidden">
                <div class="card-header header-bg p-4 text-center">
                    <h2 class="mb-0"><i class="bi bi-cloud-check-fill me-2"></i>Sistema Toshiro Shibakita</h2>
                    <p class="mb-0 opacity-75">Arquitetura de Microsserviços com Docker Swarm</p>
                </div>

                <div class="card-body p-4">

                    <div class="alert alert-info text-center border-0 shadow-sm" role="alert">
                        <h5 class="alert-heading mb-1">Requisição processada por:</h5>
                        <span class="badge bg-dark host-badge p-2 mt-2">
                            <i class="bi bi-hdd-rack-fill me-2"></i><?php echo $host_name; ?>
                        </span>
                        <p class="small mt-2 mb-0">Atualize (F5) para ver o balanceamento entre nós do cluster.</p>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-3">Log de Operações:</h5>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-filetype-php text-primary me-2"></i>Versão do PHP:</span>
                        <span class="fw-bold"><?php echo phpversion(); ?></span>
                    </div>

                    <?php if ($resultado_insert): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill flex-shrink-0 me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>Sucesso!</strong> Novo registro inserido no banco de dados.<br>
                                <small class="text-muted">Dados: <?php echo "$valor_rand1 - $valor_rand2"; ?></small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>Erro!</strong> Não foi possível gravar o registro.<br>
                                <small><?php echo $mensagem_erro; ?></small>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="card-footer text-muted text-center py-3">
                    <small>Desenvolvido para o Desafio DIO &copy; <?php echo date("Y"); ?></small>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
