resource "rancher2_project" "perfection_edge_prod" {
  name       = "Perfection-Edge-Production"
  cluster_id = "local"
}

resource "rancher2_namespace" "app_ns" {
  name       = "imagegalary-test"
  project_id = rancher2_project.perfection_edge_prod.id
}

resource "kubernetes_secret" "app_secrets" {
  metadata {
    name      = "app-secrets"
    namespace = "imagegalary-test" 
  }

  data = {
    AWS_ACCESS_KEY_ID     = "actual_id"
    AWS_SECRET_ACCESS_KEY = "actual_key"
    POSTGRES_USER         = "user"
    POSTGRES_PASSWORD     = "password"
  }

  type = "Opaque"
}

resource "kubernetes_persistent_volume" "minio_pv" {
  metadata {
    name = "minio-pv-local"
  }
  spec {
    capacity = {
      storage = "10Gi"
    }
    access_modes = ["ReadWriteOnce"]
    persistent_volume_source {
      host_path {
        path = "/mnt/data/minio"
      }
    }
    storage_class_name = "manual" 
  }
}

resource "helm_release" "longhorn" {
  name       = "longhorn"
  repository = "https://charts.longhorn.io"
  chart      = "longhorn"
  namespace  = "longhorn-system"
  create_namespace = true
}