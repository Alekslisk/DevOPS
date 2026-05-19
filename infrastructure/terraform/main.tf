terraform {
  required_providers {
    kubernetes = {
      source  = "hashicorp/kubernetes"
      version = "~> 2.0"
    }
    helm = {
      source  = "hashicorp/helm"
      version = "~> 2.0"
    }
  }
  # Стейт храним в Git или S3/Minio
  backend "local" {
    path = "terraform.tfstate"
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

# Переменные (будут браться из TF_VAR_*)
variable "minio_access_key" {
  type      = string
  sensitive = true
}

variable "minio_secret_key" {
  type      = string
  sensitive = true
}

variable "keycloak_secret_key" {
  type      = string
  sensitive = true
}

# ==========================================
# ТОЛЬКО ТО, ЧТО ЕЩЁ НЕ СОЗДАНО
# ==========================================

# Неймспейсы (с проверкой, что их нет)
data "kubernetes_namespace" "monitoring" {
  metadata {
    name = "monitoring"
    labels = {
      purpose = "monitoring"
    }
  }
}

# Loki (был удалён)
resource "helm_release" "loki" {
  name             = "loki"
  repository       = "https://grafana.github.io/helm-charts"
  chart            = "loki-stack"
  namespace        = kubernetes_namespace.monitoring.metadata[0].name

  set {
    name  = "promtail.enabled"
    value = "true"
  }
}

# CNPG (был удалён, CRD остались)
resource "helm_release" "cnpg" {
  name             = "cnpg"
  repository       = "https://cloudnative-pg.github.io/charts"
  chart            = "cloudnative-pg"
  namespace        = "cnpg-system"
  create_namespace = true
}

# Keycloak (был удалён)
resource "helm_release" "keycloak" {
  name             = "keycloak"
  repository       = "https://charts.bitnami.com/bitnami"
  chart            = "keycloak"
  version          = "24.4.5" 
  namespace        = "keycloak-system"
  create_namespace = true

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
  timeout = 600
}

# ==========================================
# NETWORK POLICIES
# ==========================================

# Используем data-ресурсы для существующих неймспейсов
data "kubernetes_namespace" "app_ns" {
  metadata {
    name = "imagegalary-test"
  }
}

data "kubernetes_namespace" "argocd_ns" {
  metadata {
    name = "argocd"
  }
}

# Default Deny для приложения
resource "kubernetes_network_policy" "default_deny_apps" {
  metadata {
    name      = "default-deny-ingress"
    namespace = data.kubernetes_namespace.app_ns.metadata[0].name
  }
  spec {
    pod_selector {}
    policy_types = ["Ingress"]
  }
}

# Ingress → Laravel
resource "kubernetes_network_policy" "ingress_to_laravel" {
  metadata {
    name      = "allow-ingress-to-laravel"
    namespace = data.kubernetes_namespace.app_ns.metadata[0].name
  }
  spec {
    pod_selector {
      match_labels = {
        app = "laravel"
      }
    }
    ingress {
      from {
        namespace_selector {
          match_labels = {
            purpose = "infra"  # kube-system с Ingress Controller
          }
        }
      }
      ports {
        port     = "80"
        protocol = "TCP"
      }
    }
    policy_types = ["Ingress"]
  }
}

# Laravel → Postgres
resource "kubernetes_network_policy" "laravel_to_postgres" {
  metadata {
    name      = "allow-laravel-to-postgres"
    namespace = data.kubernetes_namespace.app_ns.metadata[0].name
  }
  spec {
    pod_selector {
      match_labels = {
        app = "postgres"
      }
    }
    ingress {
      from {
        pod_selector {
          match_labels = {
            app = "laravel"
          }
        }
      }
      ports {
        port     = "5432"
        protocol = "TCP"
      }
    }
    policy_types = ["Ingress"]
  }
}

# Laravel → Minio
resource "kubernetes_network_policy" "laravel_to_minio" {
  metadata {
    name      = "allow-laravel-to-minio"
    namespace = data.kubernetes_namespace.app_ns.metadata[0].name
  }
  spec {
    pod_selector {
      match_labels = {
        app = "minio"
      }
    }
    ingress {
      from {
        pod_selector {
          match_labels = {
            app = "laravel"
          }
        }
      }
      ports {
        port     = "9000"
        protocol = "TCP"
      }
    }
    policy_types = ["Ingress"]
  }
}

# Ingress → ArgoCD
resource "kubernetes_network_policy" "ingress_to_argocd" {
  metadata {
    name      = "allow-ingress-to-argocd"
    namespace = data.kubernetes_namespace.argocd_ns.metadata[0].name
  }
  spec {
    pod_selector {
      match_labels = {
        "app.kubernetes.io/name" = "argocd-server"
      }
    }
    ingress {
      from {
        namespace_selector {
          match_labels = {
            purpose = "infra"
          }
        }
      }
      ports {
        port     = "8080"
        protocol = "TCP"
      }
    }
    policy_types = ["Ingress"]
  }
}