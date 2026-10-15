alter table accounts alter column firstname drop not null;
alter table accounts alter column lastname drop not null;
alter table accounts add column displayname varchar(255) default null;
