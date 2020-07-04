CREATE TABLE `test_002`
(
    `id`            int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name`          varchar(256)     NOT NULL DEFAULT '',
    `text`         varchar(256)     NOT NULL DEFAULT '',
    `status`        tinyint(1)      NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8;


INSERT INTO `test_002` (`name`, `text`, `status`)
VALUES
    ('first', 'Some Check Version 1', '1'),
    ('second', 'Some Check Version 2', '2');