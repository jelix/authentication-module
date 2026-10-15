alter table accounts CHANGE column firstname firstname varchar(100) default null;
alter table accounts CHANGE column lastname lastname varchar(100) default null;
alter table accounts add column displayname varchar(255) default null;
