/* RML persons database
*  SETUP file, run once
*  assumes already in database 'members' 
*  and it's empty
*/

drop table if exists person, keyholder, admin, inductions, loan_record;
create table person (
    person_id   int not null auto_increment primary key,
    known_as    varchar (30) not null unique,
    first_name  varchar(15)  not null,
    last_name   varchar (60) not null,
    address_1   varchar (60),
    post_code   varchar (15) not null,
    email       varchar (254),
    phone       varchar (60),
    registered  date not null,
    cancelled   date default NULL
)
    auto_increment = 314159;

drop table if exists person_detail;
create table person_detail (
    person_id       int not null,   -- foreign key
    from_signup     tinyint default 0,
    from_maillist   tinyint default 0,
    from_social     tinyint default 0,
    from_other      tinyint default 0,
    notes           text,           -- up to 16k chars per record
    mugshot         mediumblob    ); -- up to 16Mbytes per record
    
drop table if exists person_detail;
create table person_detail (
    person_id       int not null,   -- foreign key
    from_signup     tinyint default 0,
    from_maillist   tinyint default 0,
    from_social     tinyint default 0,
    from_other      tinyint default 0,
    notes           text,           -- up to 16k chars per record
    mugshot         mediumblob    ); -- up to 16Mbytes per record
    
drop table if exists subs_recv;
create table subs_recv    (
    person_id       int not null,
    amount_recv     float(6,2),
    date_recv       date    );      -- format YYYY-MM-DD
    
drop table if exists subs_due;
create table subs_due     (
    person_id       int not null,
    amount_due      float(6,2),
    date_due        date    );      -- format YYYY-MM-DD

 
drop table if exists keyholder;
create table keyholder  (
    person_id   int not null,
    issue_date  date not null,
    cancel_date date
 );

drop table if exists admin;
create table admin  (
    person_id   int not null primary key,
    passwd_hash char (255),      -- hash of password
    issue_date  date not null,
    cancel_date date
);


drop table if exists inductions;
create table inductions (
    person_id       int not null,
    tool_id     int not null,
    induction_date  date not null,
    unique (person_id, tool_id)
);

drop table if exists loan_record;
create table loan_record    (
    loan_id     int not null auto_increment primary key,
    person_id   int not null,
    tool_id     int,
    tool_name   varchar (255),
    date_out    date not null,
    date_return date
)
    auto_increment = 5432;
    
drop table if exists fault_record;
create table fault_record   (
    fault_id    int not null auto_increment primary key,
    tool_id     int not null,
    tool_name   varchar(60) not null,
    report_by   int,
    report_date date,
    report_text varchar(255),
    fix_by      int,
    fix_date    date,
    fix_text    varchar(255)
)
    auto_increment = 2121;
    
drop table if exists inventory;
create table inventory  (
    tool_id         int not null auto_increment primary key,
    tool_name       varchar(60) not null,
    tool_make       varchar(60),
    tool_model      varchar(60),
    tool_location   varchar(60),
    date_added      date not null,
    fault_notify    varchar(60) not null default 'info@richmondmakerlabs.uk',
    loan_permit     tinyint default 0,
    induction_reqd  tinyint default 0,
    fault_report_list   tinyint default 0,
    date_removed    date
)
    auto_increment 111;    
    
drop table if exists fortune;
create table fortune (
    fc_id       int not null auto_increment primary key,
    fc_text     varchar (255) not null 
);

drop table if exists attendance;
create table attendance (
    person_id       int not null,
    known_as        varchar (30),
    day             date not null,
    check_in        timestamp default current_timestamp,
    UNIQUE visit (person_id, day)
);

drop table if exists rfid_card;
create table rfid_card  (
    card_id     int unsigned not null primary key,
    person_id   int not null
);

drop table if exists recent_card;
create table recent_card    (
    card_id     int unsigned null
);
insert into recent_card values (card_id = null);
    
    

load data local infile 'person_data.txt'
    into table person
    fields terminated by '|';
    
load data local infile 'admin_data.txt'
    into table admin
    fields terminated by '|';
    
load data local infile 'inventory_data.txt'
    into table inventory
    fields terminated by '|';
    
load data local infile 'fortune_data.txt'
    into table fortune
    fields terminated by '|';
       
