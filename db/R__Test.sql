DROP TABLE IF EXISTS `r__test`;
CREATE TABLE `r__test`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name`          varchar(256)     NOT NULL DEFAULT '',
    `text`         varchar(256)     NOT NULL DEFAULT '',
    `status`        tinyint(1)      NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;


INSERT INTO `r__test` (`name`, `text`, `status`)
VALUES
    ('first', 'Some Test Version 1', '1'),
    ('second', 'Some Test Version 2', '2');