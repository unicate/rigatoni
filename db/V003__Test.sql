use `nofw`;
DROP TABLE IF EXISTS `nofw_V003__Test`;
CREATE TABLE `nofw_V003__Test`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name`          varchar(256)     NOT NULL DEFAULT '',
    `text`         varchar(256)     NOT NULL DEFAULT '',
    `status`        tinyint(1)      NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;


INSERT INTO `nofw_V003__Test` (`name`, `text`, `status`)
VALUES
    ('first', 'Some Test Version 1', '1'),
    ('second', 'Some Test Version 2', '2');