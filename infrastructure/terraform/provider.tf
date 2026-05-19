variable "rancher_token" {
  description = "Rancher API Token"
  type        = string
  sensitive   = true
}

terraform {
  required_providers {
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