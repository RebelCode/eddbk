create table `${cqrs/bookings/table}`
(
	id bigint auto_increment
		primary key,
	start bigint not null,
	end bigint not null,
	service_id bigint not null,
	resource_id bigint not null,
	payment_id bigint null,
	client_id bigint null,
	client_tz varchar(100) null,
	admin_notes longtext null,
	status varchar(20) not null
);

create table `${cqrs/session_rules/table}`
(
	id bigint auto_increment
		primary key,
	service_id bigint not null,
	start int not null,
	end int not null,
	all_day tinyint(1) default '0' null,
	`repeat` tinyint(1) default '0' null,
	repeat_period int null,
	repeat_unit enum('days', 'weeks', 'months', 'years') null,
	repeat_until enum('date', 'period') null,
	repeat_until_period int null,
	repeat_until_date int null,
	repeat_weekly_on varchar(70) null,
	repeat_monthly_on enum('dotw', 'dotm') null,
	exclude_dates longtext null
);

create table `${cqrs/sessions/table}`
(
	id int auto_increment
		primary key,
	start int not null,
	end int not null,
	service_id int not null,
	resource_id int not null,
	rule_id int not null
);
