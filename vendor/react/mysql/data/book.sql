--
-- Database schema for test use
--

USE `test`;

CREATE TABLE IF NOT EXISTS `book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `ISBN` char(20) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `author` char(10) CHARACTER SET utf8 NOT NULL,
  `created` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ISBN` (`ISBN`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;
