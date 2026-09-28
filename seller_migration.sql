
CREATE TABLE seller (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE category
    ADD COLUMN seller_id INT NULL,
    ADD CONSTRAINT fk_category_seller
    FOREIGN KEY (seller_id) REFERENCES seller(id) ON DELETE SET NULL;

ALTER TABLE products
    ADD COLUMN seller_id INT NULL,
    ADD CONSTRAINT fk_products_seller
    FOREIGN KEY (seller_id) REFERENCES seller(id) ON DELETE SET NULL;
