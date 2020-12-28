<?php
require __DIR__ . '/app/autoload.php';
require __DIR__ . '/views/header.php';

if (!isset($_SESSION['user'])) {
    header("location: /");
}

fetchUserData($_SESSION['user'], $db);

print_r($_SESSION['user']);

?>
<main>
    <section>
        <?php if (!isset($_GET["edit-profile"])) : ?>
            <h1>profile page</h1>
            <?php if (!isset($_SESSION['user']['image_url'])) : ?>
                <img src="/assets/images/no-image.png" alt="no profile picture selected">
            <?php else : ?>
                <img src="<?= $_SESSION['user']['image_url'] ?>" alt="profile picture">
            <?php endif; ?>
            <p><?= $_SESSION['user']['user_name'] ?></p>
            <p><?= $_SESSION['user']['bio'] ?></p>
            <a href="/profile.php?edit-profile=profile">edit profile</a>
            <a href="/app/users/logout.php">logout</a>
        <?php elseif ($_GET['edit-profile'] === "profile") : ?>
            <form action="/app/users/profile.php" method="post" enctype="multipart/form-data">
                <label for="profile_picture">profile picture</label>
                <?php if (!isset($_SESSION['user']['image_url'])) : ?>
                    <img src="/assets/images/no-image.png" alt="no profile picture selected">
                <?php else : ?>
                    <img src="<?= $_SESSION['user']['image_url'] ?>" alt="profile picture">
                <?php endif; ?>
                <input type="file" name="profile_picture" id="profile_picture" accept=".jpeg, .gif, .png">
                <label for="user_name">user name</label>
                <input type="text" name="user_name" id="user_name" value="<?= $_SESSION['user']['user_name']; ?>">
                <label for="bio">bio</label>
                <textarea id="bio" name="bio" rows="4"><?= $_SESSION['user']['bio']; ?></textarea>
                <button type="submit">submit</button>
            </form>
            <a href="/profile.php?edit-profile=password"><button>change password</button></a>
        <?php elseif ($_GET['edit-profile'] === "password") : ?>
            <form action="/app/users/profile.php" method="post">
                <label for="current_password">current password</label>
                <input type="password" name="current_password" id="current_password" required>
                <label for="password">new password</label>
                <input type="password" name="password" id="password" required>
                <label for="password_check">repeat new password</label>
                <input type="password" name="password_check" id="password_check" required>
                <button type="submit">change</button>
            </form>
        <?php endif; ?>
        <?php if (isset($_SESSION['message'])) : ?>
            <p><?= $_SESSION['message'] ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    </section>
</main>


<?php
require __DIR__ . '/views/footer.php';
?>