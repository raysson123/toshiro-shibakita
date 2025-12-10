# 🛒 Projeto Toshiro Shibakita: Infraestrutura de Alta Disponibilidade

> **Status:** ✅ Operacional | **Ambiente:** Proxmox VE + Docker Swarm

Este projeto documenta a implementação de uma arquitetura de **microsserviços** escalável, migrando um sistema legado para um **Cluster Docker Swarm** simulado em uma nuvem privada (On-Premise) com Proxmox.

O objetivo é demonstrar conceitos de **Alta Disponibilidade (HA)**, **Balanceamento de Carga**, **Persistência de Dados** e **Orquestração de Containers**.

---

## 🏗️ 1. Infraestrutura (Camada de Virtualização)

O “chão de fábrica” deste projeto roda sobre o hypervisor **Proxmox VE**. Foram provisionadas três máquinas virtuais (VMs) idênticas para compor o cluster.

### Especificações das VMs (Nós do Cluster)

| ID  | Hostname              | Função (Role)          | S.O.      | vCPU | RAM | IP       |
|:---:|-----------------------|------------------------|-----------|:----:|:---:|----------|
| **104** | `shibakita-manager-1` | **Leader / Manager** | Debian 13 | 2    | 2GB | `...104` |
| **105** | `shibakita-manager-2` | **Manager (Reach)**  | Debian 13 | 2    | 2GB | `...105` |
| **106** | `shibakita-manager-3` | **Manager (Reach)**  | Debian 13 | 2    | 2GB | `...106` |

### 🔧 Como as VMs foram configuradas

1. **Template Base:** Criada uma VM com Debian 13 (Trixie) limpo + Docker Engine + agente QEMU.  
2. **Clonagem:** Utilizado o recurso de *Linked Clone* do Proxmox para economizar espaço e agilizar o provisionamento.  
3. **Cluster Swarm:**
   * A VM **104** iniciou o cluster (`docker swarm init`).
   * As VMs **105** e **106** ingressaram como gerentes (`docker swarm join --token ...`).
   * **Resultado:** Alta disponibilidade — qualquer gerente pode cair sem derrubar o cluster.

---

## 🧩 2. Arquitetura de Software (Microsserviços)

O sistema foi dividido em containers independentes que rodam sobre uma rede virtual protegida (`overlay`), isolada da rede física.

### Diagrama de Funcionamento

    graph TD
        User((Usuario)) -->|HTTP:80| Proxy[Nginx Proxy]
        
        subgraph "Docker Swarm Cluster"
            Proxy -->|Round Robin| App1[Backend PHP - Replica 1]
            Proxy -->|Round Robin| App2[Backend PHP - Replica 2]
            Proxy -->|Round Robin| App3[Backend PHP - Replica 3]
            
            App1 -->|Rede Interna| DB[(MySQL Database)]
            App2 -->|Rede Interna| DB
            App3 -->|Rede Interna| DB
        end


---

## 🧱 Explicação dos Componentes

### **🚪 1. Proxy Reverso (Nginx)**  
* Função: Atua como porteiro, recebendo todas as requisições externas.  
* Destaque: Não expõe IPs internos; utiliza o DNS interno do Docker para localizar o serviço backend.

---

### **🧠 2. Backend (PHP 7.4 + Apache)**  
* Função: Processa a lógica e renderiza o HTML para o usuário.  
* Escalabilidade: Definido com **3 réplicas**; cada VM executa uma cópia do backend.  
* Hot-Reload: A pasta `./php` usa *bind mount* → qualquer alteração no código reflete automaticamente nos containers.

---

### **💾 3. Banco de Dados (MySQL 5.7)**  
* Função: Armazenamento de dados.  
* Segurança: Acessível apenas pela rede privada do Swarm.  
* Persistência: Utiliza `docker volume` (`db_data`), garantindo que os dados sobrevivam a reinicializações.

---

## 📂 3. Estrutura do Projeto

```
/toshiro-shibakita
│
├── docker-compose.yml     # 📜 Orquestra todos os serviços
│
├── php/                   # 📁 Backend PHP
│   ├── Dockerfile         # Imagem personalizada PHP + extensões MySQL
│   ├── index.php          # Código principal (frontend + backend)
│   └── banco.sql          # Script para criação da tabela
│
└── proxy/                 # 📁 Proxy reverso
    ├── Dockerfile         # Imagem base do Nginx
    └── nginx.conf         # Regras de proxy / balanceamento
```

---

## 🚀 4. Como Executar (Deploy)

### **Pré-requisitos**
* Acesso ao nó `shibakita-manager-1`.  
* Git e Docker instalados.

### 1️⃣ Baixar o projeto

```
git clone https://github.com/SEU_USUARIO/toshiro-shibakita.git
cd toshiro-shibakita
```

### 2️⃣ Subir a Stack

```
docker stack deploy -c docker-compose.yml toshiro
```

### 3️⃣ Validar

```
docker service ls
```

O serviço **toshiro_backend** deve aparecer com **3/3 réplicas** ativas.

### 4️⃣ Acessar

Abra no navegador:

```
http://IP-DE-QUALQUER-NÓ
```

---

## 🧪 5. Testes de Validação

### 🔁 Teste de Balanceamento

Ao apertar **F5** repetidamente no navegador, o campo *“Requisição processada por:”* deve alternar entre os diferentes hosts, indicando que:

* O proxy está distribuindo o tráfego.  
* Os backends estão operando em cluster.  
* O Swarm está saudável.  
