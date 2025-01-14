<?php
require __DIR__ . '/app/autoload.php';
require __DIR__ . '/views/header.php';

if (userLoggedIn()) {
    header("location: /");
    exit;
}

if (isset($_SESSION['return'])) {
    $return = $_SESSION['return'];
    unset($_SESSION['return']);
}

?>
<section class="login">
    <h1>login</h1>
    <form action="/app/users/login.php" method="post">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="<?= isset($return) ? $return['email'] : "" ?>" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">login</button>
    </form>
    <a class="button" href="signup.php">register account</a>
    <?php if (isset($_SESSION['messages'])) : ?>
        <?php foreach ($_SESSION['messages'] as $message) : ?>
            <p class="message"><?= $message ?></p>
        <?php endforeach; ?>
        <?php unset($_SESSION['messages']) ?>
    <?php endif; ?>
</section>

<?php
require __DIR__ . '/views/footer.php';
?>