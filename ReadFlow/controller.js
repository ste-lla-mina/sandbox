const Book = require('./bookModel');
const getBooks = async (req, res) => {
    try {
        const books = await Book.find();
        res.status(200).json(books);
    } catch (error) {
        res.status(500).json({ message: "Server error", error: error.message });
    }
};
const addBook = async (req, res) => {
    try {
        const { title, author, price } = req.body;
        const newBook = await Book.create({ title, author, price });
        
        res.status(201).json({ message: "Book saved successfully!", book: newBook });
    } catch (error) {
        res.status(400).json({ message: "Error: ", error: error.message });
    }
};
const updateBook = async (req, res) => {
    try {
        const bookId = req.params.id;
        const updatedBook = await Book.findByIdAndUpdate(bookId, req.body, { new: true, runValidators: true });

        if (!updatedBook) {
            return res.status(404).json({ message: "Book not found" });
        }

        res.status(200).json({ message: "Book updated successfully!", book: updatedBook });
    } catch (error) {
        res.status(400).json({ message: "Update failed", error: error.message });
    }
};

const deleteBook = async (req, res) => {
    try {
        const bookId = req.params.id;
        const deletedBook = await Book.findByIdAndDelete(bookId);

        if (!deletedBook) {
            return res.status(404).json({ message: "Book not found" });
        }

        res.status(200).json({ message: "Book deleted successfully!" });
    } catch (error) {
        res.status(500).json({ message: "Delete failed", error: error.message });
    }
};

module.exports = {
    getBooks,
    addBook,
    updateBook,
    deleteBook
};