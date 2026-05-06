resource "rancher2_project" "perfection_edge_prod" {
  name       = "Perfection-Edge-Production"
  cluster_id = "local"
}

resource "rancher2_namespace" "app_ns" {
  name       = "imagegalary-test"
  project_id = rancher2_project.perfection_edge_prod.id
}