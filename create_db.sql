create database if not exists emotes;
use emotes;

create table if not exists users
(
    id             int auto_increment primary key,
    email          nvarchar(128)        not null,
    username       nvarchar(128) unique not null,
    displayName    nvarchar(128)
);

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
    author      nvarchar(128) null
);

create table if not exists emoteFiles
(
    id         int auto_increment primary key,
    fileOwner  int           not null,
    filePath   nvarchar(256) not null unique, # Files will be stored in hashed names/paths
    fileName   nvarchar(128),
    fileFormat varchar(16),
    emote      int not null,
    constraint fileMustHaveOwner foreign key (fileOwner) references users (id),
    constraint fileMustHaveFormat foreign key (fileFormat) references format (formatID),
    constraint fileMustBelongToEmote foreign key (emote) references emotes (id)
);

create table if not exists likes (
    id int auto_increment primary key,
    userID int,
    emoteID int,
    constraint likeMustBelongToUser foreign key (userID) references users (id),
    constraint likeMustLikeSomething foreign key (emoteID) references emotes(id)
);
