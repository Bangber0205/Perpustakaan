<?php
session_start();

// Struktur Node untuk Review
class ReviewNode {
    public $reviewText;
    public $reviewerName;
    public $rating;
    public $date;
    public $next = null;
    public $prev = null;

    public function __construct($text, $name, $rating, $date) {
        $this->reviewText = $text;
        $this->reviewerName = $name;
        $this->rating = $rating;
        $this->date = $date;
    }
}

// Double Linked List untuk Review
class ReviewList {
    private $head = null;
    private $current = null;

    public function addReview($text, $name, $rating, $date) {
        $newNode = new ReviewNode($text, $name, $rating, $date);
        if (!$this->head) {
            $this->head = $newNode;
            $this->current = $newNode;
        } else {
            $newNode->next = $this->head;
            $this->head->prev = $newNode;
            $this->head = $newNode;
            $this->current = $newNode;
        }
    }

    public function next() {
        if ($this->current && $this->current->next) {
            $this->current = $this->current->next;
        }
        return $this->current;
    }

    public function prev() {
        if ($this->current && $this->current->prev) {
            $this->current = $this->current->prev;
        }
        return $this->current;
    }

    public function getCurrent() {
        return $this->current;
    }

    public function resetCurrentToHead() {
        $this->current = $this->head;
        return $this->current;
    }
}

// Struktur Node untuk Peminjaman
class BorrowNode {
    public $bookTitle;
    public $borrowDate;
    public $returnDate;
    public $next = null;
    public $prev = null;

    public function __construct($title, $borrowDate, $returnDate = null) {
        $this->bookTitle = $title;
        $this->borrowDate = $borrowDate;
        $this->returnDate = $returnDate;
    }

    public function getStatus() {
        return $this->returnDate ? "Returned" : "Borrowed";
    }
}

// Double Linked List untuk Peminjaman
class BorrowList {
    private $head = null;
    private $tail = null;
    private $current = null;

    public function addBorrow($title, $borrowDate, $returnDate = null) {
        $newNode = new BorrowNode($title, $borrowDate, $returnDate);
        if (!$this->head) {
            $this->head = $newNode;
            $this->tail = $newNode;
            $this->current = $newNode;
        } else {
            $this->tail->next = $newNode;
            $newNode->prev = $this->tail;
            $this->tail = $newNode;
            $this->current = $newNode;
        }
    }

    public function getNext() {
        if ($this->current && $this->current->next) {
            $this->current = $this->current->next;
        }
        return $this->current;
    }

    public function getPrev() {
        if ($this->current && $this->current->prev) {
            $this->current = $this->current->prev;
        }
        return $this->current;
    }

    public function goToHead() {
        $this->current = $this->head;
        return $this->current;
    }

    public function goToTail() {
        $this->current = $this->tail;
        return $this->current;
    }

    public function getCurrent() {
        return $this->current;
    }
}

// Inisialisasi session jika belum ada
if (!isset($_SESSION['review_list'])) {
    $_SESSION['review_list'] = serialize(new ReviewList());
}
if (!isset($_SESSION['borrow_list'])) {
    $_SESSION['borrow_list'] = serialize(new BorrowList());
}

$reviewList = unserialize($_SESSION['review_list']);
$borrowList = unserialize($_SESSION['borrow_list']);

// Handle form tambah review
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    $content = htmlspecialchars(trim($_POST['review_content']));
    $name = htmlspecialchars(trim($_POST['reviewer_name']));
    $rating = intval($_POST['review_rating']);
    $date = date('Y-m-d H:i:s');

    if (!empty($content) && !empty($name) && $rating >= 1 && $rating <= 5) {
        $reviewList->addReview($content, $name, $rating, $date);
        $_SESSION['review_list'] = serialize($reviewList);
    }
}

// Handle navigasi review
if (isset($_GET['nav']) && $_GET['nav'] === 'next_review') {
    $reviewList->next();
    $_SESSION['review_list'] = serialize($reviewList);
} elseif (isset($_GET['nav']) && $_GET['nav'] === 'prev_review') {
    $reviewList->prev();
    $_SESSION['review_list'] = serialize($reviewList);
} elseif (isset($_GET['reset_review'])) {
    $reviewList->resetCurrentToHead();
    $_SESSION['review_list'] = serialize($reviewList);
}

// Handle form tambah pinjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_borrow'])) {
    $title = htmlspecialchars(trim($_POST['book_title']));
    $borrowDate = htmlspecialchars(trim($_POST['borrow_date']));
    $returnDate = !empty($_POST['return_date']) ? htmlspecialchars(trim($_POST['return_date'])) : null;

    if (!empty($title) && !empty($borrowDate)) {
        $borrowList->addBorrow($title, $borrowDate, $returnDate);
        $_SESSION['borrow_list'] = serialize($borrowList);
    }
}

