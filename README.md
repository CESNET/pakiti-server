# Pakiti

Pakiti provides a monitoring mechanism to check the patching status of Linux systems.

Pakiti uses the client/server model, with clients running on monitored machines and sending reports to the Pakiti server for evaluation. The report contains a list of packages installed on the client system, which is subject to analysis done by the server. The Pakiti server compares versions against other versions which are obtained from various distribution vendors. Detected vulnerabilities identified using CVE identifiers are reported as the outcome, together with affected packages that need to be updated.

Pakiti has a web based GUI which provides a list of registered systems. This helps the system admins keep multiple machines up-to-date, and prevent unpatched machines by keeping them silently on the network. The information processed is also available via programmatic interfaces.

* [Server installation](docs/installation.md)
* [Server configuration](docs/configuration.md)
* [Client usage](docs/client.md)
