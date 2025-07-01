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

// Inisialisasi session jika belum ada
if (!isset($_SESSION['review_list'])) {
    $_SESSION['review_list'] = serialize(new ReviewList());
}
$reviewList = unserialize($_SESSION['review_list']);

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
            <h2>Add Your Review</h2>
            <form method="post">
                <label for="review_content">Your Review</label>
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
                <a href="?nav=prev_review"><button type="button">Previous</button></a>
                <a href="?nav=next_review"><button type="button">Next</button></a>
                <a href="?reset_review"><button type="button">Reset to First</button></a>
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