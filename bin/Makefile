
SHELL = /bin/sh
.PHONY: all deb clean

define CONTROL
Package: pakiti3-client
Version: 3.0.2
Architecture: all
Maintainer: Michal Procházka <michalp@ics.muni.cz>
Depends: perl
Homepage: https://github.com/CESNET/pakiti3
Description: Patching Status Monitoring Tool - client
 Pakiti provides a monitoring and notification
 mechanism to check the patching status of multiple Linux systems.
 The project (Yumit) was originally written by Steve Traylen and then was improved
 by Romain Wartel and Michal Prochazka.
 .
 This package provide client part of Pakiti. Sends information about system and installed software to defined Pakiti server.
endef
export CONTROL

all: deb

deb:
	@mkdir -p pakiti3-client/DEBIAN
	@mkdir -p pakiti3-client/usr/bin
	@cp pakiti-client pakiti3-client/usr/bin/pakiti3-client
	@echo "$$CONTROL" > pakiti3-client/DEBIAN/control
	@dpkg-deb --build pakiti3-client
# removes no longer necessary folder
	@rm -rf pakiti3-client
	@echo "debian package was successfully built"

clean:
	@rm -f pakiti3-client.deb
	@echo "successfully cleaned"
