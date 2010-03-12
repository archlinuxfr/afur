set storage_engine=InnoDB;
drop database afur;
create database afur;
use afur;

create table users 
(
	id integer unsigned not null primary key auto_increment,
	nick varchar(32) unique not null,
	passwd varchar(32) not null,
	mail varchar(64) unique not null,
	name varchar(64) not null default '',
	admin boolean not null default false,
	announce boolean not null default false,
	date_reg date not null 
);

insert into users (nick,passwd,mail,admin,announce, date_reg) values('admin', md5('admin'), 'admin@localhost', true, true, now());
insert into users (nick,passwd,mail,admin,announce, date_reg) values('test', md5('test'), 'test@localhost', false, false, now());

create table packages
(
	id integer unsigned not null primary key auto_increment,
	filename varchar(255) not null,
	user_id integer unsigned null,
	name varchar(64) not null,
	description varchar(255) not null,
	version varchar(32) not null,
	arch char(10) not null,
	url varchar(255) not null,
	license varchar(32) not null,
	first_sub datetime not null,
	last_sub datetime not null,
	modified datetime not null,
	outofdate boolean not null default false,
	del boolean not null default false,
	unique(name,arch),
	index (user_id),
	foreign key (user_id) references users (id) on delete set null
);

create table pkg_link
(
	pkg_id integer unsigned not null,
	link_id integer unsigned,
	name varchar(64) not null,
	cond varchar(64) not null default '',
	reason varchar(255) not null default '',
	opt boolean not null default false,
	primary key (pkg_id, name, cond),
	index (pkg_id),
	index (link_id),
	foreign key (pkg_id) references packages (id) on delete cascade,
	foreign key (link_id) references packages (id) on delete set null
);


create table pkg_sub
(
	pkg_id integer unsigned not null,
	user_id integer unsigned not null,
	primary key (pkg_id, user_id),
	index (pkg_id),
	index (user_id),
	foreign key (pkg_id) references packages (id) on delete cascade,
	foreign key (user_id) references users (id) on delete cascade
);
  
create table options
(
	id integer unsigned not null primary key auto_increment,
	name char(32) not null unique,
	value text
);


