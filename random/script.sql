CREATE DATABASE stocking;
USE stocking;

# Users info
CREATE TABLE users(
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) UNIQUE NOT NULL,
	`password` VARCHAR(255) NOT NULL,
	email VARCHAR(255) UNIQUE NOT NULL,
	role VARCHAR(20) CHECK(role IN('ADMIN', 'MANAGER', 'STAFF', 'VIEWER')) NOT NULL DEFAULT 'STAFF',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

# Table for category of products
CREATE TABLE categories(
	id INT AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(100) NOT NULL,
	`desc` TEXT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

# Storing all the products
CREATE TABLE products(
	id INT AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) UNIQUE NOT NULL,
	`desc` TEXT,
	price DECIMAL(10, 2) NOT NULL,
	cost DECIMAL(10, 2) NOT NULL,
	cat_id INT NOT NULL,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY(cat_id) REFERENCES categories(id)
);

# Present stocking
CREATE TABLE stock(
	id INT AUTO_INCREMENT PRIMARY KEY,
	product_id INT NOT NULL,
	quantity INT NOT NULL DEFAULT 0 CHECK(quantity >= 0),
	FOREIGN KEY(product_id) REFERENCES products(id)
);

# Track the movement of the stock
CREATE TABLE stock_movement(
	id INT AUTO_INCREMENT PRIMARY KEY,
	product_id INT NOT NULL,
	`type` VARCHAR(20) CHECK(`type` IN('IN', 'OUT', 'ADJUST')) NOT NULL,
	quantity INT NOT NULL DEFAULT 0 CHECK(quantity >= 0),
	note TEXT,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY(product_id) REFERENCES products(id)
);

# Who and where to buy supply from
CREATE TABLE suppliers(
	id INT AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) UNIQUE NOT NULL,
	contact_person VARCHAR(255),
	email VARCHAR(255) UNIQUE NOT NULL,
	phone VARCHAR(50) UNIQUE NOT NULL,
	address TEXT
);

# Product order
CREATE TABLE po(
	id INT AUTO_INCREMENT PRIMARY KEY,
	supplier_id INT NOT NULL,
	order_date DATE NOT NULL,
	`status` ENUM('PENDING', 'APPROVED', 'REJECTED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
	total_amount DECIMAL(10, 2),
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY(supplier_id) REFERENCES suppliers(id)
);

# Product order items
CREATE TABLE poi(
    id INT AUTO_INCREMENT PRIMARY KEY,
    po_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0 CHECK(quantity >= 0),
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) AS (quantity * unit_price) STORED,
    FOREIGN KEY (po_id) REFERENCES po(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

# Sale orders
CREATE TABLE so(
    id INT AUTO_INCREMENT PRIMARY KEY,
    cus_name VARCHAR(255) NOT NULL,
    cus_email VARCHAR(255) NOT NULL UNIQUE,
    order_date DATE NOT NULL,
    `status` ENUM('PENDING', 'APPROVED', 'REJECTED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
    total_amount DECIMAL(10, 2) NOT NULL
);

# Sale order items
CREATE TABLE soi(
    id INT AUTO_INCREMENT PRIMARY KEY,
    so_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 0 CHECK(quantity >= 0),
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) AS (quantity * unit_price) STORED,
    FOREIGN KEY (so_id) REFERENCES so(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);


