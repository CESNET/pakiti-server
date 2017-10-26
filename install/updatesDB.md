## 20171019 -> 20171025
```sql
alter table `Host` drop column `ownRepositoriesDef`;

create table `PkgType` (`id` integer(10) not null auto_increment, `name` varchar(10) not null, primary key (`id`), unique key (`name`))ENGINE=INNODB;

alter table `Pkg` add column `pkgTypeId` integer(10) not null;
insert ignore into `PkgType` (`name`) select distinct(`type`) from `Pkg`;
update `Pkg` inner join `PkgType` on `Pkg`.`type` = `PkgType`.`name` set `pkgTypeId` = `PkgType`.`id`;
alter table `Pkg` add foreign key (`pkgTypeId`) references PkgType(`id`) on delete cascade;
alter table `Pkg` drop column `type`;

alter table `Host` add column `pkgTypeId` integer(10) not null;
insert ignore into `PkgType` (`name`) select distinct(`type`) from `Host`;
update `Host` inner join `PkgType` on `Host`.`type` = `PkgType`.`name` set `pkgTypeId` = `PkgType`.`id`;
alter table `Host` add foreign key (`pkgTypeId`) references PkgType(`id`) on delete cascade;
alter table `Host` drop column `type`;

alter table `Pkg` add column `archId` integer(10) not null;
update `Pkg` inner join `Arch` on `Pkg`.`arch` = `Arch`.`name` set `archId` = `Arch`.`id`;
alter table `Pkg` add foreign key (`archId`) references Arch(`id`) on delete cascade;
alter table `Pkg` drop key `unique`;
alter table `Pkg` add unique key `unique` (`name`, `version`, `release`, `archId`, `pkgTypeId`);

alter table `Vulnerability` add column `archId` integer(10) not null;
update `Vulnerability` inner join `Arch` on `Vulnerability`.`arch` = `Arch`.`name` set `archId` = `Arch`.`id`;
alter table `Vulnerability` add foreign key (`archId`) references Arch(`id`) on delete cascade;
alter table `Vulnerability` drop key `unique`;
alter table `Vulnerability` add unique key `unique` (`name`, `version`, `release`, `archId`, `osGroupId`, `operator`, `cveDefId`);

/* manually check constraint on `arch` column and drop it */
show create table `Pkg`;
alter table `Pkg` drop foreign key `<your_constraint_name>`;
show create table `Vulnerability`;
alter table `Vulnerability` drop foreign key `<your_constraint_name>`;

alter table `Pkg` drop column `arch`;
alter table `Vulnerability` drop column `arch`;

update `PakitiAttributes` set `attrValue` = "20171025" where `attrName` = "dbVersion" and `attrValue` = "20171019";
```
