# Configure Provider Instance

A Provider Instance is typically the first step when establishing a connection to a third-party (such as Google or GitHub, etc).  
The process is quite simple and straight forward.  Here are some guidelines:

![Provider Instance](../../.vuepress/public/provider-instance.png "Provider Instance")

### `Client Id`
A public identifier provided to you from the third party service.

### `Client Secret`
A private string that is generated and intended only for your eyes.  Do not share this or place it in any configuration files.

### `Settings (optional)`
A provider may have additional setting.

### `Environments`
The environment(s) the provider instance is configured for.