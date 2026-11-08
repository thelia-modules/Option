# Changelog

## 3.0.3

### Fixed

- The admin permission of the option resource is added to `thelia.api.admin_resources` by a compiler pass instead of replacing the parameter, so it no longer erases the resources another module declared.
