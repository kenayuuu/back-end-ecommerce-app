const express = require('express');
const cors = require('cors');
const app = express();
app.use(cors());

const products = [
    { id: 1, name: 'Laptop', price: 999.99, Description: 'A high-performance laptop suitable for all your computing needs.' },
    { id: 2, name: 'Smartphone', price: 499.99, Description: 'A sleek smartphone with the latest features and a stunning display.' },
    { id: 3, name: 'Tablet', price: 299.99, Description: 'A lightweight tablet perfect for browsing, reading, and entertainment.'},
];

//daftar products
app.get('/products', (req, res) => {
    res.json(products);
});

//detail product berdasarkan id
app.get('/products/:id', (req, res) => {
    const productId = parseInt(req.params.id);
    const product = products.find(p => p.id === productId);
    if (product) {
        res.json(product);
    } else {
        res.status(404).json({ message: 'Product not found' });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Product service is running on port ${PORT}`);
});