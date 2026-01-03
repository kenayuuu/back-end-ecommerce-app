const express = require('express');
const cors = require('cors');
const { Pool } = require('pg');
const app = express();
app.use(cors());
app.use(express.json());

const pool = new Pool({
    host: process.env.DB_HOST || 'localhost',
    port: 5432,
    database: process.env.DB_NAME || 'userdb',
    user: process.env.DB_USER || 'useradmin',
    password: process.env.DB_PASSWORD || 'userpass',
});

// Create table if not exists
pool.query(`
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        role VARCHAR(50) NOT NULL
    );
`, (err) => {
    if (err) {
        console.error('Error creating table:', err);
    } else {
        console.log('Table users created or already exists');
    }
});

//daftar users
app.get('/users', async (req, res) => {
    try {
        const result = await pool.query('SELECT * FROM users');
        res.json(result.rows);
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

//detail user berdasarkan id
app.get('/users/:id', async (req, res) => {
    const userId = parseInt(req.params.id);
    try {
        const result = await pool.query('SELECT * FROM users WHERE id = $1', [userId]);
        if (result.rows.length > 0) {
            res.json(result.rows[0]);
        } else {
            res.status(404).json({ message: 'User not found' });
        }
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

//tambah user
app.post('/users', async (req, res) => {
    const { name, email, role } = req.body;
    if (!name || !email || !role) {
        return res.status(400).json({ message: 'Name, email, and role are required' });
    }
    try {
        const result = await pool.query(
            'INSERT INTO users (name, email, role) VALUES ($1, $2, $3) RETURNING *',
            [name, email, role]
        );
        res.status(201).json(result.rows[0]);
    } catch (err) {
        if (err.code === '23505') { // unique violation
            res.status(400).json({ message: 'Email already exists' });
        } else {
            res.status(500).json({ error: err.message });
        }
    }
});

const PORT = process.env.PORT || 4000;
app.listen(PORT,'0.0.0.0', () => {
    console.log(`User service is running on port ${PORT}`);
});
