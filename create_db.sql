create database if not exists emotes;
use emotes;

create table if not exists users
(
    id             int auto_increment primary key,
    email          nvarchar(128)        not null,
    username       nvarchar(128) unique not null,
    displayName    nvarchar(128)
);

create table if not exists auths
(
    id  int primary key auto_increment,
    name varchar(32) not null
);

create table userAccounts
(
    userID  int not null,
    authID int not null,
    platformUserID int unique not null,
    primary key (userID, authID),
    constraint validUserID foreign key (userID) references users(id),
    constraint validAuthID foreign key (authID) references auths(id)
);

GRANT ALL ON emotes.* TO iUser@`%`; # Grant access to iUser

create table if not exists format
(
    formatID        varchar(16) primary key,
    formatExtension varchar(16) unique
);

create table if not exists emotes
(
    id          int auto_increment primary key,
    hashLow     long          not null,
    hashHigh    long          not null,
    emoteOwner  int           not null,
    name        nvarchar(128) not null,
    description nvarchar(128) default '',
    author      nvarchar(128) null,
    data        VARBINARY(-1) not null
);

create table if not exists likes (
    id int auto_increment primary key,
    userID int,
    emoteID int,
    constraint likeMustBelongToUser foreign key (userID) references users (id),
    constraint likeMustLikeSomething foreign key (emoteID) references emotes(id)
);
