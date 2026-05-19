# ==========================================
# NETWORK POLICIES
# ==========================================

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


resource "kubernetes_network_policy" "default_deny" {
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
      match_labels = { app = "laravel" }
    }
    ingress {
      from {
        namespace_selector {
          match_labels = { purpose = "infra" }
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
      match_labels = { app = "postgres" }
    }
    ingress {
      from {
        pod_selector {
          match_labels = { app = "laravel" }
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
      match_labels = { app = "minio" }
    }
    ingress {
      from {
        pod_selector {
          match_labels = { app = "laravel" }
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
      match_labels = { "app.kubernetes.io/name" = "argocd-server" }
    }
    ingress {
      from {
        namespace_selector {
          match_labels = { purpose = "infra" }
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