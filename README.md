# Pakiti

Pakiti provides a monitoring mechanism to check the patching status of Linux systems.

Pakiti uses the client/server model, with clients running on monitored machines and sending reports to the Pakiti server for evaluation. The report contains a list of packages installed on the client system, which is subject to analysis done by the server. The Pakiti server compares versions against other versions which are obtained from various distribution vendors. Detected vulnerabilities identified using CVE identifiers are reported as the outcome, together with affected packages that need to be updated.

Pakiti has a web based GUI which provides a list of registered systems. The collected information help system administrators maintain proper patch management and quickly identify machine vulnerable to particular vulnerabilites. The information processed is also available via programmatic interfaces.

The service is proven in production environements, monitoring thousands of machines of various configurations.

* [Server installation](docs/installation.md)
* [Server configuration](docs/configuration.md)
* [Client usage](docs/client.md)
