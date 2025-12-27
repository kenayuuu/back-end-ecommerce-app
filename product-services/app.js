const express = require('express');
const cors = require('cors');
const { DataTypes } = require('sequelize');
const sequelize = require('./database');
const app = express();
app.use(express.json());
app.use(cors());

const Product = sequelize.define('Product', {
  name: {
    type: DataTypes.STRING,
    allowNull: false
  },
  description: DataTypes.STRING,
  price: {
    type: DataTypes.FLOAT,
    allowNull: false
  }
});

// Sync DB
(async () => {
  try {
    await sequelize.sync({ alter: true });
    console.log('✅ Database synced');
  } catch (err) {
    console.error(err);
  }
})();

// Helper response
const success = (res, message, data = null) =>
  res.json({ success: true, message, data });
const error = (res, status, message) =>
  res.status(status).json({ success: false, message });

// Routes
app.get('/products', async (_, res) => {
  const data = await Product.findAll();
  success(res, 'Products fetched', data);
});
app.get('/products/:id', async (req, res) => {
  const product = await Product.findByPk(req.params.id);
  if (!product) return error(res, 404, 'Product not found');
  success(res, 'Product fetched', product);
});

//tambah produk baru
app.post('/products', async (req, res) => {
  const { name, price, description } = req.body;
  if (!name || !price) return error(res, 400, 'Invalid data');
  const product = await Product.create({ name, price, description });
  success(res, 'Product created', product);
});

//update produk
app.put('/products/:id', async (req, res) => {
  const product = await Product.findByPk(req.params.id);
  if (!product) return error(res, 404, 'Product not found');
  await product.update(req.body);
  success(res, 'Product updated', product);
});

//update dan delete produk
app.delete('/products/:id', async (req, res) => {
  const product = await Product.findByPk(req.params.id);
  if (!product) return error(res, 404, 'Product not found');
  await product.destroy();
  success(res, 'Product deleted');
});

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, '0.0.0.0',() => {
    console.log(`Product service is running on port ${PORT}`);
});