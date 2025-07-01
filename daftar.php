<?php
session_start();

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
if (!isset($_SESSION['borrow_list'])) {
    $_SESSION['borrow_list'] = serialize(new BorrowList());
}
$borrowList = unserialize($_SESSION['borrow_list']);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpustakaan Gen-Z</title>
    <link rel="stylesheet" href="assets/style2.css">
    <link rel="preconnect" href="https://fonts.googleapis.com ">
    <link rel="preconnect" href="https://fonts.gstatic.com " crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Tangerine:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href=" https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css "/>
    <script src="https://unpkg.com/feather-icons "></script>
</head>
<body>
    <nav class="animate__animated animate__fadeInDown">
        <div class="logo-nav animate__animated animate__fadeIn">
            <h1>Books Library</h1>
        </div>
        <div class="navbar animate__animated animate__fadeIn">
            <div class="a-home">
                <a href="#">Home</a>
            </div>
            <div class="a-daftar">
                <a href="daftar.php">Pinjaman Buku</a>
            </div>
            <div class="a-review">
                <a href="review.php">Review Buku</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="section">
            <h2>Add Borrow Record</h2>
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
                <a href="?nav=prev_borrow"><button type="button">Previous</button></a>
                <a href="?nav=next_borrow"><button type="button">Next</button></a>
                <a href="?go_to_head"><button type="button">Go to First</button></a>
                <a href="?go_to_tail"><button type="button">Go to Latest</button></a>
            </div>
        </section>
    </main>

    <footer>
        <section class="info-footer">
            <div class="info">
                <h1>Books Library</h1>
                <p>Selamat datang di Perpustakaan! Temukan ribuan buku, e-book, dan referensi digital yang siap mendukung pengetahuan dan inspirasimu.</p>
            </div>
        </section>
        <div class="separator"></div>
        <section class="contact-us">
            <div class="contact">
                <div class="judul-contact">
                    <p>Contact Us</p>
                </div>
                <div class="alamat">
                    <i data-feather="map-pin"></i>
                    <a href="">Cilacap, Jawa Tengah</a>
                </div>
                <div class="telepon">
                    <i data-feather="phone"></i>
                    <a href="">+1 076 645</a>
                </div>
                <div class="email">
                    <i data-feather="mail"></i>
                    <a href="">example@gmail.com</a>
                </div>
            </div>
        </section>
        <section class="social-media">
            <div class="socmed">
                <div class="judul-socmed">
                    <p>Social Media</p>
                </div>
                <div class="ig">
                    <i data-feather="instagram"></i>
                    <a href="">Instagram</a>
                </div>
                <div class="fb">
                    <i data-feather="facebook"></i>
                    <a href="">Facebook</a>
                </div>
                <div class="gh">
                    <i data-feather="github"></i>
                    <a href="">Github</a>
                </div>
            </div>
        </section>
        <section class="quick-links">
            <div class="qlinks">
                <div class="judul-qlinks">
                    <p>Quick Links</p>
                </div>
                <div class="isi-qlinks">
                    <a href="">Home</a>
                    <a href="">Daftar Buku</a>
                    <a href="index.html">Review Buku</a>
                </div>
            </div>
        </section>
    </footer>

    <script>
        feather.replace();
    </script>
</body>
</html>