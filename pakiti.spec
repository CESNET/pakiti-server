Summary:	Patching status monitoring tool
Name:		pakiti
Version:	3.0.0
Release:	1%{?dist}
URL:		https://github.com/CESNET/pakiti3
License:	ASL 2.0
Group:		Utilities/System
Source0:	pakiti-%{version}.tgz
BuildRoot:	%{_tmppath}/%{name}-%{version}-%{release}-root
BuildArch:	noarch
BuildRequires:	perl

%description
Runs rpm -qa or dpkg -l on the hosts and sends results to a central server.

Central server then process the results and checks whether the packages are
installed in the recent version. Central server also provides web GUI where
all results can be seen.

%package client
Summary:	Client for the Pakiti tool
Group:		Utilities/System

%description client
Runs rpm -qa or dpkg -l, depends on the linux distro. Results are sent to the
central Pakiti server using openssl s_client or curl.

%prep
%setup

%build
mkdir man
pod2man --section=1 bin/pakiti-client > man/pakiti-client.1

%install
rm -rf %{buildroot}
install -D -m755 bin/pakiti-client   %{buildroot}%{_bindir}/pakiti-client
install -D -m644 man/pakiti-client.1 %{buildroot}%{_mandir}/man1/pakiti-client.1

%clean
rm -rf %{buildroot}

%files client
%defattr(-,root,root,-)
%{_bindir}/*
%{_mandir}/man?/*
