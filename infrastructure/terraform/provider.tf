terraform {
  required_providers {
    rancher2 = {
      source = "rancher/rancher2"
    }
  }
}

provider "rancher2" {
  api_url   = "https://rancher.imagegalary.local/v3"  
  token_key = "token-sr554:k5mnxhl8vjl4qfwmvhf29scbvv4kb6p6hl9grsnwpbq2l4r4tsrqlr" 
  insecure  = true
}