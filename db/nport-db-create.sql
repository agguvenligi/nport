CREATE DATABASE `nport`;

CREATE  TABLE `nport`.`ips` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `ip` VARCHAR(45) NOT NULL ,
  `creationdate` DATETIME NOT NULL ,
  `excluded` INT NOT NULL DEFAULT 0,
  `rawip` BIGINT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `rawip` (`rawip` ASC),
  UNIQUE (ip) 
);

CREATE  TABLE `nport`.`ports` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `port` INT NOT NULL ,
  `ipid` INT NOT NULL ,
  `status` INT NOT NULL ,
  `update` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `port` (`port` ASC),
  FOREIGN KEY (ipid) REFERENCES `nport`.`ips`(id)
);

CREATE  TABLE `nport`.`exclusions` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `rawipstart` BIGINT NOT NULL,
  `rawipend` BIGINT NOT NULL,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `rawipstart` (`rawipstart` ASC),
  INDEX `rawipend` (`rawipend` ASC)
);

CREATE  TABLE `nport`.`openporthistory` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `update` DATETIME NOT NULL ,
  `value` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `currdate` (`update` ASC) 
);

CREATE  TABLE `nport`.`hydrascans` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `startdate` DATETIME NOT NULL ,
  `pid` INT NOT NULL ,
  `portid` INT NOT NULL ,
  `description` TEXT NULL ,
  `outputfile` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  FOREIGN KEY (portid) REFERENCES `nport`.`ports`(id)
);
