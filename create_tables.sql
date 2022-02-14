/* create tables using the lecturer's schema
drop tables before creating */
SET FOREIGN_KEY_CHECKS = 0;
drop table if exists users, clients, projects, product_images, products, categories_products, categories;
SET FOREIGN_KEY_CHECKS = 1;

create table users
(
    id       int(11)     not null primary key auto_increment,
    username varchar(64) not null unique key,
    password varchar(64) not null
);

create table categories (
    id int(11) not null auto_increment primary key,
    name varchar(64) not null
);

create table products (
    id int(11) not null auto_increment primary key, 
    name varchar(64) not null, 
    purchase_price decimal(9,2) not null,
    sale_price decimal(9,2) not null
);

create table categories_products (
    id int(11) not null auto_increment primary key,
    category_id int(11) not null,
    product_id int(11) not null
);

create table product_images (
    id int(11) not null auto_increment primary key,
    product_id int(11) not null,
    filename varchar(4096)
);

create table clients (
    id int(11) not null auto_increment primary key,
    first_name varchar(64) not null,
    surname varchar(64) not null,
    address text,
    phone varchar(10),
    mobile varchar(10),
    email varchar(256),
    subscribed tinyint(1)
);

create table projects (
    id int(11) not null auto_increment primary key,
    client_id int(11) not null,
    name varchar(256) not null,
    description text,
    address text,
    quote decimal(9,2) not null,
    other_information mediumtext
);

-- add FK constraints
alter table categories_products
add constraint categories_products_category_id_fk foreign key (category_id) references categories(id) on delete cascade,
add constraint categories_products_product_id_fk foreign key (product_id) references products(id) on delete cascade;

alter table product_images
add constraint product_images_product_id_fk foreign key (product_id) references products(id) on delete cascade;

alter table projects
add constraint projects_client_id_fk foreign key (client_id) references clients(id) on delete cascade;

-- add some values into categories table
insert into categories (name) values ("stationery"), ("food"), ("drink"), ("clothing"), ("tranport"), ("photography"), ("decoration");

-- add user's login
insert into users (username, password)
values ('alfred', sha2('alfred1', 0)),
('seabrook', sha2('seabrook1', 0));
