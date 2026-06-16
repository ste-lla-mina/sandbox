// 1. Load environment variables at the very top
require('dotenv').config();

const express = require('express');
const connectDB = require('./db');
const { getBooks, addBook, updateBook, deleteBook } = require('./controller');

const app = express();
const PORT = process.env.PORT || 3000; 
connectDB();

app.use(express.json());

app.get('/api/books', getBooks);       
app.post('/api/books', addBook);       
app.put('/api/books/:id', updateBook);   
app.delete('/api/books/:id', deleteBook); 

app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});