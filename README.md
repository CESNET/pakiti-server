# Pakiti3

* [Installation](docs/installation.md)
* [Configuration](docs/configuration.md)

Pakiti provides a monitoring and notification mechanism to check the patching status of systems.

Once installed on a client host, Pakiti will send every night the list of installed packages to the relevant Pakiti Server(s). After the client sends the list of installed packages, Pakiti server compares the versions against versions which Pakiti server obtains from OVAL definitions from MITRE. Optionally client reports back the packages which has marked CVE by tag.

Pakiti has a web based GUI which provides a list of the registered systems. This helps the system admins keep multiple machines up-to-date and prevent unpatched machines to be kept silently on the network.
