CREATE TABLE Inventory (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    category TEXT NOT NULL,
    item_count INTEGER DEFAULT 0,
    owner TEXT NOT NULL,
    created_at TEXT
);

INSERT INTO Inventory (title, category, item_count, owner, created_at) VALUES 
('Company Laptops', 'Equipment', 142, 'admin', '2025-01-15'),
('Library Catalog', 'Book', 3248, 'librarian', '2024-06-20'),
('Office Furniture', 'Furniture', 87, 'facilities', '2025-03-10'),
('Employee Records', 'Other', 512, 'hr', '2024-11-05');
