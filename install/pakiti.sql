create table `PakitiAttributes` (
	`attrName` varchar(63) not null,
	`attrValue` varchar(255) not null,
	unique key `unique` (`attrName`, `attrValue`)
)ENGINE=INNODB;

create table `Stat` (
	`name` varchar(254) not null,
	`value` integer(10) not null,
	primary key (`name`),
	unique key (`name`)
)ENGINE=INNODB;

create table `Report` (
	`id` integer(10) not null auto_increment,
	`receivedOn` datetime not null,
	`processedOn` datetime not null,
	`throughProxy` int(1) not null,
	`proxyHostname` varchar(63),
	`hostGroup` varchar(63),
	`source` varchar(63),
	`numOfInstalledPkgs` int(10) not null,
	`numOfVulnerablePkgsSec` int(10) not null,
	`numOfVulnerablePkgsNorm` int(10) not null,
	`numOfCves` int(10) not null,
	`numOfCvesWithTag` int(10) not null,
	primary key (`id`)
)ENGINE=INNODB;

create table `Os` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	primary key (`id`),
	unique key `name` (`name`)
)ENGINE=INNODB;

create table `OsGroup` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	`regex` varchar(63) not null,
	primary key (`id`),
	unique key `name` (`name`)
)ENGINE=INNODB;

insert into OsGroup (`name`, `regex`) values ('unknown', '');

create table `Arch` (
	`id` integer(10) not null auto_increment,
	`name` varchar(10) not null,
	primary key (`id`),
	unique key `name` (`name`)
)ENGINE=INNODB;

create table `Domain` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	primary key (`id`),
	unique key `name` (`name`)
)ENGINE=INNODB;

create table `HostGroup` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	`url` varchar(1023) not null,
	`contact` varchar(1023) not null,
	`note` varchar(1023) not null,
	primary key (`id`),
	unique key `name` (`name`)
)ENGINE=INNODB;

create table `Host` (
	`id` integer(10) not null auto_increment,
	`hostname` varchar(63) not null,
	`ip` varchar(40),
	`reporterHostname` varchar(63) not null,
	`reporterIp` varchar(40) not null,
	`kernel` varchar(32) not null,
	`type` varchar(10) not null,
	`ownRepositoriesDef` integer(1) default 0,
	`osId` integer(10) not null,
	`archId` integer(10) not null,
	`domainId` integer(10) not null,
	`lastReportId` integer(10),
	`lastReportHeaderHash` char(32),
	`lastReportPkgsHash` char(32),
	primary key (`id`),
	foreign key (`osId`) references Os(`id`)  on delete cascade,
	foreign key (`archId`) references Arch(`id`)  on delete cascade,
	foreign key (`domainId`) references Domain(`id`) on delete cascade,
	foreign key (`lastReportId`) references Report(`id`)
)ENGINE=INNODB;

create table `HostHostGroup` (
	`hostId` integer(10) not null,
	`hostGroupId` integer(10) not null,
	unique key `unique` (`hostId`, `hostGroupId`),
	foreign key (`hostId`) references Host(`id`) on delete cascade,
	foreign key (`hostGroupId`) references HostGroup(`id`) on delete cascade
)ENGINE=INNODB;

create table `ReportHost` (
	`hostId` integer(10) not null,
	`reportId` integer(10) not null,
	unique key `unique` (`hostId`, `reportId`),
	foreign key (`hostId`) references Host(`id`) on delete cascade,
	foreign key (`reportId`) references Report(`id`) on delete cascade
)ENGINE=INNODB;

create table `Pkg` (
	`id` integer(10) not null auto_increment,
	`name` varchar(254) not null,
	`version` varchar(63) not null,
	`release` varchar(63) not null,
	`arch` varchar(10) not null,
	`type` varchar(10) not null,
	primary key (`id`),
	foreign key (`arch`) references Arch(`name`) on delete cascade,
	unique key (`name`, `version`, `release`, `arch`)
)ENGINE=INNODB;

create table `InstalledPkg` (
	`pkgId` integer(10) not null,
	`hostId` integer(10) not null,
	primary key (`pkgId`, `hostId`),
	foreign key (`pkgId`) references Pkg(`id`) on delete cascade,
	foreign key (`hostId`) references Host(`id`) on delete cascade
)ENGINE=INNODB;

create table `VdsSource` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	`type` varchar(63) not null,
	`className` varchar(32) not null,
	primary key (`id`),
	unique key (`name`)
)ENGINE=INNODB;

create table `VdsSubSource` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	`type` varchar(63) not null,
	`vdsSourceId` integer(10) not null,
	primary key (`id`),
	unique key (`name`),
	foreign key (`vdsSourceId`) references VdsSource(`id`) on delete cascade
)ENGINE=INNODB;

