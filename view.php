<?php
require __DIR__ . '/app/autoload.php';
require __DIR__ . '/views/header.php';

if (isset($_GET['view'])) {
    $view = filter_var($_GET['view'], FILTER_SANITIZE_STRING);
    if ($view === 'post') {
        if (isset($_GET['post_id'])) {
            $postId = (int)filter_var($_GET['post_id'], FILTER_SANITIZE_NUMBER_INT);
            $post = fetchPost($postId, $db);
            if (!isset($post)) {
                addMessage('Link seems to be dead!');
            } else {
                $postComments = fetchComments($postId, $db);
                $commentReplies = fetchReplies($postId, $db);
            }
        }
    } elseif ($view === 'profile') {
        if (isset($_GET['user_id'])) {
            $userId = (int)filter_var($_GET['user_id'], FILTER_SANITIZE_NUMBER_INT);
            if (userLoggedIn() && $userId === (int)$_SESSION['user']['id']) {
                header('location: /profile.php');
                exit;
            }
            $profile = fetchUser($userId, $db);
            if (!isset($profile)) {
                addMessage('No user like this seems to exist!');
            } else {
                $userPosts = fetchUserPosts($userId, $db);
                $userComments = fetchUserComments($userId, $db);
            }
        }
    } elseif ($view === 'search') {
        $query = filter_var($_GET['query'], FILTER_SANITIZE_STRING);
        $searchResults = searchPosts($query, $db);
    } else {
        addMessage('Incorrect url');
        header('location: /');
        exit;
    }
} else {
    addMessage('Incorrect url');
    header('location: /');
    exit;
}
?>
<main>
    <?php if (isset($post)) : ?>
        <article class="post grid">
            <div class="half">
                <div class="votes">
                    <?php if (userLoggedIn() && userUpvote($_SESSION['user']['id'], $post['id'], $db)) : ?>
                        <button class="vote active" data-post="<?= $post['id'] ?>">▲</button>
                    <?php elseif (userLoggedIn() && !userUpvote($_SESSION['user']['id'], $post['id'], $db)) : ?>
                        <button class="vote" data-post="<?= $post['id'] ?>">▲</button>
                    <?php else : ?>
                        <button class="vote">▲</button>
                    <?php endif; ?>
                    <p class="upvotes"><?= $post['upvotes'] ?></p>
                </div>
                <a href="<?= $post['url'] ?>">
                    <h1><?= $post['title'] ?></h1>
                </a>
            </div>
            <div class="full">
                <p><?= $post['description'] ?></p>
                <p>
                    by
                    <a href="/view.php?view=profile&user_id=<?= $post['user_id'] ?>">
                        <?= fetchPoster((int)$post['user_id'], $db) ?>
                    </a>
                </p>
                <p><?= $post['creation_time'] ?></p>
                <?php if (userLoggedIn() && $post['user_id'] === $_SESSION['user']['id']) : ?>
                    <div class="button">
                        <a href="/edit.php?edit=post&post_id=<?= $post['id'] ?>">edit post</a>
                    </div>
                <?php endif; ?>
            </div>
            <section class="comments full">
                <?php if (isset($postComments)) : ?>
                    <?php foreach ($postComments as $postComment) : ?>
                        <div class="comment">
                            <a href="/view.php?view=profile&user_id=<?= $postComment['user_id'] ?>"><?= fetchPoster((int)$postComment['user_id'], $db) ?>:</a>
                            <p><?= $postComment['comment'] ?></p>
                            <?php if (isset($commentReplies)) : ?>
                                <?php foreach ($commentReplies as $commentReply) : ?>
                                    <?php if ($commentReply['reply'] === $postComment['id']) : ?>
                                        <div class="comment reply">
                                            <a href="/view.php?view=profile&user_id=<?= $commentReply['user_id'] ?>"><?= fetchPoster((int)$commentReply['user_id'], $db) ?>:</a>
                                            <p><?= $commentReply['comment'] ?></p>
                                            <?php if (userLoggedIn() && $_SESSION['user']['id'] === $commentReply['user_id']) : ?>
                                                <div class="button">
                                                    <a href="/edit.php?edit=comment&comment_id=<?= $commentReply['id'] ?>">edit comment</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if (userLoggedIn()) : ?>
                                <div class="button">
                                    <a href="/edit.php?edit=reply&comment_id=<?= $postComment['id'] ?>">reply</a>
                                </div>
                            <?php endif; ?>
                            <?php if (userLoggedIn() && $_SESSION['user']['id'] === $postComment['user_id']) : ?>
                                <div class="button">
                                    <a href="/edit.php?edit=comment&comment_id=<?= $postComment['id'] ?>">edit comment</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No comments yet</p>
                <?php endif; ?>
            </section>
            <?php if (userLoggedIn()) : ?>
                <form class="full" action="/app/posts/comment.php" method="post">
                    <label for="comment">comment</label>
                    <input type="hidden" id="post_id" name="post_id" value="<?= $post['id'] ?>">
                    <textarea id="comment" name="comment" rows="4" required></textarea>
                    <button type="submit">submit</button>
                </form>
            <?php else : ?>
                <div class="button">
                    <a href="/login.php">login to comment</a>
                </div>
            <?php endif; ?>
        </article>
    <?php elseif (isset($profile)) : ?>
        <section class="profile">
            <h1>profile</h1>
            <?php if (!isset($profile['image_url'])) : ?>
                <img src="/assets/images/no-image.png" alt="no profile picture selected">
            <?php else : ?>
                <img src="<?= $profile['image_url'] ?>" alt="profile picture">
            <?php endif; ?>
            <p><?= $profile['user_name'] ?></p>
            <p><?= $profile['bio'] ?></p>
            <?php if (isset($userPosts)) : ?>
                <h2>posts</h2>
                <ul class="user-posts">
                    <?php foreach ($userPosts as $userPost) : ?>
                        <li>
                            <a href="/view.php?view=post&post_id=<?= $userPost['id'] ?>"><?= $userPost['title'] ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <h2>no posts</h2>
            <?php endif; ?>
            <?php if (isset($userComments)) : ?>
                <h2>comments</h2>
                <?php foreach ($userComments as $userComment) : ?>
                    <div class="comment">
                        <p>on
                            <a href="/view.php?view=post&post_id=<?= $userComment['post_id'] ?>">
                                <?= fetchPostTitle((int)$userComment['post_id'], $db) ?>
                            </a>
                        </p>
                        <p><?= $userComment['comment'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <h2>no comments</h2>
            <?php endif; ?>
        </section>
    <?php elseif (isset($query)) : ?>
        <section class="search-field">
            <form action="/view.php" method="get">
                <label for="search">search</label>
                <input type="hidden" name="view" id="view" value="search">
                <input type="text" name="query" id="query" value=<?= $query ?>>
                <button type="submit">search</button>
            </form>
        </section>
        <?php if (isset($searchResults)) : ?>
            <?php foreach ($searchResults as $searchResult) : ?>
                <article class="post">
                    <div class="votes">
                        <?php if (userLoggedIn() && userUpvote($_SESSION['user']['id'], $searchResult['id'], $db)) : ?>
                            <button class="vote active" data-post="<?= $searchResult['id'] ?>">▲</button>
                        <?php elseif (userLoggedIn() && !userUpvote($_SESSION['user']['id'], $searchResult['id'], $db)) : ?>
                            <button class="vote" data-post="<?= $searchResult['id'] ?>">▲</button>
                        <?php else : ?>
                            <button class="vote">▲</button>
                        <?php endif; ?>
                        <p class="upvotes"><?= $searchResult['upvotes'] ?></p>
                    </div>
                    <div class="content">
                        <a href="<?= $searchResult['url'] ?>">
                            <h2><?= $searchResult['title'] ?></h2>
                        </a>
                        <a href="/view.php?view=post&post_id=<?= $searchResult['id'] ?>">
                            <?= $searchResult['comments'] ?> comments
                        </a>
                        <p> posted by
                            <a href="/view.php?view=profile&user_id=<?= $searchResult['user_id'] ?>">
                                <?= fetchPoster($searchResult['user_id'], $db) ?>
                            </a>
                            <?= getAge($searchResult['creation_time']) ?>
                        </p>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else : ?>
            <?php addMessage('No results') ?>
        <?php endif; ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['messages'])) : ?>
        <?php foreach ($_SESSION['messages'] as $message) : ?>
            <p class="error"><?= $message ?></p>
        <?php endforeach; ?>
        <?php unset($_SESSION['messages']) ?>
    <?php endif; ?>
</main>
<?php
require __DIR__ . '/views/footer.php';
?>