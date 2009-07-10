-- Optional: Only needed if you intend to use rating functionality 

DROP TABLE IF EXISTS `ezcontentobject_yuirating`;
CREATE TABLE `ezcontentobject_yuirating` (
  `contentobject_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `session_key` varchar(32) NOT NULL default '',
  `rating` float NOT NULL default '0',
  `time` int(11) NOT NULL default '0',
  PRIMARY KEY  ( `contentobject_id`,`user_id`, `session_key` )
);
