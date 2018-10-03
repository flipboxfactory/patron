# Configure Provider

Configuring a provider is typically the first step when establishing a connection to a third-party (such as Google or GitHub, etc).  
The process is quite simple and straight forward.  Here are some guidelines:

[[toc]]

### `Handle`
A unique identifier for the provider.  This handle is used to reference the provider via the API.  For     

### `Client Id`
A public identifier provided to you from the third party service.

### `Client Secret`
A private string that is generated and intended only for your eyes.  Do not share this or place it in any configuration files.

### `Provider`
The provider that a connection is intended to be established.

_Note: a provider may have additional setting which will appear once selected._

### `Enabled`
Identifies whether the provider is considered 'available for use' by default.

### `Environments`
The environments the provider is configured for.