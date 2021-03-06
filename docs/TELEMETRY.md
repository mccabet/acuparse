# Acuparse Telemetry Collection Guide

When checking for updates, Acuparse will send a data packet containing some basic details about your system and install.
This data is necessary for planning future releases and understanding the overall install base.

Acuparse generates a unique client ID during install which is sent along with this data.
Client ID and Access/Hub MAC address used for data validation.

To view the data stored for your install, visit the admin settings page, then click on the Telemetry Data button (under Update Checking).

The current query used when checking for updates is:

- `client=<install_id>&version=<version>&mac=<device_mac>&chassis=<chassis>&virt=<virtualization>&kernel=<kernel>&os=<operating_system>&arch=<architecture>`

On most installs, data is generated by executing:

```bash
hostnamectl
```
