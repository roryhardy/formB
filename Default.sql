CREATE TABLE IF NOT EXISTS `userActivity` (
  `date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `page` varchar(255) NOT NULL default '',
  `remote_addr` int(10) unsigned NOT NULL default '0',
  `attempts` tinyint(1) unsigned NOT NULL default '1',
  UNIQUE KEY `uid` (`page`,`remote_addr`),
  KEY `uid_date` (`page`,`remote_addr`,`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;