CREATE sequence items_id_seq;

CREATE sequence orders_id_seq;

CREATE TABLE items
(
	id INTEGER DEFAULT nextval('items_id_seq'::regclass) NOT NULL
		CONSTRAINT items_pkey
			PRIMARY KEY,
	name varchar(255) NOT NULL,
	price DOUBLE PRECISION NOT NULL
);

CREATE TABLE orders
(
	id INTEGER DEFAULT nextval('orders_id_seq'::regclass) NOT NULL
		CONSTRAINT orders_pkey
			PRIMARY KEY,
	status VARCHAR (32) NOT NULL,
	created_at TIMESTAMP DEFAULT now() NOT NULL,
	amount DOUBLE PRECISION NOT NULL
);

CREATE TABLE order_item
(
	order_id INTEGER NOT NULL
		CONSTRAINT order_item_order_id_fkey
			REFERENCES orders
				ON UPDATE CASCADE ON DELETE CASCADE,
	item_id INTEGER NOT NULL
		CONSTRAINT order_item_item_id_fkey
			REFERENCES items
				ON UPDATE CASCADE,
	CONSTRAINT order_item_pkey
		PRIMARY KEY (order_id, item_id)
);

