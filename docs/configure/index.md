# Configure - General Settings

![General Settings](../.vuepress/public/general-settings.png "General Settings")

---

### Encrypt Data
Protecting sensitive data is important.  When enabled, the [Provider's Client Secret] value will be encrypted prior to storage in the database.
*Note: encryption uses Craft's security key; changing it will result in data corruption.*

[Provider's Client Secret]: provider/instance.md#client-secret

### Auto populate Token environment(s)
If enabled, when a token is created it can be auto-assign to the current environment.  *You can also choose to mirror the provider
environments using the setting below.*

### Auto populate Token environment(s) from Provider
If enabled, all of the provider environments will be assigned to the token; not the current environment.

### Environments
Patron is intended to work across multiple environments; providing the ability to associate OAuth2 Providers and Tokens on a per-environment basis.  Define all of the 
environments for your application.

### Callback Url Path
Customize the OAuth2 callback path.  