// Handle navigasi peminjaman
if (isset($_GET['nav']) && $_GET['nav'] === 'next_borrow') {
    $borrowList->getNext();
    $_SESSION['borrow_list'] = serialize($borrowList);
} elseif (isset($_GET['nav']) && $_GET['nav'] === 'prev_borrow') {
    $borrowList->getPrev();
    $_SESSION['borrow_list'] = serialize($borrowList);
} elseif (isset($_GET['go_to_head'])) {
    $borrowList->goToHead();
    $_SESSION['borrow_list'] = serialize($borrowList);
} elseif (isset($_GET['go_to_tail'])) {
    $borrowList->goToTail();
    $_SESSION['borrow_list'] = serialize($borrowList);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Management System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            max-width: 900px;
            margin: auto;
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
        }
        .section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .nav-buttons {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .review-item, .borrow-item {
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-top: 10px;
            background-color: #f9f9f9;
            border-radius: 0 4px 4px 0;
        }
        .status-borrowed {
            color: red;
            font-weight: bold;
        }
        .status-returned {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Book Management System</h1>

    <!-- Bagian Review -->
    <div class="section">
        <h2>Book Reviews</h2>
        <form method="post">
            <label for="review_content">Add Your Review</label>
            <textarea name="review_content" id="review_content"></textarea>
            <label for="reviewer_name">Your Name</label>
            <input type="text" name="reviewer_name" id="reviewer_name">
            <label for="review_rating">Rating</label>
            <select name="review_rating" id="review_rating">
                <option value="5">★★★★★ (Excellent)</option>
                <option value="4">★★★★ (Good)</option>
                <option value="3">★★★ (Average)</option>
                <option value="2">★★ (Fair)</option>
                <option value="1">★ (Poor)</option>
            </select>
            <button type="submit" name="add_review">Add Review</button>
        </form>

        <hr>

        <?php
        $currentReview = $reviewList->getCurrent();
        if ($currentReview): ?>
            <div class="review-item">
                <h3><?= $currentReview->reviewerName ?>'s Review (<?= str_repeat("★", $currentReview->rating) . str_repeat("☆", 5 - $currentReview->rating) ?>)</h3>
                <p><em>Posted on <?= date('F j, Y', strtotime($currentReview->date)) ?></em></p>
                <p><?= nl2br(htmlspecialchars($currentReview->reviewText)) ?></p>
            </div>
        <?php else: ?>
            <p>No reviews available.</p>
        <?php endif; ?>

        <div class="nav-buttons">
            <a href="?nav=prev_review"><button type="button">Previous Review</button></a>
            <a href="?nav=next_review"><button type="button">Next Review</button></a>
            <a href="?reset_review"><button type="button">Reset to First Review</button></a>
        </div>
    </div>

    <!-- Bagian Peminjaman -->
    <div class="section">
        <h2>Borrowing History</h2>
        <form method="post">
            <label for="book_title">Book Title</label>
            <input type="text" name="book_title" id="book_title">
            <label for="borrow_date">Borrow Date</label>
            <input type="date" name="borrow_date" id="borrow_date">
            <label for="return_date">Return Date (optional)</label>
            <input type="date" name="return_date" id="return_date">
            <button type="submit" name="add_borrow">Add Borrow Record</button>
        </form>

        <hr>

        <?php
        $currentBorrow = $borrowList->getCurrent();
        if ($currentBorrow): ?>
            <div class="borrow-item">
                <h3><?= $currentBorrow->bookTitle ?></h3>
                <p><strong>Borrow Date:</strong> <?= $currentBorrow->borrowDate ?></p>
                <p><strong>Return Date:</strong> <?= $currentBorrow->returnDate ?: 'Not returned yet' ?></p>
                <p class="<?= $currentBorrow->getStatus() === 'Returned' ? 'status-returned' : 'status-borrowed' ?>">
                    Status: <?= $currentBorrow->getStatus() ?>
                </p>
            </div>
        <?php else: ?>
            <p>No borrowing records available.</p>
        <?php endif; ?>

        <div class="nav-buttons">
            <a href="?nav=prev_borrow"><button type="button">Previous Record</button></a>
            <a href="?nav=next_borrow"><button type="button">Next Record</button></a>
            <a href="?go_to_head"><button type="button">Go to First Record</button></a>
            <a href="?go_to_tail"><button type="button">Go to Latest Record</button></a>
        </div>
    </div>
</body>
</html>