create table `VdsSubSourceDef` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	`uri` varchar(255) not null,
	`enabled` integer(1) not null,
	`lastChecked` datetime not null,
	`lastSubSourceDefHash` char(32),
	`vdsSubSourceId` integer(10) not null,
	primary key (`id`),
	unique key `unique` (`name`),
	foreign key (`vdsSubSourceId`) references VdsSubSource(`id`) on delete cascade
)ENGINE=INNODB;

create table `Cve` (
	`id` integer(10) not null auto_increment,
	`name` varchar(63) not null,
	`cveDefId` integer(10) not null,
	primary key (`id`),
	unique key `unique` (`name`, `cveDefId`)
)ENGINE=INNODB;

create table `CveDef` (
	`id` integer(10) not null auto_increment,
	`definitionId` varchar(63) not null,
	`title` varchar(128) not null,
	`refUrl` varchar(255) not null,
	`vdsSubSourceDefId` integer(10) not null,
	primary key (`id`),
	unique key `unique` (`definitionId`, `title`, `refUrl`, `vdsSubSourceDefId`),
	foreign key (`vdsSubSourceDefId`) references VdsSubSourceDef(`id`) on delete cascade
)ENGINE=INNODB;

create table `PkgCveDef` (
	`pkgId` integer(10) not null,
	`cveDefId` integer(10) not null,
	`osGroupId` integer(10) not null,
	unique key `unique` (`pkgId`, `cveDefId`, `osGroupId`),
	foreign key (`pkgId`) references Pkg(`id`) on delete cascade,
	foreign key (`cveDefId`) references CveDef(`id`) on delete cascade,
	foreign key (`osGroupId`) references OsGroup(`id`) on delete cascade
)ENGINE=INNODB;

create table `CveException` (
	`id` integer(10) not null auto_increment,
	`pkgId` integer(10) not null,
	`cveName` varchar(63) not null,
	`osGroupId` integer(10) not null,
	`reason` varchar(255) not null,
	`modifier` varchar(255) not null,
	`timestamp` datetime not null,
	primary key (`id`),
	unique key `unique` (`cveName`, `pkgId`, `osGroupId`),
	foreign key (`pkgId`) references Pkg(`id`) on delete cascade,
	foreign key (`cveName`) references Cve(`name`) on delete cascade,
	foreign key (`osGroupId`) references OsGroup(`id`) on delete cascade
)ENGINE=INNODB;

create table `CveTag` (
	`id` integer(10) not null auto_increment,
	`cveName` varchar(63) not null,
	`tagName` varchar(63) not null,
	`reason` varchar(255) not null,
	`infoUrl` varchar(255) not null,
	`timestamp` timestamp default CURRENT_TIMESTAMP,
	`enabled` int(1) default 1,
	`modifier` varchar(255),
	primary key (`id`),
	unique key `unique` (`cveName`, `tagName`),
	foreign key (`cveName`) references Cve(`name`) on delete cascade
)ENGINE=INNODB;

create table `Vulnerability` (
	`id` integer(10) not null auto_increment,
	`name` varchar(254) not null,
	`version` varchar(63) not null,
	`release` varchar(63) not null,
	`arch` varchar(10) not null,
	`osGroupId` integer(10) not null,
	`operator` char(2) not null,
	`cveDefId` integer(10) not null,
	primary key (`id`),
	unique key `unique` (`name`, `version`, `release`, `arch`, `osGroupId`, `operator`, `cveDefId`),
	foreign key (`arch`) references Arch(`name`) on delete cascade,
	foreign key (`cveDefId`) references CveDef(`id`) on delete cascade,
	foreign key (`osGroupId`) references OsGroup(`id`) on delete cascade
)ENGINE=INNODB;

create table `User` (
	`id` integer(10) not null auto_increment, 
	`uid` VARCHAR(255) not null,
	`name` VARCHAR(255) not null,
	`email` VARCHAR(255) not null,
	`admin` integer(1) not null,
	`createdAt` DATETIME not null,
	primary key (`id`),
	unique key (`uid`)
)ENGINE=INNODB;

create table `UserHostGroup` (
	`userId` integer(10) not null,
	`hostGroupId` integer(10) not null,
	unique key `unique` (`userId`, `hostGroupId`),
	foreign key (`userId`) references User(`id`) on delete cascade,
	foreign key (`hostGroupId`) references HostGroup(`id`) on delete cascade
)ENGINE=INNODB;

create table `UserHost` (
	`userId` integer(10) not null,
	`hostId` integer(10) not null,
	unique key `unique` (`userId`, `hostId`),
	foreign key (`userId`) references User(`id`) on delete cascade,
	foreign key (`hostId`) references Host(`id`) on delete cascade
)ENGINE=INNODB;
