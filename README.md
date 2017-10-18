# Pakiti

Pakiti provides a monitoring mechanism to check the patching status of Linux systems.

Pakiti is using the client/server model, with clients running on monitored machines and sending reports to the Pakiti server for evaluation. The report contains a list of packages installed on the client system, which is subject to analysis done by the server. The Pakiti server compares the versions against versions which are obtained obtains from various distribution vendors. Detected vulnerabilities identified using CVE identifiers are reported as the outcome, together with affected packages that need to be installed.

Pakiti has a web based GUI which provides a list of the registered systems. This helps the system admins keep multiple machines up-to-date and prevent unpatched machines to be kept silently on the network. The information processed is also available via programmatic interfaces.

* [Server installation](docs/installation.md)
* [Server configuration](docs/configuration.md)
* [Client usage](docs/client.md)
