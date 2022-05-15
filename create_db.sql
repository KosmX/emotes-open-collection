drop database if exists emotes;

create database if not exists emotes CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
use emotes;

create table if not exists users
(
    id             int auto_increment primary key,
    email          varchar(128)        not null,
    username       varchar(128) unique not null,
    displayName    varchar(128) collate utf8mb4_0900_ai_ci,
    isEmailPublic  bool default false,
    theCheckbox    bool default false
);

create table if not exists auths
(
    id    int primary key auto_increment,
    name  varchar(32)  not null
);

create table userAccounts
(
    userID  int not null,
    authID int not null,
    platformUserID int unique not null,
    token varchar(128) null, #This is not a password
    primary key (userID, authID),
    constraint validUserID foreign key (userID) references users(id),
    constraint validAuthID foreign key (authID) references auths(id)
);

GRANT ALL ON emotes.* TO iUser@`%`; # Grant access to iUser

/* If I create a formatter service, it won't be needed
create table if not exists format
(
    formatID        varchar(16) primary key,
    formatExtension varchar(16) unique
);*/

create table if not exists emotes
(
    id          int auto_increment primary key,
    hashLow     bigint        not null,
    hashHigh    bigint        not null,
    emoteOwner  int           not null,
    name        varchar(128) not null,
    description varchar(256) default '',
    author      varchar(128) null,
    data        MEDIUMBLOB    not null,
    unique (hashHigh, hashLow) #128 bit UUID
);

create table if not exists likes (
    id int auto_increment primary key,
    userID int,
    emoteID int,
    constraint likeMustBelongToUser foreign key (userID) references users (id),
    constraint likeMustLikeSomething foreign key (emoteID) references emotes(id)
);

INSERT INTO auths (name) value ('gh');