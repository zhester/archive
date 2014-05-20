-- Poll System Database Schema

-- The polls.
create table zz_pl_polls (
	id int not null auto_increment,
	suspended int(1) not null default 0,
	posted int(12) not null,
	starts int(12) not null,
	expires int(12) not null,
	title varchar(63) not null,	
	primary key(id)) type=MyISAM;

-- The options.
create table zz_pl_options (
	id int not null auto_increment,
	poll_id int not null,
	title varchar(63),
	rank int not null default 0,
	votes not null default 0,
	primary key(id)) type=MyISAM;

-- For now, vote tracking will be handled in long-life session cookies.
create table zz_pl_voters (
	id int not null auto_increment,
	primary key(id)) type=MyISAM;