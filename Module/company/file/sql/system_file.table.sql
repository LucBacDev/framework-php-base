create table system_file(
    id varchar(50) primary key ,
    createdDate datetime,
    `name` varchar(255),
    b64 longtext,
    siteID varchar(50),
    b64Size bigint,
    context varchar(150),
    mime varchar(255)
);

create index idx_siteID on system_file(siteID);
create index idx_context on system_file(context);