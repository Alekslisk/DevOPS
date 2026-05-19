terraform {
  backend "kubernetes" {
    secret_suffix = "infra-state"
    config_path   = "~/.kube/config"
  }

  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.24.0"
    }
    helm = {
      source  = "hashicorp/helm"
      version = "~> 2.12.0"
    }
    null = {
      source  = "hashicorp/null"
      version = "~> 3.2.0"
    }
  }
}

provider "kubernetes" {
  config_path = "~/.kube/config"
}

provider "helm" {
  kubernetes {
    config_path = "~/.kube/config"
  }
}


# ==========================================
# ВХОДНЫЕ ПЕРЕМЕННЫЕ
# ==========================================

variable "ssh_private_key" {
  type      = string
  sensitive = true
}

variable "keycloak_secret_key" {
  type      = string
  sensitive = true
}

variable "minio_access_key" {
  type      = string
  sensitive = true
}

variable "minio_secret_key" {
  type      = string
  sensitive = true
}

# ==========================================
# НЕЙМСЕЙСЫ, СЕКРЕТЫ И МЕТКИ
# ==========================================

resource "kubernetes_labels" "kube_system_infra_label" {
  api_version = "v1"
  kind        = "Namespace"
  
  metadata {
    name = "kube-system"
  }
  
  labels = {
    purpose = "infra"
  }
}

resource "kubernetes_namespace" "app_ns" {
  metadata {
    name = "imagegalary-test"
    labels = {
      purpose = "apps"
    }
  }
}

resource "kubernetes_secret" "app_aws_secrets" {
  metadata {
    name      = "app-secrets"
    namespace = kubernetes_namespace.app_ns.metadata[0].name
  }
  data = {
    AWS_ACCESS_KEY_ID     = var.minio_access_key
    AWS_SECRET_ACCESS_KEY = var.minio_secret_key
  }
  type = "Opaque"
}


resource "null_resource" "prepare_longhorn_nodes" {
  for_each = toset([
    "10.188.157.79",  # master
    "10.188.157.193", # master-2
    "10.188.157.222", # master-3
    "10.188.157.32",  # worker1
    "10.188.157.244"  # worker2
  ])

  connection {
    type        = "ssh"
    user        = "ubuntu"
    private_key = var.ssh_private_key
    host        = each.key
    timeout     = "2m"
  }

  provisioner "remote-exec" {
    inline = [
      "echo '==> [Terraform] Начало подготовки ноды ${each.key}...'",
      "sudo apt-get update -y",
      "sudo apt-get install -y nfs-common open-iscsi",
      "sudo rm -rf /var/lib/longhorn/*",
      "echo '==> [Terraform] Нода ${each.key} успешно настроена!'"
    ]
  }

  triggers = {
    nodes_bundle = join(",", [
      "10.188.157.79", "10.188.157.193", "10.188.157.222", "10.188.157.32", "10.188.157.244"
    ])
  }
}


# ==========================================
# ПОСЛЕДОВАТЕЛЬНАЯ УСТАНОВКА HELM-ЧАРТОВ
# ==========================================

# Шаг 1: Разворачиваем Longhorn (Распределенное хранилище)
resource "helm_release" "longhorn" {
  name             = "longhorn"
  repository       = "https://charts.longhorn.io"
  chart            = "longhorn"
  namespace        = "longhorn-system"
  create_namespace = true
  wait             = true
  timeout          = 450 # Даем 7.5 минут на выкачивание всех образов

  depends_on = [
    null_resource.prepare_longhorn_nodes
  ]
}

# Шаг 2: Разворачиваем CloudNative-PG (Оператор Postgres)
resource "helm_release" "cnpg" {
  name             = "cnpg"
  repository       = "https://cloudnative-pg.github.io/charts"
  chart            = "cloudnative-pg"
  namespace        = "cnpg-system"
  create_namespace = true

  depends_on = [helm_release.longhorn]
}

# Шаг 3: Разворачиваем ArgoCD (Инструмент GitOps деплоя)
resource "helm_release" "argocd" {
  name             = "argocd"
  repository       = "https://argoproj.github.io/argo-helm"
  chart            = "argo-cd"
  namespace        = "argocd"
  create_namespace = true

  depends_on = [helm_release.cnpg]

  set {
    name  = "server.ingress.enabled"
    value = "true"
  }
  set {
    name  = "server.ingress.hosts[0]"
    value = "argo.imagegalary.local"
  }
  set {
    name  = "server.extraArgs[0]"
    value = "--insecure"
  }
}

# Шаг 4: Разворачиваем Loki-Stack (Сбор логов)
resource "helm_release" "loki" {
  name             = "loki"
  repository       = "https://grafana.github.io/helm-charts"
  chart            = "loki-stack"
  namespace        = "monitoring"
  create_namespace = true 

  depends_on = [helm_release.argocd]

  set {
    name  = "promtail.enabled"
    value = "true"
  }
}

# Шаг 5: Разворачиваем Keycloak (Аутентификация) через OCI
resource "helm_release" "keycloak" {
  name             = "keycloak"
  repository       = "oci://registry-1.docker.io/bitnamicharts" 
  chart            = "keycloak"
  version          = "24.4.4"  
  namespace        = "keycloak-system"
  create_namespace = true
  replace          = true       
  
  depends_on = [helm_release.loki] 

  set {
    name  = "auth.adminUser"
    value = "admin"
  }
  set {
    name  = "auth.adminPassword"
    value = var.keycloak_secret_key
  }
  set {
    name  = "ingress.enabled"
    value = "false"
  }
}