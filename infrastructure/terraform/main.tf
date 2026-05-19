variable "rancher_token" {
  description = "Rancher API Token"
  type        = string
  sensitive   = true
}

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
    rancher2 = {
      source = "rancher/rancher2"
    }
  }
}

provider "rancher2" {
  api_url   = "https://rancher.imagegalary.local/v3"  
  token_key = var.rancher_token 
  insecure  = true
}


provider "kubernetes" {
  config_path = "~/.kube/config"
}

provider "helm" {
  kubernetes {
    config_path = "~/.kube/config"
  }
}

resource "helm_release" "kube_prometheus_stack" {
  name             = "prometheus-stack"
  repository       = "https://prometheus-community.github.io/helm-charts"
  chart            = "kube-prometheus-stack"
  namespace        = "monitoring"
  create_namespace = true
}

resource "helm_release" "loki" {
  name             = "loki"
  repository       = "https://grafana.github.io/helm-charts"
  chart            = "loki-stack"
  namespace        = "monitoring"
  
  set {
    name  = "promtail.enabled"
    value = "true" 
  }
}

variable "minio_access_key" {
  description = "Access Key for MinIO/S3"
  type        = string
  sensitive   = true 
}

variable "minio_secret_key" {
  description = "Secret Key for MinIO/S3"
  type        = string
  sensitive   = true
}

variable "keycloak_secret_key" {
  description = "Secret Key for Keycloak"
  type        = string
  sensitive   = true
}

resource "kubernetes_namespace" "app_ns" {
  metadata { name = "imagegalary-test" }
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

resource "helm_release" "longhorn" {
  name             = "longhorn"
  repository       = "https://charts.longhorn.io"
  chart            = "longhorn"
  namespace        = "longhorn-system"
  create_namespace = true
}

resource "helm_release" "argocd" {
  name             = "argocd"
  repository       = "https://argoproj.github.io/argo-helm"
  chart            = "argo-cd"
  namespace        = "argocd"
  create_namespace = true

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

resource "helm_release" "cnpg" {
  name             = "cnpg"
  repository       = "https://cloudnative-pg.github.io/charts"
  chart            = "cloudnative-pg"
  namespace        = "cnpg-system"
  create_namespace = true
}

resource "helm_release" "keycloak" {
  name             = "keycloak"
  repository       = "https://charts.bitnami.com/bitnami"
  chart            = "keycloak"
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
    value = "true"
  }
  set {
    name  = "ingress.hostname"
    value = "keycloak.imagegalary.local"
  }
}

resource "kubernetes_cluster_role_binding" "admin_access" {
  metadata { name = "cluster-admin-group-binding" }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "ClusterRole"
    name      = "cluster-admin" 
  }
  subject {
    kind      = "Group"
    name      = "admins" 
    api_group = "rbac.authorization.k8s.io"
  }
}

resource "kubernetes_role_binding" "devs_access" {
  metadata {
    name      = "devs-zone-binding"
    namespace = kubernetes_namespace.app_ns.metadata[0].name
  }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "ClusterRole"
    name      = "edit" 
  }
  subject {
    kind      = "Group"
    name      = "developers"
    api_group = "rbac.authorization.k8s.io"
  }
}

resource "kubernetes_role_binding" "guest_access" {
  metadata {
    name      = "guest-zone-binding"
    namespace = kubernetes_namespace.app_ns.metadata[0].name
  }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "ClusterRole"
    name      = "view" 
  }
  subject {
    kind      = "Group"
    name      = "guests"
    api_group = "rbac.authorization.k8s.io"
  }
}

resource "kubernetes_role_binding" "designer_access" {
  metadata {
    name      = "designer-zone-binding"
    namespace = kubernetes_namespace.app_ns.metadata[0].name
  }
  role_ref {
    api_group = "rbac.authorization.k8s.io"
    kind      = "ClusterRole"
    name      = "view" 
  }
  subject {
    kind      = "Group"
    name      = "designers" 
    api_group = "rbac.authorization.k8s.io"
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