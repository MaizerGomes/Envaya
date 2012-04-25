CREATE TABLE `email_templates` (
    <?php require 'schema/entity_columns.php'; ?>,
    <?php require 'schema/content_columns.php'; ?>, 
  `subject` text default null,
  `from` text default null,
  `num_sent` int(11) not null default 0,
  `time_last_sent` bigint(20) unsigned null,
  `filters_json` text default null
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `sms_templates` (
   <?php require 'schema/entity_columns.php'; ?>,
   <?php require 'schema/content_columns.php'; ?>, 
  `num_sent` int(11) not null default 0,
  `time_last_sent` bigint(20) unsigned null,
  `filters_json` text default null
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
