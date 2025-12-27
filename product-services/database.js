const {Sequelize} = require('sequelize');

// Initialize Sequelize with database configuration
const sequelize = new Sequelize(
    process.env.DB_NAME || 'product_db',
    process.env.DB_USER || 'root',
    process.env.DB_PASSWORD || 'root',
    {
        host: process.env.DB_HOST,
        dialect: 'mysql',
        port: 3306,
        logging: false
    }
);

// FUNCTION TO TEST THE DATABASE CONNECTION
const testConnection = async () => {
    try {
        await sequelize.authenticate();
        console.log('Database connection has been established successfully.');
    } catch (error) {
        console.error('Unable to connect to the database:', error);
    }   
};

// Test the database connection
// testConnection();

module.exports = sequelize;